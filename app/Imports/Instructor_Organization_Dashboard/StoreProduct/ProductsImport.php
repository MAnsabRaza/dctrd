<?php

namespace App\Imports\Instructor_Organization_Dashboard\StoreProduct;

use App\Models\Product;
use App\Models\Translation\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\ProductMedia;
use App\Models\ProductSelectedFilterOption;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        Log::info('Processing product row:', $row);
        $requiredFields = [
            'type', 'locale', 'title' // Removed 'creator_id' since we’ll use auth()->id()
        ];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $row) || (empty($row[$field]) && $row[$field] !== '0')) 
            {
                Log::warning("Skipping row due to missing or empty field: {$field}", $row);
                return null;
            }
        }
        $validator = Validator::make($row, [
            'type' => 'required|in:' . implode(',', Product::$productTypes),
            'locale' => 'required|string',
            'title' => 'required|string',
            'creator_id' => 'nullable|exists:users,id', // Optional in Excel, defaults to auth()->id()
            'slug' => 'nullable|max:255|unique:products,slug',
            'category_id' => 'nullable|exists:product_categories,id',
            'price' => 'nullable|numeric|min:0',
            'point' => 'nullable|integer|min:0',
            'unlimited_inventory' => 'nullable|in:0,1',
            'ordering' => 'nullable|in:0,1',
            'inventory' => 'nullable|integer|min:0',
            'inventory_warning' => 'nullable|integer|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'delivery_estimated_time' => 'nullable|integer|min:0',
            'message_for_reviewer' => 'nullable|string',
            'tax' => 'nullable|integer|min:0',
            'commission_type' => 'nullable|in:percent,fixed_amount',
            'commission' => 'nullable|numeric|min:0',
            'seo_description' => 'nullable|string',
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'variants' => 'nullable|string', // e.g., "Variant1:10:5|Variant2:15:3"
            'media' => 'nullable|string', // e.g., "/path/to/image1|/path/to/image2"
            'filter_options' => 'nullable|string', // e.g., "[1,2,3]" or "1,2,3"
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed for product row:', ['errors' => $validator->errors()->all(), 'row' => $row]);
            return null;
        }

        $data = $row;

        // Parse multilingual fields
        $locales = array_map('trim', explode(',', $data['locale']));
        $titles = array_map('trim', explode('|', $data['title']));
        $seo_descriptions = !empty($data['seo_description']) ? array_map('trim', explode('|', $data['seo_description'])) : array_fill(0, count($locales), null);
        $summaries = !empty($data['summary']) ? array_map('trim', explode('|', $data['summary'])) : array_fill(0, count($locales), null);
        $descriptions = !empty($data['description']) ? array_map('trim', explode('|', $data['description'])) : array_fill(0, count($locales), null);

        if (count($locales) !== count($titles) ||
            count($locales) !== count($seo_descriptions) ||
            count($locales) !== count($summaries) ||
            count($locales) !== count($descriptions)) {
            Log::error("Skipping row: Locale, title, seo_description, summary, and description must have matching lengths", $row);
            return null;
        }

        foreach ($locales as $locale) {
            if (!in_array(strtolower($locale), ['en', 'ar'])) {
                Log::error("Skipping row: Unsupported locale: {$locale}", $row);
                return null;
            }
        }
        foreach ($titles as $title) {
            if (strlen($title) > 255) {
                Log::error("Skipping row: Title exceeds 255 characters: {$title}", $row);
                return null;
            }
        }

        if (empty($data['slug'])) {
            $data['slug'] = Product::makeSlug($titles[0]);
        }

        $commission = !empty($data['commission']) ? $data['commission'] : null;
        if ($commission && ($data['commission_type'] ?? 'percent') === 'fixed_amount') {
            $commission = convertPriceToDefaultCurrency($commission);
        }

        // Create Product
        $product = Product::create([
            'creator_id' => $data['creator_id'] ?? auth()->id(), // Use provided creator_id or auth()->id()
            'type' => $data['type'],
            'slug' => $data['slug'],
            'category_id' => $data['category_id'] ?? null,
            'price' => $data['price'] ?? null,
            'point' => $data['point'] ?? null,
            'unlimited_inventory' => !empty($data['unlimited_inventory']) ? (bool)$data['unlimited_inventory'] : false,
            'ordering' => !empty($data['ordering']) ? (bool)$data['ordering'] : false,
            'inventory' => $data['inventory'] ?? null,
            'inventory_warning' => $data['inventory_warning'] ?? null,
            'inventory_updated_at' => $data['inventory'] ? time() : null,
            'delivery_fee' => $data['delivery_fee'] ?? null,
            'delivery_estimated_time' => $data['delivery_estimated_time'] ?? null,
            'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
            'tax' => $data['tax'] ?? null,
            'commission_type' => $data['commission_type'] ?? 'percent',
            'commission' => $commission,
            'status' => Product::$pending,
            'updated_at' => time(),
            'created_at' => time(),
        ]);

        if ($product) {
            // Product Translations
            foreach ($locales as $index => $locale) {
                ProductTranslation::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'locale' => mb_strtolower($locale),
                    ],
                    [
                        'title' => $titles[$index],
                        'seo_description' => $seo_descriptions[$index],
                        'summary' => $summaries[$index],
                        'description' => $descriptions[$index],
                    ]
                );
            }

            // Product Variants
            if (!empty($data['variants'])) {
                $variants = array_map('trim', explode('|', $data['variants']));
                ProductVariant::where('product_id', $product->id)->delete();
                foreach ($variants as $variant) {
                    [$name, $price, $inventory] = array_pad(explode(':', $variant), 3, null);
                    if ($name && is_numeric($price) && is_numeric($inventory)) {
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'name' => $name,
                            'price' => $price,
                            'stock' => $inventory, // Assuming 'stock' is the field name in ProductVariant
                            'created_at' => time(),
                            'updated_at' => time(),
                        ]);
                    }
                }
            }

            // Product Media
            if (!empty($data['media'])) {
                $mediaPaths = array_map('trim', explode('|', $data['media']));
                ProductMedia::where('product_id', $product->id)->delete();
                foreach ($mediaPaths as $path) {
                    if (!empty($path)) {
                        ProductMedia::create([
                            'creator_id' => $product->creator_id, // Use product's creator_id
                            'product_id' => $product->id,
                            'path' => $path,
                            'type' => 'image', // Default to 'image'; adjust if needed
                            'created_at' => time(),
                            'updated_at' => time(),
                        ]);
                    }
                }
            }

            // Filter Options
            if (!empty($data['filter_options'])) {
                $filterString = (string)$data['filter_options'];
                $filterIds = json_decode($filterString, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($filterIds)) {
                    $filterIds = array_map('trim', explode(',', $filterString));
                }

                foreach ($filterIds as $filterId) {
                    if (!is_numeric($filterId) || $filterId < 0) {
                        Log::error("Skipping row: Invalid filter ID: {$filterId}", $row);
                        return null;
                    }
                }

                ProductSelectedFilterOption::where('product_id', $product->id)->delete();
                foreach ($filterIds as $filterId) {
                    ProductSelectedFilterOption::create([
                        'product_id' => $product->id,
                        'filter_option_id' => (int)$filterId,
                    ]);
                }
            }
        }

        return $product;
    }
}
