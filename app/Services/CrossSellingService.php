<?php

namespace App\Services;

use App\Models\CrossSellingRelation;
use App\Models\UserCrossSelling;
use Illuminate\Support\Collection;

class CrossSellingService
{


    public function getCrossSellingItemsWithSettings($userIds, $sourceTypes, $sourceIds, $page = 'product')
    {
        $settings = UserCrossSelling::whereIn('user_id', (array) $userIds)->where('enable', true)->get();

        if ($settings->isEmpty()) {
            return ['products' => collect(), 'description' => null];
        }

        $validSettings = $settings->filter(function ($setting) use ($page) {
            if (
                ($page === 'product' && $setting->hide_on_single_product) ||
                ($page === 'cart' && !$setting->show_on_cart_page) ||
                ($page === 'checkout' && !$setting->show_on_checkout_page)
            ) {
               
                return false;
            }
            return true;
        });
        
        if ($validSettings->isEmpty()) {
            return ['products' => collect(), 'description' => null];
        }

        $query = CrossSellingRelation::query()->with('target');
        

        if (!empty($sourceTypes)) {
            $query->whereIn('source_type', (array) $sourceTypes);
        }

        if (!empty($sourceIds)) {
            $query->whereIn('source_id', (array) $sourceIds);
        }

        

        $anyHideOutOfStock = $validSettings->contains('hide_out_of_stock', true);

        if ($anyHideOutOfStock) {
            $query->whereHasMorph('target', ['App\Models\Webinar', 'App\Models\Product', 'App\Models\Blog'], function ($q, $type) {
                if ($type === 'App\Models\Product') {
                    $q->where('inventory', '>', 0);
                }
            });
        }
        
        
        

        $targets = $query->get()->map(function ($relation) {
            return $relation->target;
        })->filter();

        // dd($targets);

        $description = $validSettings->pluck('description')->filter()->first() ?? 'منتجات مقترحة';
        $display_on = $validSettings->pluck('display_on')->filter()->first();
        $slider = $validSettings->pluck('display_type_on_single_product')->filter()->first();

        if ($page === 'cart') {
            $sortType = $validSettings->pluck('product_bundle_type_cart')->filter()->first();
            $targets = $this->applySort($targets, $sortType);
            $display_on = $validSettings->pluck('display_on_cart')->filter()->first();
        } elseif ($page === 'checkout') {
            $sortType = $validSettings->pluck('product_bundle_type_checkout')->filter()->first();
            $targets = $this->applySort($targets, $sortType);
            $display_on = $validSettings->pluck('display_on_checkout')->filter()->first();
        }

        return [
            'products' => $targets,
            'description' => $description,
            'display_on' => $display_on,
            'slider' => $slider,
        ];
    }


    protected function applySort($targets, $sortType)
    {
        switch ($sortType) {
            case 1:
                return $targets->sortByDesc('quantity_ordered');
            case 2: 
                return $targets->shuffle();
            case 3:
                return $targets->sortByDesc('price');
            default:
                return $targets;
        }
    }

    // private $user;
    // private $model;
    // private $location;

    // public function __construct($user, $model, $location)
    // {
    //     $this->user = $user;
    //     $this->model = $model;
    //     $this->location = $location;
    // }

    // public function getItems()
    // {

    //     if (!$this->user || !$this->model || !$this->model->crossSellings()->exists()) {
    //         return collect();
    //     }

    //     $settings = UserCrossSelling::firstOrNew(['user_id' => $this->user]);

    //     if ($this->location == 'single') {
    //         if (!$settings['enable'] || !$settings['hide_on_single_product'] || !$this->model->crossSellings()->exists()) {
    //             return collect();
    //         } else {
    //             $result = [];
    //             $modelName = class_basename($this->model);
    //             $display_on = $settings['display_on'];
    //             $query = $this->model->crossSellings();
    //             dd($query);

    //             if ($modelName == 'Webinar') {
    //                 switch ($settings['product_bundle_type_' . $this->location]) {
    //                     case 2:
    //                         $query->inRandomOrder();
    //                         break;
    //                     case 3:
    //                         $query->join('webinars', 'cross_sellings.target_id', '=', 'webinars.id')
    //                             ->where('cross_sellings.target_type', 'App\Models\Webinar')
    //                             ->orderBy('webinars.price', 'desc');
    //                         break;
    //                     default:
    //                         $query->orderBy('created_at', 'desc');
    //                         break;
    //                 }

    //                 $views = [
    //                     1 => 'web.default.cross_up_selling.popup',
    //                     2 => 'web.default.cross_up_selling.below_cart_button',
    //                     3 => 'web.default.cross_up_selling.above_description_tab',
    //                     4 => 'web.default.cross_up_selling.below_cart_button'
    //                 ];

    //                 $view = $views[$display_on] ?? 'web.default.cross_up_selling.popup';
    //                 $result = [
    //                     'settings' => $settings,
    //                     'view' => $view,
    //                     'data' => $query->with(['target'])->get()
    //                 ];

    //                 return $result;
    //             }
    //         }
    //     }



    //     if (
    //         !$settings['enable'] ||
    //         !$settings['show_on_' . $this->location] ||
    //         !$this->model->crossSellings()->exists()
    //     ) {
    //         return collect();
    //     }



    //     $query = $this->model->crossSellings();

    //     switch ($settings['product_bundle_type_' . $this->location]) {
    //         case 1:
    //             $query->orderBy('quantity', 'desc');
    //             break;
    //         case 2:
    //             $query->inRandomOrder();
    //             break;
    //         case 3:
    //             $query->orderBy('price', 'desc');
    //             break;
    //     }

    //     return $query->get();
    // }

    // public function getDisplayType(): string|null
    // {
    //     if (!$this->user) {
    //         return null;
    //     }

    //     $settings = UserCrossSelling::firstOrNew(['user_id' => $this->user]);

    //     return $settings['product_bundle_display_type_' . $this->location] ?? 'default';
    // }
}
