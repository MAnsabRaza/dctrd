<?php

namespace App\Http\Controllers\Api\Erp;

use App\Http\Controllers\Controller;
use App\Models\ErpIdMapping;
use App\User;
use Illuminate\Http\Request;

/**
 * Flow 1: Rocket LMS → ERP → Accounting  (push, from Rocket side jobs)
 * Flow 2: Supplier feed → ERP → Rocket LMS (pull, ERP calls in to hand us client/order data)
 *
 * Route: domain.com/_ERP/api/v1/clients   (middleware: erp.key:import_export)
 * Vendor credential attribute set by VerifyErpApiKey middleware: $request->attributes->get('erp_vendor_id')
 */
class ClientsController extends Controller
{
    /**
     * ERP se Rocket LMS ko clients ki list bhejo (agar ERP pull karna chahe)
     */
    public function index(Request $request)
    {
        $vendorId = $request->attributes->get('erp_vendor_id');

        $mappings = ErpIdMapping::where('vendor_id', $vendorId)
            ->where('entity_type', 'customer')
            ->get(['local_id', 'remote_id', 'last_synced_at']);

        return response()->json(['data' => $mappings]);
    }

    /**
     * ERP se naya/updated client push hokar aaye — yahan Rocket User row update/create karo
     * aur mapping table mein remote_id save karo.
     */
    public function store(Request $request)
    {
        $vendorId = $request->attributes->get('erp_vendor_id');

        $data = $request->validate([
            'remote_id'   => 'required|string',
            'full_name'   => 'required|string|max:255',
            'email'       => 'nullable|email',
            'mobile'      => 'nullable|string',
        ]);

        // Existing mapping se local user dhoondo
        $mapping = ErpIdMapping::where('vendor_id', $vendorId)
            ->where('entity_type', 'customer')
            ->where('remote_id', $data['remote_id'])
            ->first();

        if ($mapping) {
            $user = User::find($mapping->local_id);
        } else {
            $user = User::create([
                'full_name'  => $data['full_name'],
                'email'      => $data['email'] ?? null,
                'mobile'     => $data['mobile'] ?? null,
                'role_name'  => 1,
                'password'   => User::generatePassword(str()->random(12)),
                'verified'   => true,
                'created_at' => time(),
            ]);

            $mapping = ErpIdMapping::create([
                'vendor_id'   => $vendorId,
                'entity_type' => 'customer',
                'local_id'    => $user->id,
                'remote_id'   => $data['remote_id'],
                'last_synced_at' => now(),
            ]);
        }

        return response()->json([
            'success'  => true,
            'local_id' => $user->id,
        ]);
    }
}
