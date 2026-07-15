<?php

namespace App\Services\Erp\Drivers;

/**
 * Flow 1: Rocket LMS -> ERP -> Accounting
 * Apna khud ka product/booking bech rahe hain, uska data Perfex ERP mein
 * jaata hai (clients/items/invoices/appointments/payments). Uses the default
 * entity->path map from AbstractPerfexDriver — koi override nahi chahiye.
 */
class PerfexFlow1OwnProductsDriver extends AbstractPerfexDriver
{
    // Sab kuch AbstractPerfexDriver se inherit hota hai — is class ka wajood
    // sirf isliye taake config/driver_class mein har flow apni identity rakhe,
    // aur future mein flow-specific behavior (e.g. extra headers) yahan add ho sake.
}
