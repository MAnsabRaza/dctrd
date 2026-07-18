<?php

namespace App\Http\Controllers\Api\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Flow 3: Rocket LMS → ERP → External Marketplace
 * Vendor khud supplier ban ke apna product feed doosri marketplace ko deta hai.
 *
 * Route: domain.com/_ERP/api/v1/supplier-feed   (middleware: erp.key:dropshipping)
 * Alag API key (type=dropshipping) se protected — import_export key se access nahi hoga.
 */
class SupplierFeedController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = $request->attributes->get('erp_vendor_id');

        // TODO: apne Product/Webinar/Booking model se vendor ke "export_ability_enabled"
        // checklist ke mutabiq (Dropship Price, Stock Availability, Product Images, etc.)
        // published items nikaal kar feed format mein return karo. Schema is document mein
        // nahi diya gaya isliye placeholder chhoda hai — is function ko apne Product model
        // ke sath map karke complete karo.

        $perPage = min((int) $request->get('per_page', 50), 200);

        return response()->json([
            'vendor_id' => $vendorId,
            'data'      => [],
            'per_page'  => $perPage,
            'page'      => (int) $request->get('page', 1),
        ]);
    }
}
