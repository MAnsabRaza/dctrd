<?php

namespace App\Http\Controllers\Panel\Store;

use App\Enums\UploadSource;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Store\Traits\MyProductsListsTrait;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductMedia;
use App\Models\ProductOrder;
use App\Models\ProductSelectedFilterOption;
use App\Models\ProductSpecification;
use App\Models\ProductSpecificationCategory;
use App\Models\ErpCredential;
use App\Models\Translation\ProductTranslation;
use App\Services\LocationService;
use App\Services\Erp\ErpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use MyProductsListsTrait;

    public function index(Request $request)
    {
        $this->authorize("panel_products_lists");

        $user = auth()->user();

        if ((!$user->isTeacher() and !$user->isOrganization()) or !$user->checkCanAccessToStore()) {
            abort(403);
        }

        $query = Product::query()->where('creator_id', $user->id);
        $query = $this->handleFilters($request, $query);

        $pageListData = $this->getPageListData($request, $query);

        if ($request->ajax()) {
            return $pageListData;
        }

        $topStats = $this->handlePageTopStats($user);

        $pageTitle = trans('panel.my_purchases');
        $breadcrumbs = [
            ['text' => trans('update.platform'), 'url' => '/'],
            ['text' => trans('panel.dashboard'), 'url' => '/panel'],
            ['text' => $pageTitle, 'url' => null],
        ];

        $data = [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            ...$topStats,
            ...$pageListData,
        ];

        return view('design_1.panel.store.my_products.index', $data);
    }

    public function create()
    {
        $this->authorize("panel_products_create");

        $user = auth()->user();

        if (!$user->checkCanAccessToStore()) {
            abort(403);
        }

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $userPackage = new UserPackage();
        $userCoursesCountLimited = $userPackage->checkPackageLimit('product_count');

        if ($userCoursesCountLimited) {
            session()->put('registration_package_limited', $userCoursesCountLimited);

            return redirect()->back();
        }

        $data = [
            'pageTitle' => trans('update.new_product_page_title'),
            'currentStep' => 1,
            'stepCount' => 5,
        ];

        return view('design_1.panel.store.create_product.index', $data);
    }

    public function store(Request $request)
    {
        $this->authorize("panel_products_create");

        $user = auth()->user();

        if (!$user->checkCanAccessToStore()) {
            abort(403);
        }

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $userPackage = new UserPackage();
        $userCoursesCountLimited = $userPackage->checkPackageLimit('product_count');

        if ($userCoursesCountLimited) {
            session()->put('registration_package_limited', $userCoursesCountLimited);

            return redirect()->back();
        }

        $rules = [
            'type' => 'required|in:' . implode(',', Product::$productTypes),
            'title' => 'required|max:255',
            'seo_description' => 'required|max:255',
            'summary' => 'required',
            'description' => 'required',
            'location_enabled' => 'nullable|in:on',
            'address_line' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'checkout_message' => 'nullable|string',
            'reviewer_message' => 'nullable|string',
        ];

        $this->validate($request, $rules);

        $data = $request->all();
        $data['location_enabled'] = !empty($data['location_enabled']) && $data['location_enabled'] === 'on';

      $product = Product::create([
            'creator_id' => $user->id,
            'type' => $data['type'],
            'slug' => Product::makeSlug($data['title']),
            'category_id' => null,
            'price' => null,
            'unlimited_inventory' => false,
            'ordering' => (!empty($data['ordering']) and $data['ordering'] == 'on'),
            'inventory' => null,
            'inventory_warning' => null,
            'inventory_updated_at' => null,
            'delivery_fee' => null,
            'checkout_message' => $data['checkout_message'] ?? null,
            'reviewer_message' => $data['reviewer_message'] ?? null,
            'delivery_estimated_time' => null,
            'message_for_reviewer' => null,
            'location_enabled' => $data['location_enabled'],
            'qr_enabled' => $request->boolean('qr_enabled'),
            'status' => ((!empty($data['draft']) and $data['draft'] == 1) or (!empty($data['get_next']) and $data['get_next'] == 1)) ? Product::$draft : Product::$pending,
            'updated_at' => time(),
            'created_at' => time(),
        ]);

        if ($product) {
            if (!empty($data['location_enabled']) || !empty($data['address_line']) || !empty($data['city']) || !empty($data['state']) || !empty($data['country']) || !empty($data['postal_code']) || !empty($data['lat']) || !empty($data['lng'])) {
                app(LocationService::class)->saveLocation($product, [
                    'location_enabled' => $data['location_enabled'],
                    'address_line' => $data['location_enabled'] ? ($data['address_line'] ?? null) : null,
                    'city' => $data['location_enabled'] ? ($data['city'] ?? null) : null,
                    'state' => $data['location_enabled'] ? ($data['state'] ?? null) : null,
                    'country' => $data['location_enabled'] ? ($data['country'] ?? null) : null,
                    'postal_code' => $data['location_enabled'] ? ($data['postal_code'] ?? null) : null,
                    'lat' => $data['location_enabled'] ? ($data['lat'] ?? null) : null,
                    'lng' => $data['location_enabled'] ? ($data['lng'] ?? null) : null,
                ]);
            }
            ProductTranslation::updateOrCreate([
                'product_id' => $product->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'seo_description' => $data['seo_description'],
                'summary' => $data['summary'],
                'description' => $data['description'],
            ]);
        }

        $notifyOptions = [
            '[u.name]' => $user->full_name,
            '[item_title]' => $product->title,
            '[content_type]' => trans('update.product'),
        ];
        sendNotification("new_item_created", $notifyOptions, 1);

        $url = '/panel/store/products';
        if ($data['get_next'] == 1) {
            $url = '/panel/store/products/' . $product->id . '/step/2';
        }

        return redirect($url);
    }

    public function edit(Request $request, $id, $step = 1)
    {
        $this->authorize("panel_products_create");

        $user = auth()->user();

        if (!$user->checkCanAccessToStore()) {
            abort(403);
        }

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $stepCount = 5;

        if ($step > $stepCount) {
            return redirect("/panel/store/products/{$id}/step/{$stepCount}");
        }

        $locale = $request->get('locale', app()->getLocale());

        $query = Product::where('id', $id)
            ->where('creator_id', $user->id)
            ->with([
                'files' => function ($query) {
                    $query->orderBy('order', 'asc');
                }
            ]);

        if ($step == 4) {
            $query->with([
                'category' => function ($query) {
                    $query->with([
                        'filters' => function ($query) {
                            $query->with('options');
                        }
                    ]);
                },
                'selectedSpecifications' => function ($query) {
                    $query->orderBy('order', 'asc');
                    $query->with('specification');
                },
                'faqs' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
            ]);
        }

        $product = $query->first();

        if (empty($product)) {
            abort(404);
        }

               $data = [
            'pageTitle' => trans('update.edit_product') . ' | ' . $product->title,
            'product' => $product,
            'currentStep' => $step,
            'locale' => mb_strtolower($locale),
            'defaultLocale' => getDefaultLocale(),
            'stepCount' => $stepCount,
        ];

        // $productCategories is needed on step 1 (ERP Category/Subcategory mapping)
        // AND on step 2 (main product category select), so load it unconditionally
        // instead of only inside the step==2 branch.
        $productCategories = ProductCategory::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $data['productCategories'] = $productCategories;

        if ($step == 2) {
            $productCategoryFilters = !empty($product->category) ? $product->category->filters : [];

            if (empty($product->category) and !empty($request->old('category_id'))) {
                $category = ProductCategory::where('id', $request->old('category_id'))->first();

                if (!empty($category)) {
                    $productCategoryFilters = $category->filters;
                }
            }

            $data['productCategoryFilters'] = $productCategoryFilters;
        } elseif ($step == 4) {
            $specificationIds = ProductSpecificationCategory::where('category_id', $product->category_id)
                ->pluck('specification_id')
                ->toArray();

            $data['productSpecifications'] = ProductSpecification::whereIn('id', $specificationIds)
                ->get();
        }

        return view('design_1.panel.store.create_product.index', $data);
    }

    public function erpPostSaleCategories()
    {
        return $this->fetchErpOptions('categories');
    }

    public function erpPostSaleStaff()
    {
        return $this->fetchErpOptions('staff');
    }

    private function normalizeErpPostSaleData(array $data): array
    {
        $enabled = !empty($data['erp_post_sale_enabled']) && $data['erp_post_sale_enabled'] === 'on';

        $data['erp_post_sale_enabled'] = $enabled;
        $data['erp_category_id'] = $enabled ? ($data['erp_category_id'] ?? null) : null;
        $data['erp_category_name'] = $enabled ? ($data['erp_category_name'] ?? null) : null;
        $data['erp_subcategory_id'] = $enabled ? ($data['erp_subcategory_id'] ?? null) : null;
        $data['erp_subcategory_name'] = $enabled ? ($data['erp_subcategory_name'] ?? null) : null;
        $data['erp_staff_ids'] = $enabled ? array_values(array_filter($data['erp_staff_ids'] ?? [], fn ($id) => is_numeric($id))) : null;

        $templates = preg_split('/\r\n|\r|\n/', (string) ($data['erp_task_templates'] ?? ''));
        $data['erp_task_templates'] = $enabled
            ? array_values(array_filter(array_map('trim', $templates)))
            : null;
        $data['erp_task_templates_raw'] = null;

        return $data;
    }

    private function fetchErpOptions(string $type)
    {
        $user = auth()->user();

        if (empty($user) || (!$user->isTeacher() && !$user->isOrganization()) || !$user->checkCanAccessToStore()) {
            abort(403);
        }

        $credential = ErpCredential::where('vendor_id', $user->id)
            ->where('type', 'import_export')
            ->where('is_active', true)
            ->first();

        if (empty($credential) || empty($credential->base_url) || empty($credential->api_key)) {
            return response()->json([
                'success' => false,
                'message' => 'Perfex ERP credential is not configured or inactive.',
                'data' => [],
            ], 422);
        }

        $client = new ErpClient($credential);
        $result = $type === 'staff' ? $client->getStaff() : $client->getPostSaleCategories();

        return response()->json([
            'success' => !empty($result['success']),
            'message' => $result['error'] ?? null,
            'data' => $result['body']['data'] ?? $result['body'] ?? [],
        ], !empty($result['success']) ? 200 : 502);
    }

    public function update(Request $request, $id)
    {
        $this->authorize("panel_products_create");

        $user = auth()->user();

        if (!$user->checkCanAccessToStore()) {
            abort(403);
        }

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $rules = [];
        $data = $request->all();
        $currentStep = $data['current_step'];
        $getStep = $data['get_step'];
        $getNextStep = (!empty($data['get_next']) and $data['get_next'] == 1);
        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);

        $product = Product::where('id', $id)
            ->where('creator_id', $user->id)
            ->first();

        if (empty($product)) {
            abort(404);
        }

        if ($currentStep == 1) {
            $rules = [
                'location_enabled' => 'nullable|in:on',
                'address_line' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'lat' => 'nullable|numeric',
                'lng' => 'nullable|numeric',
                 'checkout_message' => 'nullable|string',
                 'reviewer_message' => 'nullable|string',
                'erp_post_sale_enabled' => 'nullable|in:on',
                'erp_category_id' => 'nullable|string|max:255',
                'erp_category_name' => 'nullable|string|max:255',
                'erp_subcategory_id' => 'nullable|string|max:255',
                'erp_subcategory_name' => 'nullable|string|max:255',
                'erp_staff_ids' => 'nullable|array',
                'erp_staff_ids.*' => 'nullable|integer',
                'erp_task_templates' => 'nullable|string',
            ];

            $data['location_enabled'] = !empty($data['location_enabled']) && $data['location_enabled'] === 'on';
            $data = $this->normalizeErpPostSaleData($data);
        }

        if ($currentStep == 2) {
            $rules = [
                'category_id' => 'required',
                'inventory' => 'required_without:unlimited_inventory'
            ];

            $data['unlimited_inventory'] = (!empty($data['unlimited_inventory']) and $data['unlimited_inventory'] == 'on');
            $data['location_enabled'] = !empty($data['location_enabled']);
        }

        $this->validate($request, $rules);

        $productRulesRequired = false;
        if (($currentStep == 5 and !$getNextStep and !$isDraft) or (!$getNextStep and !$isDraft)) {
            $productRulesRequired = empty($data['rules']);
        }

        $data['status'] = ($isDraft or $productRulesRequired) ? Product::$draft : Product::$pending;
        $data['updated_at'] = time();
