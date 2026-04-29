<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Discount;
use App\Models\DiscountUser;
use App\Models\Product;
use App\Models\RegistrationPackage;
use App\Models\SpecialOffer;
use App\Models\Subscribe;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;
use App\Models\SpecialOfferProductDiscount;

class SpecialOfferProductDiscountController extends Controller
{
    // +++++++++++++++++++ index() +++++++++++++++++++
    public function index(Request $request)
    {
        $this->authorize('admin_product_discount_list');
        $query = SpecialOffer::where('discount_type', 'product')
                             ->whereNotNull('product_id'); // Corrected condition
        $specialOffersProductDiscounts = $this->filters($query, $request)
                                              ->orderBy('created_at', 'desc')
                                              ->paginate(10);
        // Start query and load translations
        $query = Product::query()->with('translations');
        $products = $query->get()->map(function ($product)
        {
            $locale = app()->getLocale();
            $title = optional($product->translate($locale))->title
                    ?? optional($product->translate('en'))->title
                    ?? 'Untitled'; // Default if no translation exists
            return [
                'id' => $product->id,
                'title' => $title,
            ];
        });
        $data = [
            'pageTitle' => "Product Discounts",
            'specialOffersProductDiscounts' => $specialOffersProductDiscounts,
            'products' => $products,
        ];
        return view('admin.financial.special_offer_product_discounts.lists', $data);
    }
    // +++++++++++++++++++ filters +++++++++++++++++++
    private function filters($query, $request)
    {
        $name = $request->get('name');
        $from = $request->get('from');
        $to = $request->get('to');
        $sort = $request->get('sort');
        $product_id = $request->get('product_id');
        $status = $request->get('status');
        // +++++++++++ filters : name +++++++++++
        if (!empty($name))
        {
            $query->where('name', 'like', "%$name%");
        }
        // +++++++++++ filters : from +++++++++++
        if (!empty($from))
        {
            $from = strtotime($from);
            $query->where('from_date', '>=', $from);
        }
        // +++++++++++ filters : to +++++++++++
        if (!empty($to))
        {
            $to = strtotime($to);
            $query->where('to_date', '<', $to);
        }
        // +++++++++++ filters : sort +++++++++++
        if (!empty($sort))
        {
            switch ($sort)
            {
                case 'percent_asc':
                    $query->orderBy('percent', 'asc');
                    break;
                case 'percent_desc':
                    $query->orderBy('percent', 'desc');
                    break;
                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'expire_at_asc':
                    $query->orderBy('to_date', 'asc');
                    break;
                case 'expire_at_desc':
                    $query->orderBy('to_date', 'desc');
                    break;
            }
        }
        // +++++++++++ filters : products +++++++++++
        if (!empty($product_id))
        {
            $query->where('product_id', $product_id);
        }
        // +++++++++++ filters : status +++++++++++
        if (!empty($status))
        {
            $query->where('status', $status);
        }

        return $query;
    }
    // +++++++++++++++++++ create() +++++++++++++++++++
    public function create()
    {
        $this->authorize('admin_product_discount_create');
        $data = [
            'pageTitle' => "New Product Discount",
        ];
        return view('admin.financial.special_offer_product_discounts.new', $data);
    }
    // +++++++++++++++++++ store() +++++++++++++++++++
    public function store(Request $request)
    {
        $this->authorize('admin_product_discount_create');
        $request->validate([
            'name' => 'required|string|max:64|unique:special_offer_product_discounts,name',
            'percent' => 'required|integer|min:0|max:100',
            'status' => 'nullable|in:active,inactive',
            'product_type' => 'nullable|in:virtual,physical',
            'from_date' => 'required|date_format:Y-m-d H:i',
            'to_date' => 'required|date_format:Y-m-d H:i|after:from_date',
        ]);
        try
        {
            $data = $request->all();
            // Convert dates to UTC timestamps
            $fromDate = convertTimeToUTCzone($data['from_date'], getTimezone());
            $toDate = convertTimeToUTCzone($data['to_date'], getTimezone());

            SpecialOffer::create([
                'creator_id' => auth()->id(),
                'name' => $data['name'],
                'product_id' => $data['product_id'] ?? null,
                'discount_type' => "product",
                'percent' => $data['percent'],
                'status' => $data['status'],
                'created_at' => time(),
                'from_date' => $fromDate->getTimestamp(),
                'to_date' => $toDate->getTimestamp(),
            ]);

            return redirect(getAdminPanelUrl() . '/financial/special_offer_product_discounts')->with('success', 'Product discount created successfully.');
        }
        catch (\Exception $e)
        {
            Log::info("Error : ".$e->getMessage());
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
    // +++++++++++++++++++ edit() +++++++++++++++++++
    public function edit($id)
    {
        $this->authorize('admin_product_discount_edit');
        $productDiscounts = SpecialOffer::findOrFail($id);
        $data = [
            'pageTitle' => "Edit Product Discount",
            'productDiscounts' => $productDiscounts,
        ];
        return view('admin.financial.special_offer_product_discounts.new', $data);
    }
    // +++++++++++++++++++ update() +++++++++++++++++++
    public function update(Request $request, $id)
    {
        $this->authorize('admin_product_discount_edit');
        $this->validate($request, [
            'percent' => 'required',
            'status' => 'nullable|in:active,inactive',
            'from_date' => 'required',
            'to_date' => 'required',
        ]);
        $specialOfferProductDiscount = SpecialOffer::findOrfail($id);
        $data = $request->all();
        $fromDate = convertTimeToUTCzone($data['from_date'], getTimezone());
        $toDate = convertTimeToUTCzone($data['to_date'], getTimezone());
        $specialOfferProductDiscount->update([
            'creator_id' => auth()->id(),
            'name' => $data['name'],
            'product_id' => $data['product_id'] ?? null,
            'discount_type' => "product",
            'percent' => $data['percent'],
            'status' => $data['status'],
            'created_at' => time(),
            'from_date' => $fromDate->getTimestamp(),
            'to_date' => $toDate->getTimestamp(),
        ]);
        return redirect(getAdminPanelUrl() . '/financial/special_offer_product_discounts');
    }
    // +++++++++++++++++++ destroy() +++++++++++++++++++
    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_product_discount_delete');
        SpecialOffer::findOrfail($id)->delete();
        return redirect()->back();
    }
}
