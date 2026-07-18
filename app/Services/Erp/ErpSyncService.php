<?php

namespace App\Services\Erp;

use App\Models\ErpCredential;
use App\Models\ErpIdMapping;
use App\Models\ErpSyncLog;
use App\User;

/**
 * Real-time sync: Rocket LMS entity → Perfex ERP.
 * Vendor subscribe kare ya na kare, ye service hamesha chalti hai (silent sync);
 * sirf agar credential is_active=true hai to vendor khud apne ERP panel mein bhi dekh sakta hai.
 */
class ErpSyncService
{
    /**
     * @param string $entityType customer|product|order|booking|payment
     * @param int    $localId    Rocket LMS ka local record id
     * @param array  $payload    ERP ko bheji jane wali mapped data
     */
    public function sync(int $vendorId, string $entityType, int $localId, array $payload, string $action = 'create'): ErpSyncLog
    {
        $log = ErpSyncLog::create([
            'vendor_id'       => $vendorId,
            'entity_type'     => $entityType,
            'local_id'        => $localId,
            'action'          => $action,
            'status'          => 'pending',
            'attempts'        => 1,
            'request_payload' => $payload,
        ]);

        $credential = ErpCredential::where('vendor_id', $vendorId)
            ->where('type', 'import_export')
            ->first();

        if (empty($credential) || empty($credential->base_url) || empty($credential->api_key)) {
            $log->update([
                'status'        => 'failed',
                'error_message' => 'ERP credential (base_url/api_key) configured nahi hai.',
            ]);

            return $log;
        }

        $mapping = ErpIdMapping::firstOrNew([
            'vendor_id'   => $vendorId,
            'entity_type' => $entityType,
            'local_id'    => $localId,
        ]);

        $client = new ErpClient($credential);

        $result = match ($entityType) {
            'customer' => $client->pushClient($payload, $mapping->remote_id),
            'product'  => $client->pushItem($payload, $mapping->remote_id),
            'order'    => $client->pushInvoice($payload, $mapping->remote_id),
            'booking'  => $client->pushAppointment($payload, $mapping->remote_id),
            'payment'  => $client->pushPayment($payload, $mapping->remote_id),
            default    => ['success' => false, 'body' => [], 'error' => 'Unknown entity_type'],
        };

        if (!empty($result['success'])) {
            $remoteId = $result['body']['id'] ?? $mapping->remote_id;

            $mapping->fill([
                'remote_id'      => $remoteId,
                'last_synced_at' => now(),
            ])->save();

            $log->update([
                'status'            => 'success',
                'remote_id'         => $remoteId,
                'response_payload'  => $result['body'] ?? [],
            ]);
        } else {
            $log->update([
                'status'        => 'failed',
                'response_payload' => $result['body'] ?? [],
                'error_message' => $result['error'] ?? ('HTTP ' . ($result['status'] ?? 0)),
            ]);
        }

        return $log;
    }

    /**
     * Helper mappers — Rocket model → ERP payload shape.
     * In functions ko job/controller se call karo before sync().
     */
    public function mapCustomer(User $user): array
    {
        return [
            'company'    => $user->full_name,
            'email'      => $user->email,
            'phonenumber'=> $user->mobile,
        ];
    }

    public function mapPayment(array $sale): array
    {
        return [
            'amount' => $sale['amount'] ?? 0,
            'date'   => $sale['created_at'] ?? now()->toDateString(),
        ];
    }
}
