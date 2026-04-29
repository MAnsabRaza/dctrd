<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
//use App\Models\PurchaseCode;
//use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseCodeController extends Controller
{
    

    public function show()
    {
        $purchaseCode = PurchaseCode::getPurchaseCode();
        $licenseType = PurchaseCode::getLicenseType();
        return view('purchase_code.enter', compact('purchaseCode', 'licenseType'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_code' => 'required|string|size:36'
        ]);

        $purchaseCode = $request->input('purchase_code');
        $licenseType = 'Extended License';

        
        PurchaseCode::updatePurchaseCode($purchaseCode, PurchaseCode::TYPE_MAIN, $licenseType);
        
        return redirect('/')
            ->with('success', 'Purchase code successfully saved.');
    }
}