if ($currentStep == 1) {
            $data['ordering'] = (!empty($data['ordering']) and $data['ordering'] == 'on');
            $data['qr_enabled'] = $request->boolean('qr_enabled');

            ProductTranslation::updateOrCreate([
                'product_id' => $product->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'seo_description' => $data['seo_description'],
                'summary' => $data['summary'],
                'description' => $data['description'],
            ]);
        } elseif ($currentStep == 2) {
            $data['price'] = !empty($data['price']) ? convertPriceToDefaultCurrency($data['price']) : null;
            $data['delivery_fee'] = !empty($data['delivery_fee']) ? convertPriceToDefaultCurrency($data['delivery_fee']) : null;

            $inventory = $data['inventory'];
            $productAvailability = $product->getAvailability();

            if ($inventory != $productAvailability) {
                $data['inventory_updated_at'] = time();
            }

            ProductSelectedFilterOption::where('product_id', $product->id)->delete();

            $filters = $request->get('filters', null);
            if (!empty($filters) and is_array($filters)) {
                foreach ($filters as $filter) {
                    ProductSelectedFilterOption::create([
                        'product_id' => $product->id,
                        'filter_option_id' => $filter
                    ]);
                }
            }
        } elseif ($currentStep == 3) {
            $this->handleProductImages($request, $product);
        }

        unset($data['_token'],
            $data['current_step'],
            $data['draft'],
            $data['get_next'],
            $data['locale'],
            $data['get_step'],
            $data['ajax'],
            $data['title'],
            $data['description'],
            $data['seo_description'],
            $data['summary'],
            $data['thumbnail'],
            $data['images'],
            $data['video_demo'],
            $data['filters'],
            $data['erp_task_templates_raw'],
        );

        if (isset($product->salesCountCache)) {
            unset($product->salesCountCache);
        }

        if (isset($product->availabilityCount)) {
            unset($product->availabilityCount);
        }

        $product->update($data);

        if ($currentStep == 1 && array_key_exists('location_enabled', $data)) {
            app(LocationService::class)->saveLocation($product, [
                'location_enabled' => $data['location_enabled'] ?? false,
                'address_line' => $data['location_enabled'] ? ($data['address_line'] ?? null) : null,
                'city' => $data['location_enabled'] ? ($data['city'] ?? null) : null,
                'state' => $data['location_enabled'] ? ($data['state'] ?? null) : null,
                'country' => $data['location_enabled'] ? ($data['country'] ?? null) : null,
                'postal_code' => $data['location_enabled'] ? ($data['postal_code'] ?? null) : null,
                'lat' => $data['location_enabled'] ? ($data['lat'] ?? null) : null,
                'lng' => $data['location_enabled'] ? ($data['lng'] ?? null) : null,
            ]);
        }

        $url = '/panel/store/products';
        if ($getNextStep) {
            $nextStep = (!empty($getStep) and $getStep > 0) ? $getStep : $currentStep + 1;

            $url = '/panel/store/products/' . $product->id . '/step/' . (($nextStep <= 5) ? $nextStep : 5);
        }

        if ($productRulesRequired) {
            $url = '/panel/store/products/' . $product->id . '/step/5';

            return redirect($url)->withErrors(['rules' => trans('validation.required', ['attribute' => 'rules'])]);
        }

        if (!$getNextStep and !$isDraft and !$productRulesRequired) {
            $notifyOptions = [
                '[u.name]' => $user->full_name,
                '[item_title]' => $product->title,
                '[content_type]' => trans('update.product'),
            ];
            sendNotification("content_review_request", $notifyOptions, 1);
        }

        return redirect($url);
    }

    private function handleProductImages(Request $request, $product)
    {
        $user = auth()->user();

        if (!empty($request->file('thumbnail'))) {
            $thumbnail = $this->uploadFile($request->file('thumbnail'), "products/{$product->id}", 'thumbnail', $product->creator_id);

            ProductMedia::updateOrCreate([
                'creator_id' => $user->id,
                'product_id' => $product->id,
                'type' => ProductMedia::$thumbnail,
            ], [
                'path' => $thumbnail,
                'created_at' => time(),
            ]);
        }

        if (!empty($request->file('images'))) {
            ProductMedia::where('creator_id', $user->id)
                ->where('product_id', $product->id)
                ->where('type', ProductMedia::$image)
                ->delete();

            foreach ($request->file('images') as $k => $image) {
                if (!empty($image)) {
                    $name = "image_" . $k + 1;
                    $path = $this->uploadFile($image, "products/{$product->id}", $name, $product->creator_id);

                    ProductMedia::create([
                        'creator_id' => $user->id,
                        'product_id' => $product->id,
                        'type' => ProductMedia::$image,
                        'path' => $path,
                        'created_at' => time(),
                    ]);
                }
            }
        }

        $videoDemo = null;

        ProductMedia::where('creator_id', $user->id)
            ->where('product_id', $product->id)
            ->where('type', ProductMedia::$video)
            ->delete();

        if (in_array($request->get('video_demo_source'), UploadSource::urlPathItems) and !empty($request->get('demo_video_path'))) {
            $videoDemo = $request->get('demo_video_path');
        } elseif ($request->get('video_demo_source') == UploadSource::UPLOAD and !empty($request->file('demo_video_local'))) {
            $videoDemo = $this->uploadFile($request->file('demo_video_local'), "products/{$product->id}", 'video', $product->creator_id);
        } elseif ($request->get('video_demo_source') == UploadSource::S3 and !empty($request->file('demo_video_local'))) {
            $videoDemo = $this->uploadFile($request->file('demo_video_local'), "products/{$product->id}", 'video', $product->creator_id, 'minio');
        }

        if (!empty($videoDemo)) {
            ProductMedia::updateOrCreate([
                'creator_id' => $user->id,
                'product_id' => $product->id,
                'type' => ProductMedia::$video,
            ], [
                'path' => $videoDemo,
                'created_at' => time(),
            ]);
        }
    }
    public function regenerateQr($id)
{
    $user = auth()->user();

    $product = Product::where('id', $id)
        ->where('creator_id', $user->id)
        ->first();

    if (empty($product)) {
        abort(404);
    }

    if (empty($product->qr_enabled)) {
        return back()->with('error', 'QR Code is not enabled for this product.');
    }

    app(\App\Services\PusClient::class)->createLink($product);

    return redirect('/panel/store/products/' . $id . '/step/1')
        ->with('success', 'QR Code and Short URL re-generated successfully.');
}

    public function destroy(Request $request, $id)
    {
        $this->authorize("panel_products_delete");

        $user = auth()->user();

        if (!$user->checkCanAccessToStore()) {
            abort(403);
        }

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        if (!canDeleteContentDirectly()) {
            if ($request->ajax()) {
                return response()->json([], 422);
            } else {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('update.it_is_not_possible_to_delete_the_content_directly'),
                    'status' => 'error'
                ];
                return redirect()->back()->with(['toast' => $toastData]);
            }
        }

        $product = Product::where('id', $id)
            ->where('creator_id', $user->id)
            ->first();

        if (!$product) {
            abort(404);
        }

        $product->delete();

        return response()->json([
            'code' => 200,
            'redirect_to' => $request->get('redirect_to')
        ], 200);
    }

    public function deleteMediaById(Request $request, $productId, $mediaId)
    {
        $this->authorize("panel_products_delete");

        $user = auth()->user();

        if (!$user->checkCanAccessToStore()) {
            abort(403);
        }

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $product = Product::query()->where('id', $productId)
            ->where('creator_id', $user->id)
            ->first();

        if (!empty($product)) {
            $media = $product->media()->where('type', ProductMedia::$image)
                ->where('id', $mediaId)
                ->first();

            if (!empty($media)) {
                $media->delete();

                return response()->json([
                    'code' => 200,
                    'redirect_to' => $request->get('redirect_to')
                ], 200);
            }
        }

        return response()->json([], 422);
    }

    public function getContentItemByLocale(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'item_id' => 'required',
            'locale' => 'required',
            'relation' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        $product = Product::where('id', $id)
            ->where('creator_id', $user->id)
            ->first();

        if (!empty($product)) {

            $itemId = $data['item_id'];
            $locale = $data['locale'];
            $relation = $data['relation'];

            if (!empty($product->$relation)) {
                $item = $product->$relation->where('id', $itemId)->first();

                if (!empty($item)) {
                    foreach ($item->translatedAttributes as $attribute) {
                        try {
                            $item->$attribute = $item->translate(mb_strtolower($locale))->$attribute;
                        } catch (\Exception $e) {
                            $item->$attribute = null;
                        }
                    }

                    return response()->json([
                        'item' => $item
                    ], 200);
                }
            }
        }

        abort(403);
    }

    public function getFilesModal($id)
    {
        $user = auth()->user();

        $product = Product::where('id', $id)->first();

        if (!empty($product) and !empty($product->files) and count($product->files) and $product->checkUserHasBought()) {
            $data = [
                'product' => $product
            ];

            $html = (string)view("web.default.products.includes.tabs.files", $data);

            return response()->json([
                'code' => 200,
                'html' => $html
            ]);
        }

        return response()->json([], 422);
    }

    public function search(Request $request)
    {
        $term = $request->get('term', null);
        $option = $request->get('option', null);
        $itemId = $request->get('item_id', null);

        if (!empty($term)) {
            $query = Product::query()->select('id', 'creator_id')
                ->where('status', 'active')
                ->whereTranslationLike('title', '%' . $term . '%')
                ->with([
                    'creator' => function ($query) {
                        $query->select('id', 'full_name');
                    }
                ]);

            if (!empty($itemId)) {
                $query->where('id', '!=', $itemId);
            }

            $products = $query->get();

            foreach ($products as $product) {
                $product->title .= ' - ' . $product->creator->full_name;
            }
            return response()->json($products, 200);
        }

        return response('', 422);
    }

}
