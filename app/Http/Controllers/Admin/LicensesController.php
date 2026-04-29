<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseCode;
//use App\Services\LicenseService;
use App\Services\PluginBundleLicenseService;
use App\Services\ThemeBuilderLicenseService;
use App\Services\MobileAppLicenseService;

class LicensesController extends Controller
{
    protected $mainLicenseService;
    protected $pluginLicenseService;
    protected $themeBuilderLicenseService;
    protected $mobileAppLicenseService;


    public function index()
    {
        $mainLicense = [
            'code' => PurchaseCode::getPurchaseCode(),
            'license_type' => PurchaseCode::getLicenseType(),
            'status' => true, 
            'message' => null
        ];

        $pluginBundleLicense = [
            'code' => PurchaseCode::getPluginBundlePurchaseCode(),
            'license_type' => PurchaseCode::getPluginBundleLicenseType(),
            'status' => true, 
            'message' => null
        ];

        $themeBuilderLicense = [
            'code' => PurchaseCode::getThemeBuilderPurchaseCode(),
            'license_type' => PurchaseCode::getThemeBuilderLicenseType(),
            'status' => true, 
            'message' => null
        ];
        
        $mobileAppLicense = [
            'code' => PurchaseCode::getMobileAppPurchaseCode(),
            'license_type' => PurchaseCode::getMobileAppLicenseType(),
            'status' => true, 
            'message' => null
        ];

        $data = [
            'pageTitle' => trans('admin/main.licenses'),
            'mainLicense' => $mainLicense,
            'pluginBundleLicense' => $pluginBundleLicense,
            'themeBuilderLicense' => $themeBuilderLicense,
            'mobileAppLicense' => $mobileAppLicense,
        ];

        return view('admin.licenses.index', $data);
    }
}