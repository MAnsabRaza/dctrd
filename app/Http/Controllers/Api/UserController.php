<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get authenticated user's address from profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAddress(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $address = [
                'address' => $user->address,
                'address_line' => $user->address,
                'city' => $user->city ? $user->city->title : ($user->city_name ?? ''),
                'state' => $user->province ? $user->province->title : ($user->state ?? ''),
                'country' => $user->country ? $user->country->title : ($user->country_name ?? ''),
                'postal_code' => $user->postal_code ?? '',
                'zip_code' => $user->zip_code ?? '',
                'province' => $user->province ? $user->province->title : '',
            ];

            // Try to get more detailed address if available
            if (!empty($user->address_line)) {
                $address['address_line'] = $user->address_line;
            }

            return response()->json([
                'success' => true,
                'address' => $address,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
