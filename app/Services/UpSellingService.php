<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\UserUpSelling;
use App\Models\VendorUpsellSetting;
use App\Models\Webinar;
use Illuminate\Support\Facades\DB;

class UpSellingService
{

    public function getUpsellItems(int $userId, int $currentItemId, string $itemType , string $page = 'single_page')
    {
        $settings = UserUpSelling::where('user_id', $userId)->first();

        if (!$settings || !$settings->enable) return collect();

        if ($page == 'cart') {
            if (!$settings || $settings->hide_on_cart_page) return collect();
        }
        
        if ($page == 'checkout') {
            if (!$settings || $settings->hide_on_checkout_page) return collect();
        }

        $query = $this->getItemQuery($itemType);

        if ($settings->exclude_products_upsell) {
            $excludeProducts = json_decode($settings->exclude_products_upsell, true);
            if ($itemType == 'product') {
                $query->whereNotIn('products.id', $excludeProducts);
            } elseif ($itemType == 'course') {
                $query->whereNotIn('webinars.id', $excludeProducts);
            }
        }
        


        if ($settings->hide_out_of_stock && $itemType == 'product') {
            $query->where('inventory', '>', 0);
        }


        if ($settings->product_same_category) {
            $currentItem = $this->getItemById($itemType, $currentItemId);
            if ($currentItem) {
                $query->where('category_id', $currentItem->category_id);
            }
        }



        if ($settings->sort_by == 1) {
            if ($itemType == 'product') {
                $query->leftJoin('product_orders', 'products.id', '=', 'product_orders.product_id')
                      ->select('products.*', DB::raw('COUNT(product_orders.id) as sales_count'))
                      ->groupBy('products.id')
                      ->orderByDesc('sales_count');
            } elseif ($itemType == 'course') {
                $query->leftJoin('sales', 'webinars.id', '=', 'sales.webinar_id')
                      ->select('webinars.*', DB::raw('COUNT(sales.id) as sales_count'))
                      ->groupBy('webinars.id')
                      ->orderByDesc('sales_count');
            }
        } elseif ($settings->sort_by == 2) {
            if ($itemType == 'product') {
                $query->orderBy('products.created_at', 'desc');
            } elseif ($itemType == 'course') {
                $query->orderBy('webinars.created_at', 'desc');
            }
        } elseif ($settings->sort_by == 3) {
            if ($itemType == 'product') {
                $query->orderBy('products.price', 'asc');
            } elseif ($itemType == 'course') {
                $query->orderBy('webinars.price', 'asc');
            }
        }
        


        return $query->get();
    }

    private function getItemQuery(string $itemType)
    {
        if ($itemType === 'product') {
            return Product::where('products.status','active');
        } elseif ($itemType === 'course') {
            return Webinar::where('products.status','active');
        }

        return collect();
    }


    private function getItemById(string $itemType, int $itemId)
    {
        if ($itemType === 'product') {
            return Product::find($itemId);
        } elseif ($itemType === 'course') {
            return Webinar::find($itemId);
        }

        return null;
    }
}
