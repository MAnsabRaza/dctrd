<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dashboards 1 - 49
        \App\Models\Permission::updateOrCreate(['id' => 1], ['role_id' => 2, 'section_id' => 1, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2], ['role_id' => 2, 'section_id' => 2, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3], ['role_id' => 2, 'section_id' => 3, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 4], ['role_id' => 2, 'section_id' => 4, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 5], ['role_id' => 2, 'section_id' => 5, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 6], ['role_id' => 2, 'section_id' => 6, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 7], ['role_id' => 2, 'section_id' => 7, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 8], ['role_id' => 2, 'section_id' => 8, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 9], ['role_id' => 2, 'section_id' => 9, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 10], ['role_id' => 2, 'section_id' => 10, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 11], ['role_id' => 2, 'section_id' => 11, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 12], ['role_id' => 2, 'section_id' => 12, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 13], ['role_id' => 2, 'section_id' => 13, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 14], ['role_id' => 2, 'section_id' => 14, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 15], ['role_id' => 2, 'section_id' => 15, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 16], ['role_id' => 2, 'section_id' => 16, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 17], ['role_id' => 2, 'section_id' => 17, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 25], ['role_id' => 2, 'section_id' => 25, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 26], ['role_id' => 2, 'section_id' => 26, 'allow' => 1]);

        // Roles 50 - 99
        \App\Models\Permission::updateOrCreate(['id' => 50], ['role_id' => 2, 'section_id' => 50, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 51], ['role_id' => 2, 'section_id' => 51, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 52], ['role_id' => 2, 'section_id' => 52, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 53], ['role_id' => 2, 'section_id' => 53, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 54], ['role_id' => 2, 'section_id' => 54, 'allow' => 1]);

        // Users 100 - 149
        \App\Models\Permission::updateOrCreate(['id' => 100], ['role_id' => 2, 'section_id' => 100, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 101], ['role_id' => 2, 'section_id' => 101, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 102], ['role_id' => 2, 'section_id' => 102, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 103], ['role_id' => 2, 'section_id' => 103, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 104], ['role_id' => 2, 'section_id' => 104, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 105], ['role_id' => 2, 'section_id' => 105, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 106], ['role_id' => 2, 'section_id' => 106, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 107], ['role_id' => 2, 'section_id' => 107, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 108], ['role_id' => 2, 'section_id' => 108, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 109], ['role_id' => 2, 'section_id' => 109, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 110], ['role_id' => 2, 'section_id' => 110, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 111], ['role_id' => 2, 'section_id' => 111, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 112], ['role_id' => 2, 'section_id' => 112, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 113], ['role_id' => 2, 'section_id' => 113, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 114], ['role_id' => 2, 'section_id' => 114, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 115], ['role_id' => 2, 'section_id' => 115, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 116], ['role_id' => 2, 'section_id' => 116, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 117], ['role_id' => 2, 'section_id' => 117, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 118], ['role_id' => 2, 'section_id' => 118, 'allow' => 1]);

        // Webinar 150 - 199
        \App\Models\Permission::updateOrCreate(['id' => 150], ['role_id' => 2, 'section_id' => 150, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 151], ['role_id' => 2, 'section_id' => 151, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 152], ['role_id' => 2, 'section_id' => 152, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 153], ['role_id' => 2, 'section_id' => 153, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 154], ['role_id' => 2, 'section_id' => 154, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 155], ['role_id' => 2, 'section_id' => 155, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 156], ['role_id' => 2, 'section_id' => 156, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 157], ['role_id' => 2, 'section_id' => 157, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 158], ['role_id' => 2, 'section_id' => 158, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 159], ['role_id' => 2, 'section_id' => 159, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 160], ['role_id' => 2, 'section_id' => 160, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 161], ['role_id' => 2, 'section_id' => 161, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 162], ['role_id' => 2, 'section_id' => 162, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 163], ['role_id' => 2, 'section_id' => 163, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 164], ['role_id' => 2, 'section_id' => 164, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 165], ['role_id' => 2, 'section_id' => 165, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 166], ['role_id' => 2, 'section_id' => 166, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 167], ['role_id' => 2, 'section_id' => 167, 'allow' => 1]);

        // Categories 200 - 249
        \App\Models\Permission::updateOrCreate(['id' => 200], ['role_id' => 2, 'section_id' => 200, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 201], ['role_id' => 2, 'section_id' => 201, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 202], ['role_id' => 2, 'section_id' => 202, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203], ['role_id' => 2, 'section_id' => 203, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 204], ['role_id' => 2, 'section_id' => 204, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 205], ['role_id' => 2, 'section_id' => 205, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 206], ['role_id' => 2, 'section_id' => 206, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 207], ['role_id' => 2, 'section_id' => 207, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 208], ['role_id' => 2, 'section_id' => 208, 'allow' => 1]);

        // Tags 250 - 299
        \App\Models\Permission::updateOrCreate(['id' => 250], ['role_id' => 2, 'section_id' => 250, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 251], ['role_id' => 2, 'section_id' => 251, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 252], ['role_id' => 2, 'section_id' => 252, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 253], ['role_id' => 2, 'section_id' => 253, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 254], ['role_id' => 2, 'section_id' => 254, 'allow' => 1]);

        // Filters 300 - 349
        \App\Models\Permission::updateOrCreate(['id' => 300], ['role_id' => 2, 'section_id' => 300, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 301], ['role_id' => 2, 'section_id' => 301, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 302], ['role_id' => 2, 'section_id' => 302, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 303], ['role_id' => 2, 'section_id' => 303, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 304], ['role_id' => 2, 'section_id' => 304, 'allow' => 1]);

        // Quiz 350 - 399
        \App\Models\Permission::updateOrCreate(['id' => 350], ['role_id' => 2, 'section_id' => 350, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 351], ['role_id' => 2, 'section_id' => 351, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 352], ['role_id' => 2, 'section_id' => 352, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 353], ['role_id' => 2, 'section_id' => 353, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 354], ['role_id' => 2, 'section_id' => 354, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 355], ['role_id' => 2, 'section_id' => 355, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 356], ['role_id' => 2, 'section_id' => 356, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 357], ['role_id' => 2, 'section_id' => 357, 'allow' => 1]);

        // QuizResult 400 - 449
        \App\Models\Permission::updateOrCreate(['id' => 400], ['role_id' => 2, 'section_id' => 400, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 401], ['role_id' => 2, 'section_id' => 401, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 402], ['role_id' => 2, 'section_id' => 402, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 403], ['role_id' => 2, 'section_id' => 403, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 404], ['role_id' => 2, 'section_id' => 404, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 405], ['role_id' => 2, 'section_id' => 405, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 406], ['role_id' => 2, 'section_id' => 406, 'allow' => 1]);

        // Certificates 450 - 499
        \App\Models\Permission::updateOrCreate(['id' => 450], ['role_id' => 2, 'section_id' => 450, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 451], ['role_id' => 2, 'section_id' => 451, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 452], ['role_id' => 2, 'section_id' => 452, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 453], ['role_id' => 2, 'section_id' => 453, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 454], ['role_id' => 2, 'section_id' => 454, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 455], ['role_id' => 2, 'section_id' => 455, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 456], ['role_id' => 2, 'section_id' => 456, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 457], ['role_id' => 2, 'section_id' => 457, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 458], ['role_id' => 2, 'section_id' => 458, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 459], ['role_id' => 2, 'section_id' => 459, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 460], ['role_id' => 2, 'section_id' => 460, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 461], ['role_id' => 2, 'section_id' => 461, 'allow' => 1]);

        // Discount 500 - 549
        \App\Models\Permission::updateOrCreate(['id' => 500], ['role_id' => 2, 'section_id' => 500, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 501], ['role_id' => 2, 'section_id' => 501, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 502], ['role_id' => 2, 'section_id' => 502, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 503], ['role_id' => 2, 'section_id' => 503, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 504], ['role_id' => 2, 'section_id' => 504, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 505], ['role_id' => 2, 'section_id' => 505, 'allow' => 1]);

        // Group 550 - 599
        \App\Models\Permission::updateOrCreate(['id' => 550], ['role_id' => 2, 'section_id' => 550, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 551], ['role_id' => 2, 'section_id' => 551, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 552], ['role_id' => 2, 'section_id' => 552, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 553], ['role_id' => 2, 'section_id' => 553, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 554], ['role_id' => 2, 'section_id' => 554, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 555], ['role_id' => 2, 'section_id' => 555, 'allow' => 1]);

        // Payment Channels 600 - 649
        \App\Models\Permission::updateOrCreate(['id' => 600], ['role_id' => 2, 'section_id' => 600, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 601], ['role_id' => 2, 'section_id' => 601, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 602], ['role_id' => 2, 'section_id' => 602, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 603], ['role_id' => 2, 'section_id' => 603, 'allow' => 1]);

        // Setting 650 - 699
        \App\Models\Permission::updateOrCreate(['id' => 650], ['role_id' => 2, 'section_id' => 650, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 651], ['role_id' => 2, 'section_id' => 651, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 652], ['role_id' => 2, 'section_id' => 652, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 653], ['role_id' => 2, 'section_id' => 653, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 654], ['role_id' => 2, 'section_id' => 654, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 655], ['role_id' => 2, 'section_id' => 655, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 656], ['role_id' => 2, 'section_id' => 656, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 657], ['role_id' => 2, 'section_id' => 657, 'allow' => 1]);

        // Blog 700 - 749
        \App\Models\Permission::updateOrCreate(['id' => 700], ['role_id' => 2, 'section_id' => 700, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 701], ['role_id' => 2, 'section_id' => 701, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 702], ['role_id' => 2, 'section_id' => 702, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 703], ['role_id' => 2, 'section_id' => 703, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 704], ['role_id' => 2, 'section_id' => 704, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 705], ['role_id' => 2, 'section_id' => 705, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 706], ['role_id' => 2, 'section_id' => 706, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 707], ['role_id' => 2, 'section_id' => 707, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 708], ['role_id' => 2, 'section_id' => 708, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 709], ['role_id' => 2, 'section_id' => 709, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 710], ['role_id' => 2, 'section_id' => 710, 'allow' => 1]);

        // Sales 750 - 799
        \App\Models\Permission::updateOrCreate(['id' => 750], ['role_id' => 2, 'section_id' => 750, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 751], ['role_id' => 2, 'section_id' => 751, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 752], ['role_id' => 2, 'section_id' => 752, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 753], ['role_id' => 2, 'section_id' => 753, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 754], ['role_id' => 2, 'section_id' => 754, 'allow' => 1]);

        // Documents 800 - 849
        \App\Models\Permission::updateOrCreate(['id' => 800], ['role_id' => 2, 'section_id' => 800, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 801], ['role_id' => 2, 'section_id' => 801, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 802], ['role_id' => 2, 'section_id' => 802, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 803], ['role_id' => 2, 'section_id' => 803, 'allow' => 1]);

        // Payouts 850 - 899
        \App\Models\Permission::updateOrCreate(['id' => 850], ['role_id' => 2, 'section_id' => 850, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 851], ['role_id' => 2, 'section_id' => 851, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 852], ['role_id' => 2, 'section_id' => 852, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 853], ['role_id' => 2, 'section_id' => 853, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 854], ['role_id' => 2, 'section_id' => 854, 'allow' => 1]);

        // Offline Payment 900 - 949
        \App\Models\Permission::updateOrCreate(['id' => 900], ['role_id' => 2, 'section_id' => 900, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 901], ['role_id' => 2, 'section_id' => 901, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 902], ['role_id' => 2, 'section_id' => 902, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 903], ['role_id' => 2, 'section_id' => 903, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 904], ['role_id' => 2, 'section_id' => 904, 'allow' => 1]);

        // Supports 950 - 999
        \App\Models\Permission::updateOrCreate(['id' => 950], ['role_id' => 2, 'section_id' => 950, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 951], ['role_id' => 2, 'section_id' => 951, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 952], ['role_id' => 2, 'section_id' => 952, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 953], ['role_id' => 2, 'section_id' => 953, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 954], ['role_id' => 2, 'section_id' => 954, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 955], ['role_id' => 2, 'section_id' => 955, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 956], ['role_id' => 2, 'section_id' => 956, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 957], ['role_id' => 2, 'section_id' => 957, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 958], ['role_id' => 2, 'section_id' => 958, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 959], ['role_id' => 2, 'section_id' => 959, 'allow' => 1]);

        // Subscribes 1000 - 1049
        \App\Models\Permission::updateOrCreate(['id' => 1000], ['role_id' => 2, 'section_id' => 1000, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1001], ['role_id' => 2, 'section_id' => 1001, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1002], ['role_id' => 2, 'section_id' => 1002, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1003], ['role_id' => 2, 'section_id' => 1003, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1004], ['role_id' => 2, 'section_id' => 1004, 'allow' => 1]);

        // Notifications 1050 - 1074
        \App\Models\Permission::updateOrCreate(['id' => 1050], ['role_id' => 2, 'section_id' => 1050, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1051], ['role_id' => 2, 'section_id' => 1051, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1052], ['role_id' => 2, 'section_id' => 1052, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1053], ['role_id' => 2, 'section_id' => 1053, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1054], ['role_id' => 2, 'section_id' => 1054, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1055], ['role_id' => 2, 'section_id' => 1055, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1056], ['role_id' => 2, 'section_id' => 1056, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1057], ['role_id' => 2, 'section_id' => 1057, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1058], ['role_id' => 2, 'section_id' => 1058, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1059], ['role_id' => 2, 'section_id' => 1059, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1060], ['role_id' => 2, 'section_id' => 1060, 'allow' => 1]);

        // Noticeboards 1075 - 1099
        \App\Models\Permission::updateOrCreate(['id' => 1075], ['role_id' => 2, 'section_id' => 1075, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1076], ['role_id' => 2, 'section_id' => 1076, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1077], ['role_id' => 2, 'section_id' => 1077, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1078], ['role_id' => 2, 'section_id' => 1078, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1079], ['role_id' => 2, 'section_id' => 1079, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1080], ['role_id' => 2, 'section_id' => 1080, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1081], ['role_id' => 2, 'section_id' => 1081, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1082], ['role_id' => 2, 'section_id' => 1082, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1083], ['role_id' => 2, 'section_id' => 1083, 'allow' => 1]);

        // Promotions 1100 - 1149
        \App\Models\Permission::updateOrCreate(['id' => 1100], ['role_id' => 2, 'section_id' => 1100, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1101], ['role_id' => 2, 'section_id' => 1101, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1102], ['role_id' => 2, 'section_id' => 1102, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1103], ['role_id' => 2, 'section_id' => 1103, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1104], ['role_id' => 2, 'section_id' => 1104, 'allow' => 1]);

        // Testimonials 1150 - 1199
        \App\Models\Permission::updateOrCreate(['id' => 1150], ['role_id' => 2, 'section_id' => 1150, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1151], ['role_id' => 2, 'section_id' => 1151, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1152], ['role_id' => 2, 'section_id' => 1152, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1153], ['role_id' => 2, 'section_id' => 1153, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1154], ['role_id' => 2, 'section_id' => 1154, 'allow' => 1]);

        // Admin Advertising 1200 - 1229
        \App\Models\Permission::updateOrCreate(['id' => 1200], ['role_id' => 2, 'section_id' => 1200, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1201], ['role_id' => 2, 'section_id' => 1201, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1202], ['role_id' => 2, 'section_id' => 1202, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1203], ['role_id' => 2, 'section_id' => 1203, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1204], ['role_id' => 2, 'section_id' => 1204, 'allow' => 1]);

        // Admin Newsletters 1230 - 1249
        \App\Models\Permission::updateOrCreate(['id' => 1230], ['role_id' => 2, 'section_id' => 1230, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1231], ['role_id' => 2, 'section_id' => 1231, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1232], ['role_id' => 2, 'section_id' => 1232, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1233], ['role_id' => 2, 'section_id' => 1233, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1234], ['role_id' => 2, 'section_id' => 1234, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1235], ['role_id' => 2, 'section_id' => 1235, 'allow' => 1]);

        // Contact 1250 - 1299
        \App\Models\Permission::updateOrCreate(['id' => 1250], ['role_id' => 2, 'section_id' => 1250, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1251], ['role_id' => 2, 'section_id' => 1251, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1252], ['role_id' => 2, 'section_id' => 1252, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1253], ['role_id' => 2, 'section_id' => 1253, 'allow' => 1]);

        // Special Offers 1300 - 1349
        \App\Models\Permission::updateOrCreate(['id' => 1300], ['role_id' => 2, 'section_id' => 1300, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1301], ['role_id' => 2, 'section_id' => 1301, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1302], ['role_id' => 2, 'section_id' => 1302, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1303], ['role_id' => 2, 'section_id' => 1303, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1304], ['role_id' => 2, 'section_id' => 1304, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1305], ['role_id' => 2, 'section_id' => 1305, 'allow' => 1]);

        // Pages 1350 - 1399
        \App\Models\Permission::updateOrCreate(['id' => 1350], ['role_id' => 2, 'section_id' => 1350, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1351], ['role_id' => 2, 'section_id' => 1351, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1352], ['role_id' => 2, 'section_id' => 1352, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1353], ['role_id' => 2, 'section_id' => 1353, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1354], ['role_id' => 2, 'section_id' => 1354, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1355], ['role_id' => 2, 'section_id' => 1355, 'allow' => 1]);

        // Comments 1400 - 1449
        \App\Models\Permission::updateOrCreate(['id' => 1400], ['role_id' => 2, 'section_id' => 1400, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1401], ['role_id' => 2, 'section_id' => 1401, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1402], ['role_id' => 2, 'section_id' => 1402, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1403], ['role_id' => 2, 'section_id' => 1403, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1404], ['role_id' => 2, 'section_id' => 1404, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1405], ['role_id' => 2, 'section_id' => 1405, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1406], ['role_id' => 2, 'section_id' => 1406, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1407], ['role_id' => 2, 'section_id' => 1407, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1408], ['role_id' => 2, 'section_id' => 1408, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1409], ['role_id' => 2, 'section_id' => 1409, 'allow' => 1]);

        // Reports 1450 - 1499
        \App\Models\Permission::updateOrCreate(['id' => 1450], ['role_id' => 2, 'section_id' => 1450, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1451], ['role_id' => 2, 'section_id' => 1451, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1452], ['role_id' => 2, 'section_id' => 1452, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1453], ['role_id' => 2, 'section_id' => 1453, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1454], ['role_id' => 2, 'section_id' => 1454, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1455], ['role_id' => 2, 'section_id' => 1455, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1456], ['role_id' => 2, 'section_id' => 1456, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1457], ['role_id' => 2, 'section_id' => 1457, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1458], ['role_id' => 2, 'section_id' => 1458, 'allow' => 1]);

        // Additional Pages 1500 - 1549
        \App\Models\Permission::updateOrCreate(['id' => 1500], ['role_id' => 2, 'section_id' => 1500, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1501], ['role_id' => 2, 'section_id' => 1501, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1502], ['role_id' => 2, 'section_id' => 1502, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1503], ['role_id' => 2, 'section_id' => 1503, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1504], ['role_id' => 2, 'section_id' => 1504, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1550], ['role_id' => 2, 'section_id' => 1550, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1551], ['role_id' => 2, 'section_id' => 1551, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1552], ['role_id' => 2, 'section_id' => 1552, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1553], ['role_id' => 2, 'section_id' => 1553, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1554], ['role_id' => 2, 'section_id' => 1554, 'allow' => 1]);

        // Reviews 1600 - 1649
        \App\Models\Permission::updateOrCreate(['id' => 1600], ['role_id' => 2, 'section_id' => 1600, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1601], ['role_id' => 2, 'section_id' => 1601, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1602], ['role_id' => 2, 'section_id' => 1602, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1603], ['role_id' => 2, 'section_id' => 1603, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1604], ['role_id' => 2, 'section_id' => 1604, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1605], ['role_id' => 2, 'section_id' => 1605, 'allow' => 1]);

        // Consultants 1650 - 1674
        \App\Models\Permission::updateOrCreate(['id' => 1650], ['role_id' => 2, 'section_id' => 1650, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1651], ['role_id' => 2, 'section_id' => 1651, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1652], ['role_id' => 2, 'section_id' => 1652, 'allow' => 1]);

        // Referrals 1675 - 1699
        \App\Models\Permission::updateOrCreate(['id' => 1675], ['role_id' => 2, 'section_id' => 1675, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1676], ['role_id' => 2, 'section_id' => 1676, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1677], ['role_id' => 2, 'section_id' => 1677, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1678], ['role_id' => 2, 'section_id' => 1678, 'allow' => 1]);

        // Agora History 1700 - 1724
        \App\Models\Permission::updateOrCreate(['id' => 1701], ['role_id' => 2, 'section_id' => 1701, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1702], ['role_id' => 2, 'section_id' => 1702, 'allow' => 1]);

        // Regions 1725 - 1749
        \App\Models\Permission::updateOrCreate(['id' => 1725], ['role_id' => 2, 'section_id' => 1725, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1726], ['role_id' => 2, 'section_id' => 1726, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1727], ['role_id' => 2, 'section_id' => 1727, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1728], ['role_id' => 2, 'section_id' => 1728, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1729], ['role_id' => 2, 'section_id' => 1729, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1730], ['role_id' => 2, 'section_id' => 1730, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1731], ['role_id' => 2, 'section_id' => 1731, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1732], ['role_id' => 2, 'section_id' => 1732, 'allow' => 1]);

        // Rewards 1750 - 1774
        \App\Models\Permission::updateOrCreate(['id' => 1750], ['role_id' => 2, 'section_id' => 1750, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1751], ['role_id' => 2, 'section_id' => 1751, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1752], ['role_id' => 2, 'section_id' => 1752, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1753], ['role_id' => 2, 'section_id' => 1753, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1754], ['role_id' => 2, 'section_id' => 1754, 'allow' => 1]);

        // Registration Packages 1775 - 1799
        \App\Models\Permission::updateOrCreate(['id' => 1775], ['role_id' => 2, 'section_id' => 1775, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1776], ['role_id' => 2, 'section_id' => 1776, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1777], ['role_id' => 2, 'section_id' => 1777, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1778], ['role_id' => 2, 'section_id' => 1778, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1779], ['role_id' => 2, 'section_id' => 1779, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1780], ['role_id' => 2, 'section_id' => 1780, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1781], ['role_id' => 2, 'section_id' => 1781, 'allow' => 1]);

        // 1800 - 1837
        \App\Models\Permission::updateOrCreate(['id' => 1800], ['role_id' => 2, 'section_id' => 1800, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1801], ['role_id' => 2, 'section_id' => 1801, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1802], ['role_id' => 2, 'section_id' => 1802, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1803], ['role_id' => 2, 'section_id' => 1803, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1804], ['role_id' => 2, 'section_id' => 1804, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1805], ['role_id' => 2, 'section_id' => 1805, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1806], ['role_id' => 2, 'section_id' => 1806, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1807], ['role_id' => 2, 'section_id' => 1807, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1808], ['role_id' => 2, 'section_id' => 1808, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1809], ['role_id' => 2, 'section_id' => 1809, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1810], ['role_id' => 2, 'section_id' => 1810, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1811], ['role_id' => 2, 'section_id' => 1811, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1812], ['role_id' => 2, 'section_id' => 1812, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1813], ['role_id' => 2, 'section_id' => 1813, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1814], ['role_id' => 2, 'section_id' => 1814, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1815], ['role_id' => 2, 'section_id' => 1815, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1816], ['role_id' => 2, 'section_id' => 1816, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1817], ['role_id' => 2, 'section_id' => 1817, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1818], ['role_id' => 2, 'section_id' => 1818, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1819], ['role_id' => 2, 'section_id' => 1819, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1820], ['role_id' => 2, 'section_id' => 1820, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1821], ['role_id' => 2, 'section_id' => 1821, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1822], ['role_id' => 2, 'section_id' => 1822, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1823], ['role_id' => 2, 'section_id' => 1823, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1824], ['role_id' => 2, 'section_id' => 1824, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1825], ['role_id' => 2, 'section_id' => 1825, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1826], ['role_id' => 2, 'section_id' => 1826, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1827], ['role_id' => 2, 'section_id' => 1827, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1828], ['role_id' => 2, 'section_id' => 1828, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1829], ['role_id' => 2, 'section_id' => 1829, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1830], ['role_id' => 2, 'section_id' => 1830, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1831], ['role_id' => 2, 'section_id' => 1831, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1832], ['role_id' => 2, 'section_id' => 1832, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1833], ['role_id' => 2, 'section_id' => 1833, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1834], ['role_id' => 2, 'section_id' => 1834, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1835], ['role_id' => 2, 'section_id' => 1835, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1836], ['role_id' => 2, 'section_id' => 1836, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1837], ['role_id' => 2, 'section_id' => 1837, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1850], ['role_id' => 2, 'section_id' => 1850, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1851], ['role_id' => 2, 'section_id' => 1851, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1852], ['role_id' => 2, 'section_id' => 1852, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1853], ['role_id' => 2, 'section_id' => 1853, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1875], ['role_id' => 2, 'section_id' => 1875, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1876], ['role_id' => 2, 'section_id' => 1876, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1877], ['role_id' => 2, 'section_id' => 1877, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1900], ['role_id' => 2, 'section_id' => 1900, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1901], ['role_id' => 2, 'section_id' => 1901, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1902], ['role_id' => 2, 'section_id' => 1902, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1903], ['role_id' => 2, 'section_id' => 1903, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1904], ['role_id' => 2, 'section_id' => 1904, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1905], ['role_id' => 2, 'section_id' => 1905, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1925], ['role_id' => 2, 'section_id' => 1925, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1926], ['role_id' => 2, 'section_id' => 1926, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1927], ['role_id' => 2, 'section_id' => 1927, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1928], ['role_id' => 2, 'section_id' => 1928, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1929], ['role_id' => 2, 'section_id' => 1929, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1930], ['role_id' => 2, 'section_id' => 1930, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1931], ['role_id' => 2, 'section_id' => 1931, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1932], ['role_id' => 2, 'section_id' => 1932, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1933], ['role_id' => 2, 'section_id' => 1933, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1934], ['role_id' => 2, 'section_id' => 1934, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1935], ['role_id' => 2, 'section_id' => 1935, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1950], ['role_id' => 2, 'section_id' => 1950, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1951], ['role_id' => 2, 'section_id' => 1951, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1952], ['role_id' => 2, 'section_id' => 1952, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1953], ['role_id' => 2, 'section_id' => 1953, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1954], ['role_id' => 2, 'section_id' => 1954, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1975], ['role_id' => 2, 'section_id' => 1975, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1976], ['role_id' => 2, 'section_id' => 1976, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1977], ['role_id' => 2, 'section_id' => 1977, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1978], ['role_id' => 2, 'section_id' => 1978, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 1979], ['role_id' => 2, 'section_id' => 1979, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2000], ['role_id' => 2, 'section_id' => 2000, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2001], ['role_id' => 2, 'section_id' => 2001, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2015], ['role_id' => 2, 'section_id' => 2015, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2016], ['role_id' => 2, 'section_id' => 2016, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2017], ['role_id' => 2, 'section_id' => 2017, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2018], ['role_id' => 2, 'section_id' => 2018, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2019], ['role_id' => 2, 'section_id' => 2019, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2020], ['role_id' => 2, 'section_id' => 2020, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2030], ['role_id' => 2, 'section_id' => 2030, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2031], ['role_id' => 2, 'section_id' => 2031, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2032], ['role_id' => 2, 'section_id' => 2032, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2050], ['role_id' => 2, 'section_id' => 2050, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2051], ['role_id' => 2, 'section_id' => 2051, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2052], ['role_id' => 2, 'section_id' => 2052, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2053], ['role_id' => 2, 'section_id' => 2053, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2054], ['role_id' => 2, 'section_id' => 2054, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2055], ['role_id' => 2, 'section_id' => 2055, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2070], ['role_id' => 2, 'section_id' => 2070, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2071], ['role_id' => 2, 'section_id' => 2071, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2072], ['role_id' => 2, 'section_id' => 2072, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2073], ['role_id' => 2, 'section_id' => 2073, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2074], ['role_id' => 2, 'section_id' => 2074, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2075], ['role_id' => 2, 'section_id' => 2075, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2076], ['role_id' => 2, 'section_id' => 2076, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2077], ['role_id' => 2, 'section_id' => 2077, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2078], ['role_id' => 2, 'section_id' => 2078, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2079], ['role_id' => 2, 'section_id' => 2079, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2080], ['role_id' => 2, 'section_id' => 2080, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2081], ['role_id' => 2, 'section_id' => 2081, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2090], ['role_id' => 2, 'section_id' => 2090, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2091], ['role_id' => 2, 'section_id' => 2091, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2092], ['role_id' => 2, 'section_id' => 2092, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 2093], ['role_id' => 2, 'section_id' => 2093, 'allow' => 1]);

        // Booking 3000 - 3099
        \App\Models\Permission::updateOrCreate(['id' => 3000], ['role_id' => 2, 'section_id' => 3000, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3001], ['role_id' => 2, 'section_id' => 3001, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3010], ['role_id' => 2, 'section_id' => 3010, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3011], ['role_id' => 2, 'section_id' => 3011, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3012], ['role_id' => 2, 'section_id' => 3012, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3013], ['role_id' => 2, 'section_id' => 3013, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3020], ['role_id' => 2, 'section_id' => 3020, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3021], ['role_id' => 2, 'section_id' => 3021, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3022], ['role_id' => 2, 'section_id' => 3022, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3023], ['role_id' => 2, 'section_id' => 3023, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3024], ['role_id' => 2, 'section_id' => 3024, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3025], ['role_id' => 2, 'section_id' => 3025, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3030], ['role_id' => 2, 'section_id' => 3030, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3031], ['role_id' => 2, 'section_id' => 3031, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3032], ['role_id' => 2, 'section_id' => 3032, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3033], ['role_id' => 2, 'section_id' => 3033, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3034], ['role_id' => 2, 'section_id' => 3034, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3035], ['role_id' => 2, 'section_id' => 3035, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3040], ['role_id' => 2, 'section_id' => 3040, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3041], ['role_id' => 2, 'section_id' => 3041, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3042], ['role_id' => 2, 'section_id' => 3042, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3043], ['role_id' => 2, 'section_id' => 3043, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3044], ['role_id' => 2, 'section_id' => 3044, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3045], ['role_id' => 2, 'section_id' => 3045, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3046], ['role_id' => 2, 'section_id' => 3046, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3050], ['role_id' => 2, 'section_id' => 3050, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3051], ['role_id' => 2, 'section_id' => 3051, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3052], ['role_id' => 2, 'section_id' => 3052, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3053], ['role_id' => 2, 'section_id' => 3053, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3054], ['role_id' => 2, 'section_id' => 3054, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3055], ['role_id' => 2, 'section_id' => 3055, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3056], ['role_id' => 2, 'section_id' => 3056, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3060], ['role_id' => 2, 'section_id' => 3060, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3061], ['role_id' => 2, 'section_id' => 3061, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3062], ['role_id' => 2, 'section_id' => 3062, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3063], ['role_id' => 2, 'section_id' => 3063, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3064], ['role_id' => 2, 'section_id' => 3064, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3070], ['role_id' => 2, 'section_id' => 3070, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3071], ['role_id' => 2, 'section_id' => 3071, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3072], ['role_id' => 2, 'section_id' => 3072, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3080], ['role_id' => 2, 'section_id' => 3080, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3081], ['role_id' => 2, 'section_id' => 3081, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3082], ['role_id' => 2, 'section_id' => 3082, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3083], ['role_id' => 2, 'section_id' => 3083, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3084], ['role_id' => 2, 'section_id' => 3084, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3090], ['role_id' => 2, 'section_id' => 3090, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3091], ['role_id' => 2, 'section_id' => 3091, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3092], ['role_id' => 2, 'section_id' => 3092, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3093], ['role_id' => 2, 'section_id' => 3093, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3100], ['role_id' => 2, 'section_id' => 3100, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3101], ['role_id' => 2, 'section_id' => 3101, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3102], ['role_id' => 2, 'section_id' => 3102, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3103], ['role_id' => 2, 'section_id' => 3103, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3104], ['role_id' => 2, 'section_id' => 3104, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3110], ['role_id' => 2, 'section_id' => 3110, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3111], ['role_id' => 2, 'section_id' => 3111, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3120], ['role_id' => 2, 'section_id' => 3120, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3121], ['role_id' => 2, 'section_id' => 3121, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3122], ['role_id' => 2, 'section_id' => 3122, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3123], ['role_id' => 2, 'section_id' => 3123, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3130], ['role_id' => 2, 'section_id' => 3130, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3131], ['role_id' => 2, 'section_id' => 3131, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3140], ['role_id' => 2, 'section_id' => 3140, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3141], ['role_id' => 2, 'section_id' => 3141, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3150], ['role_id' => 2, 'section_id' => 3150, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3151], ['role_id' => 2, 'section_id' => 3151, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3152], ['role_id' => 2, 'section_id' => 3152, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3153], ['role_id' => 2, 'section_id' => 3153, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3154], ['role_id' => 2, 'section_id' => 3154, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3155], ['role_id' => 2, 'section_id' => 3155, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3156], ['role_id' => 2, 'section_id' => 3156, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3170], ['role_id' => 2, 'section_id' => 3170, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3171], ['role_id' => 2, 'section_id' => 3171, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3172], ['role_id' => 2, 'section_id' => 3172, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3173], ['role_id' => 2, 'section_id' => 3173, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3174], ['role_id' => 2, 'section_id' => 3174, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3175], ['role_id' => 2, 'section_id' => 3175, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3176], ['role_id' => 2, 'section_id' => 3176, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3190], ['role_id' => 2, 'section_id' => 3190, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3191], ['role_id' => 2, 'section_id' => 3191, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3192], ['role_id' => 2, 'section_id' => 3192, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3193], ['role_id' => 2, 'section_id' => 3193, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3200], ['role_id' => 2, 'section_id' => 3200, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3201], ['role_id' => 2, 'section_id' => 3201, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3202], ['role_id' => 2, 'section_id' => 3202, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3203], ['role_id' => 2, 'section_id' => 3203, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3204], ['role_id' => 2, 'section_id' => 3204, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3205], ['role_id' => 2, 'section_id' => 3205, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3210], ['role_id' => 2, 'section_id' => 3210, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3211], ['role_id' => 2, 'section_id' => 3211, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3212], ['role_id' => 2, 'section_id' => 3212, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3213], ['role_id' => 2, 'section_id' => 3213, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3214], ['role_id' => 2, 'section_id' => 3214, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3215], ['role_id' => 2, 'section_id' => 3215, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3216], ['role_id' => 2, 'section_id' => 3216, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3217], ['role_id' => 2, 'section_id' => 3217, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3218], ['role_id' => 2, 'section_id' => 3218, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3219], ['role_id' => 2, 'section_id' => 3219, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3220], ['role_id' => 2, 'section_id' => 3220, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3230], ['role_id' => 2, 'section_id' => 3230, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3231], ['role_id' => 2, 'section_id' => 3231, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3232], ['role_id' => 2, 'section_id' => 3232, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3233], ['role_id' => 2, 'section_id' => 3233, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3234], ['role_id' => 2, 'section_id' => 3234, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3235], ['role_id' => 2, 'section_id' => 3235, 'allow' => 1]);

        // Booking Category 3240 - 3249
        \App\Models\Permission::updateOrCreate(['id' => 3240], ['role_id' => 2, 'section_id' => 3240, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3241], ['role_id' => 2, 'section_id' => 3241, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3242], ['role_id' => 2, 'section_id' => 3242, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3243], ['role_id' => 2, 'section_id' => 3243, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3244], ['role_id' => 2, 'section_id' => 3244, 'allow' => 1]);

        // Booking
        \App\Models\Permission::updateOrCreate(['id' => 3245], ['role_id' => 2, 'section_id' => 3245, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3246], ['role_id' => 2, 'section_id' => 3246, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3247], ['role_id' => 2, 'section_id' => 3247, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3248], ['role_id' => 2, 'section_id' => 3248, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3249], ['role_id' => 2, 'section_id' => 3249, 'allow' => 1]);

        // Booking Resource 3250 - 3258
        \App\Models\Permission::updateOrCreate(['id' => 3250], ['role_id' => 2, 'section_id' => 3250, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3251], ['role_id' => 2, 'section_id' => 3251, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3252], ['role_id' => 2, 'section_id' => 3252, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3253], ['role_id' => 2, 'section_id' => 3253, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3254], ['role_id' => 2, 'section_id' => 3254, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3255], ['role_id' => 2, 'section_id' => 3255, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3256], ['role_id' => 2, 'section_id' => 3256, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3257], ['role_id' => 2, 'section_id' => 3257, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3258], ['role_id' => 2, 'section_id' => 3258, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3260], ['role_id' => 2, 'section_id' => 3260, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3261], ['role_id' => 2, 'section_id' => 3261, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3262], ['role_id' => 2, 'section_id' => 3262, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3263], ['role_id' => 2, 'section_id' => 3263, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3264], ['role_id' => 2, 'section_id' => 3264, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3265], ['role_id' => 2, 'section_id' => 3265, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3266], ['role_id' => 2, 'section_id' => 3266, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3267], ['role_id' => 2, 'section_id' => 3267, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3268], ['role_id' => 2, 'section_id' => 3268, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3269], ['role_id' => 2, 'section_id' => 3269, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3270], ['role_id' => 2, 'section_id' => 3270, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3271], ['role_id' => 2, 'section_id' => 3271, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3272], ['role_id' => 2, 'section_id' => 3272, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3273], ['role_id' => 2, 'section_id' => 3273, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3274], ['role_id' => 2, 'section_id' => 3274, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3275], ['role_id' => 2, 'section_id' => 3275, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3276], ['role_id' => 2, 'section_id' => 3276, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3277], ['role_id' => 2, 'section_id' => 3277, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3278], ['role_id' => 2, 'section_id' => 3278, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3279], ['role_id' => 2, 'section_id' => 3279, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3280], ['role_id' => 2, 'section_id' => 3280, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3281], ['role_id' => 2, 'section_id' => 3281, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3282], ['role_id' => 2, 'section_id' => 3282, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3283], ['role_id' => 2, 'section_id' => 3283, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3284], ['role_id' => 2, 'section_id' => 3284, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3285], ['role_id' => 2, 'section_id' => 3285, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3286], ['role_id' => 2, 'section_id' => 3286, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3287], ['role_id' => 2, 'section_id' => 3287, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3288], ['role_id' => 2, 'section_id' => 3288, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3289], ['role_id' => 2, 'section_id' => 3289, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3290], ['role_id' => 2, 'section_id' => 3290, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3291], ['role_id' => 2, 'section_id' => 3291, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3292], ['role_id' => 2, 'section_id' => 3292, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3293], ['role_id' => 2, 'section_id' => 3293, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3294], ['role_id' => 2, 'section_id' => 3294, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3295], ['role_id' => 2, 'section_id' => 3295, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3296], ['role_id' => 2, 'section_id' => 3296, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3297], ['role_id' => 2, 'section_id' => 3297, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3298], ['role_id' => 2, 'section_id' => 3298, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3299], ['role_id' => 2, 'section_id' => 3299, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3311], ['role_id' => 2, 'section_id' => 3311, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3312], ['role_id' => 2, 'section_id' => 3312, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3313], ['role_id' => 2, 'section_id' => 3313, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3320], ['role_id' => 2, 'section_id' => 3320, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3321], ['role_id' => 2, 'section_id' => 3321, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3322], ['role_id' => 2, 'section_id' => 3322, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3323], ['role_id' => 2, 'section_id' => 3323, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3330], ['role_id' => 2, 'section_id' => 3330, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3331], ['role_id' => 2, 'section_id' => 3331, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3332], ['role_id' => 2, 'section_id' => 3332, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3333], ['role_id' => 2, 'section_id' => 3333, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3340], ['role_id' => 2, 'section_id' => 3340, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3341], ['role_id' => 2, 'section_id' => 3341, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3342], ['role_id' => 2, 'section_id' => 3342, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3343], ['role_id' => 2, 'section_id' => 3343, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3350], ['role_id' => 2, 'section_id' => 3350, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3351], ['role_id' => 2, 'section_id' => 3351, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3352], ['role_id' => 2, 'section_id' => 3352, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3353], ['role_id' => 2, 'section_id' => 3353, 'allow' => 1]);

        // 3360 - 3399
        \App\Models\Permission::updateOrCreate(['id' => 3360], ['role_id' => 2, 'section_id' => 3360, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3361], ['role_id' => 2, 'section_id' => 3361, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3362], ['role_id' => 2, 'section_id' => 3362, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3363], ['role_id' => 2, 'section_id' => 3363, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3364], ['role_id' => 2, 'section_id' => 3364, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3365], ['role_id' => 2, 'section_id' => 3365, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3366], ['role_id' => 2, 'section_id' => 3366, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3367], ['role_id' => 2, 'section_id' => 3367, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3368], ['role_id' => 2, 'section_id' => 3368, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3369], ['role_id' => 2, 'section_id' => 3369, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3370], ['role_id' => 2, 'section_id' => 3370, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3371], ['role_id' => 2, 'section_id' => 3371, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3372], ['role_id' => 2, 'section_id' => 3372, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3373], ['role_id' => 2, 'section_id' => 3373, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3374], ['role_id' => 2, 'section_id' => 3374, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3375], ['role_id' => 2, 'section_id' => 3375, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3376], ['role_id' => 2, 'section_id' => 3376, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3377], ['role_id' => 2, 'section_id' => 3377, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3378], ['role_id' => 2, 'section_id' => 3378, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3379], ['role_id' => 2, 'section_id' => 3379, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3380], ['role_id' => 2, 'section_id' => 3380, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3381], ['role_id' => 2, 'section_id' => 3381, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3382], ['role_id' => 2, 'section_id' => 3382, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3383], ['role_id' => 2, 'section_id' => 3383, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3384], ['role_id' => 2, 'section_id' => 3384, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3385], ['role_id' => 2, 'section_id' => 3385, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3386], ['role_id' => 2, 'section_id' => 3386, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3387], ['role_id' => 2, 'section_id' => 3387, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3388], ['role_id' => 2, 'section_id' => 3388, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3389], ['role_id' => 2, 'section_id' => 3389, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3390], ['role_id' => 2, 'section_id' => 3390, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3391], ['role_id' => 2, 'section_id' => 3391, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3392], ['role_id' => 2, 'section_id' => 3392, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3393], ['role_id' => 2, 'section_id' => 3393, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3394], ['role_id' => 2, 'section_id' => 3394, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3395], ['role_id' => 2, 'section_id' => 3395, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3396], ['role_id' => 2, 'section_id' => 3396, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3397], ['role_id' => 2, 'section_id' => 3397, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3398], ['role_id' => 2, 'section_id' => 3398, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3399], ['role_id' => 2, 'section_id' => 3399, 'allow' => 1]);

        // Booking Rate Plan / Misc 3400 - 3405
        \App\Models\Permission::updateOrCreate(['id' => 3400], ['role_id' => 2, 'section_id' => 3400, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3401], ['role_id' => 2, 'section_id' => 3401, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3402], ['role_id' => 2, 'section_id' => 3402, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3403], ['role_id' => 2, 'section_id' => 3403, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3404], ['role_id' => 2, 'section_id' => 3404, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3405], ['role_id' => 2, 'section_id' => 3405, 'allow' => 1]);

        // Booking Featured 3500 - 3503
        \App\Models\Permission::updateOrCreate(['id' => 3500], ['role_id' => 2, 'section_id' => 3500, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3501], ['role_id' => 2, 'section_id' => 3501, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3502], ['role_id' => 2, 'section_id' => 3502, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3503], ['role_id' => 2, 'section_id' => 3503, 'allow' => 1]);

        // Booking Top Categories 3510 - 3513
        \App\Models\Permission::updateOrCreate(['id' => 3510], ['role_id' => 2, 'section_id' => 3510, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3511], ['role_id' => 2, 'section_id' => 3511, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3512], ['role_id' => 2, 'section_id' => 3512, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3513], ['role_id' => 2, 'section_id' => 3513, 'allow' => 1]);

        // Booking Feature Categories 3520 - 3523
        \App\Models\Permission::updateOrCreate(['id' => 3520], ['role_id' => 2, 'section_id' => 3520, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3521], ['role_id' => 2, 'section_id' => 3521, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3522], ['role_id' => 2, 'section_id' => 3522, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3523], ['role_id' => 2, 'section_id' => 3523, 'allow' => 1]);

        // Booking Content Settings 3530 - 3531
        \App\Models\Permission::updateOrCreate(['id' => 3530], ['role_id' => 2, 'section_id' => 3530, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3531], ['role_id' => 2, 'section_id' => 3531, 'allow' => 1]);

        // Permission for in-house bookings (custom) 3600
        \App\Models\Permission::updateOrCreate(['id' => 3600], ['role_id' => 2, 'section_id' => 3600, 'allow' => 1]);

        // Booking Order 3601 - 3605
        \App\Models\Permission::updateOrCreate(['id' => 3601], ['role_id' => 2, 'section_id' => 3601, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3602], ['role_id' => 2, 'section_id' => 3602, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3603], ['role_id' => 2, 'section_id' => 3603, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3604], ['role_id' => 2, 'section_id' => 3604, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3605], ['role_id' => 2, 'section_id' => 3605, 'allow' => 1]);

        // Booking Sellers 3610 - 3612
        \App\Models\Permission::updateOrCreate(['id' => 3610], ['role_id' => 2, 'section_id' => 3610, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3611], ['role_id' => 2, 'section_id' => 3611, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3612], ['role_id' => 2, 'section_id' => 3612, 'allow' => 1]);

        // Booking Review 3700 - 3704
        \App\Models\Permission::updateOrCreate(['id' => 3700], ['role_id' => 2, 'section_id' => 3700, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3701], ['role_id' => 2, 'section_id' => 3701, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3702], ['role_id' => 2, 'section_id' => 3702, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3703], ['role_id' => 2, 'section_id' => 3703, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 3704], ['role_id' => 2, 'section_id' => 3704, 'allow' => 1]);

        // Panel Booking Orders (all roles)
        \App\Models\Permission::updateOrCreate(['id' => 100361], ['role_id' => 1, 'section_id' => 100361, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 200361], ['role_id' => 2, 'section_id' => 100361, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 300361], ['role_id' => 3, 'section_id' => 100361, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 400361], ['role_id' => 4, 'section_id' => 100361, 'allow' => 1]);

        // Panel Booking Favorites (all roles)
        \App\Models\Permission::updateOrCreate(['id' => 100362], ['role_id' => 1, 'section_id' => 100362, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 200362], ['role_id' => 2, 'section_id' => 100362, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 300362], ['role_id' => 3, 'section_id' => 100362, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 400362], ['role_id' => 4, 'section_id' => 100362, 'allow' => 1]);

        // Panel Booking Reviews (all roles)
        \App\Models\Permission::updateOrCreate(['id' => 100363], ['role_id' => 1, 'section_id' => 100363, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 200363], ['role_id' => 2, 'section_id' => 100363, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 300363], ['role_id' => 3, 'section_id' => 100363, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 400363], ['role_id' => 4, 'section_id' => 100363, 'allow' => 1]);

        // Panel Booking Comments (all roles)
        \App\Models\Permission::updateOrCreate(['id' => 100364], ['role_id' => 1, 'section_id' => 100364, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 200364], ['role_id' => 2, 'section_id' => 100364, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 300364], ['role_id' => 3, 'section_id' => 100364, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 400364], ['role_id' => 4, 'section_id' => 100364, 'allow' => 1]);

        // Panel Checkout Options (all roles)
        \App\Models\Permission::updateOrCreate(['id' => 100304], ['role_id' => 1, 'section_id' => 100304, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 200304], ['role_id' => 2, 'section_id' => 100304, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 300304], ['role_id' => 3, 'section_id' => 100304, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 400304], ['role_id' => 4, 'section_id' => 100304, 'allow' => 1]);

        // Admin Checkout Modules (500-504) - duplicate IDs kept for backward compat
        \App\Models\Permission::updateOrCreate(['id' => 200500], ['role_id' => 2, 'section_id' => 500, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 200501], ['role_id' => 2, 'section_id' => 501, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 200502], ['role_id' => 2, 'section_id' => 502, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 200503], ['role_id' => 2, 'section_id' => 503, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 200504], ['role_id' => 2, 'section_id' => 504, 'allow' => 1]);

        // Booking Featured permissions (large IDs)
        \App\Models\Permission::updateOrCreate(['id' => 203500], ['role_id' => 2, 'section_id' => 3500, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203501], ['role_id' => 2, 'section_id' => 3501, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203502], ['role_id' => 2, 'section_id' => 3502, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203503], ['role_id' => 2, 'section_id' => 3503, 'allow' => 1]);

        // Booking Top Categories (both roles)
        \App\Models\Permission::updateOrCreate(['id' => 103510], ['role_id' => 1, 'section_id' => 3510, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203510], ['role_id' => 2, 'section_id' => 3510, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 103511], ['role_id' => 1, 'section_id' => 3511, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203511], ['role_id' => 2, 'section_id' => 3511, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 103512], ['role_id' => 1, 'section_id' => 3512, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203512], ['role_id' => 2, 'section_id' => 3512, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 103513], ['role_id' => 1, 'section_id' => 3513, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203513], ['role_id' => 2, 'section_id' => 3513, 'allow' => 1]);

        // Booking Feature Categories (both roles)
        \App\Models\Permission::updateOrCreate(['id' => 103520], ['role_id' => 1, 'section_id' => 3520, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203520], ['role_id' => 2, 'section_id' => 3520, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 103521], ['role_id' => 1, 'section_id' => 3521, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203521], ['role_id' => 2, 'section_id' => 3521, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 103522], ['role_id' => 1, 'section_id' => 3522, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203522], ['role_id' => 2, 'section_id' => 3522, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 103523], ['role_id' => 1, 'section_id' => 3523, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203523], ['role_id' => 2, 'section_id' => 3523, 'allow' => 1]);

        // Booking Content Settings (both roles)
        \App\Models\Permission::updateOrCreate(['id' => 103530], ['role_id' => 1, 'section_id' => 3530, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203530], ['role_id' => 2, 'section_id' => 3530, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 103531], ['role_id' => 1, 'section_id' => 3531, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203531], ['role_id' => 2, 'section_id' => 3531, 'allow' => 1]);

        // Booking Sellers (both roles)
        \App\Models\Permission::updateOrCreate(['id' => 103610], ['role_id' => 1, 'section_id' => 3610, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203610], ['role_id' => 2, 'section_id' => 3610, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 103611], ['role_id' => 1, 'section_id' => 3611, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203611], ['role_id' => 2, 'section_id' => 3611, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 103612], ['role_id' => 1, 'section_id' => 3612, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 203612], ['role_id' => 2, 'section_id' => 3612, 'allow' => 1]);

        // Panel permissions 100001 - 100366
        \App\Models\Permission::updateOrCreate(['id' => 100001], ['role_id' => 2, 'section_id' => 100001, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100002], ['role_id' => 2, 'section_id' => 100002, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100003], ['role_id' => 2, 'section_id' => 100003, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100004], ['role_id' => 2, 'section_id' => 100004, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100005], ['role_id' => 2, 'section_id' => 100005, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100010], ['role_id' => 2, 'section_id' => 100010, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100011], ['role_id' => 2, 'section_id' => 100011, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100012], ['role_id' => 2, 'section_id' => 100012, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100013], ['role_id' => 2, 'section_id' => 100013, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100014], ['role_id' => 2, 'section_id' => 100014, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100020], ['role_id' => 2, 'section_id' => 100020, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100021], ['role_id' => 2, 'section_id' => 100021, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100022], ['role_id' => 2, 'section_id' => 100022, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100023], ['role_id' => 2, 'section_id' => 100023, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100024], ['role_id' => 2, 'section_id' => 100024, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100025], ['role_id' => 2, 'section_id' => 100025, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100026], ['role_id' => 2, 'section_id' => 100026, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100027], ['role_id' => 2, 'section_id' => 100027, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100028], ['role_id' => 2, 'section_id' => 100028, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100029], ['role_id' => 2, 'section_id' => 100029, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100030], ['role_id' => 2, 'section_id' => 100030, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100031], ['role_id' => 2, 'section_id' => 100031, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100032], ['role_id' => 2, 'section_id' => 100032, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100033], ['role_id' => 2, 'section_id' => 100033, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100034], ['role_id' => 2, 'section_id' => 100034, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100035], ['role_id' => 2, 'section_id' => 100035, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100036], ['role_id' => 2, 'section_id' => 100036, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100037], ['role_id' => 2, 'section_id' => 100037, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100038], ['role_id' => 2, 'section_id' => 100038, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100039], ['role_id' => 2, 'section_id' => 100039, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100040], ['role_id' => 2, 'section_id' => 100040, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100041], ['role_id' => 2, 'section_id' => 100041, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100042], ['role_id' => 2, 'section_id' => 100042, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100043], ['role_id' => 2, 'section_id' => 100043, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100044], ['role_id' => 2, 'section_id' => 100044, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100045], ['role_id' => 2, 'section_id' => 100045, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100046], ['role_id' => 2, 'section_id' => 100046, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100050], ['role_id' => 2, 'section_id' => 100050, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100051], ['role_id' => 2, 'section_id' => 100051, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100052], ['role_id' => 2, 'section_id' => 100052, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100053], ['role_id' => 2, 'section_id' => 100053, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100054], ['role_id' => 2, 'section_id' => 100054, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100055], ['role_id' => 2, 'section_id' => 100055, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100060], ['role_id' => 2, 'section_id' => 100060, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100061], ['role_id' => 2, 'section_id' => 100061, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100062], ['role_id' => 2, 'section_id' => 100062, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100063], ['role_id' => 2, 'section_id' => 100063, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100070], ['role_id' => 2, 'section_id' => 100070, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100071], ['role_id' => 2, 'section_id' => 100071, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100072], ['role_id' => 2, 'section_id' => 100072, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100073], ['role_id' => 2, 'section_id' => 100073, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100080], ['role_id' => 2, 'section_id' => 100080, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100081], ['role_id' => 2, 'section_id' => 100081, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100082], ['role_id' => 2, 'section_id' => 100082, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100083], ['role_id' => 2, 'section_id' => 100083, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100084], ['role_id' => 2, 'section_id' => 100084, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100085], ['role_id' => 2, 'section_id' => 100085, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100086], ['role_id' => 2, 'section_id' => 100086, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100090], ['role_id' => 2, 'section_id' => 100090, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100091], ['role_id' => 2, 'section_id' => 100091, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100092], ['role_id' => 2, 'section_id' => 100092, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100093], ['role_id' => 2, 'section_id' => 100093, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100100], ['role_id' => 2, 'section_id' => 100100, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100101], ['role_id' => 2, 'section_id' => 100101, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100102], ['role_id' => 2, 'section_id' => 100102, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100103], ['role_id' => 2, 'section_id' => 100103, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100104], ['role_id' => 2, 'section_id' => 100104, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100105], ['role_id' => 2, 'section_id' => 100105, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100106], ['role_id' => 2, 'section_id' => 100106, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100107], ['role_id' => 2, 'section_id' => 100107, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100111], ['role_id' => 2, 'section_id' => 100111, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100112], ['role_id' => 2, 'section_id' => 100112, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100113], ['role_id' => 2, 'section_id' => 100113, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100120], ['role_id' => 2, 'section_id' => 100120, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100121], ['role_id' => 2, 'section_id' => 100121, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100122], ['role_id' => 2, 'section_id' => 100122, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100123], ['role_id' => 2, 'section_id' => 100123, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100124], ['role_id' => 2, 'section_id' => 100124, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100125], ['role_id' => 2, 'section_id' => 100125, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100126], ['role_id' => 2, 'section_id' => 100126, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100127], ['role_id' => 2, 'section_id' => 100127, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100140], ['role_id' => 2, 'section_id' => 100140, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100141], ['role_id' => 2, 'section_id' => 100141, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100142], ['role_id' => 2, 'section_id' => 100142, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100143], ['role_id' => 2, 'section_id' => 100143, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100160], ['role_id' => 2, 'section_id' => 100160, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100161], ['role_id' => 2, 'section_id' => 100161, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100162], ['role_id' => 2, 'section_id' => 100162, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100163], ['role_id' => 2, 'section_id' => 100163, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100164], ['role_id' => 2, 'section_id' => 100164, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100165], ['role_id' => 2, 'section_id' => 100165, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100166], ['role_id' => 2, 'section_id' => 100166, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100167], ['role_id' => 2, 'section_id' => 100167, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100180], ['role_id' => 2, 'section_id' => 100180, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100181], ['role_id' => 2, 'section_id' => 100181, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100182], ['role_id' => 2, 'section_id' => 100182, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100183], ['role_id' => 2, 'section_id' => 100183, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100184], ['role_id' => 2, 'section_id' => 100184, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100200], ['role_id' => 2, 'section_id' => 100200, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100201], ['role_id' => 2, 'section_id' => 100201, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100202], ['role_id' => 2, 'section_id' => 100202, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100203], ['role_id' => 2, 'section_id' => 100203, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100204], ['role_id' => 2, 'section_id' => 100204, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100220], ['role_id' => 2, 'section_id' => 100220, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100221], ['role_id' => 2, 'section_id' => 100221, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100222], ['role_id' => 2, 'section_id' => 100222, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100223], ['role_id' => 2, 'section_id' => 100223, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100224], ['role_id' => 2, 'section_id' => 100224, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100225], ['role_id' => 2, 'section_id' => 100225, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100240], ['role_id' => 2, 'section_id' => 100240, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100241], ['role_id' => 2, 'section_id' => 100241, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100260], ['role_id' => 2, 'section_id' => 100260, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100261], ['role_id' => 2, 'section_id' => 100261, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100280], ['role_id' => 2, 'section_id' => 100280, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100281], ['role_id' => 2, 'section_id' => 100281, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100300], ['role_id' => 2, 'section_id' => 100300, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100301], ['role_id' => 2, 'section_id' => 100301, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100302], ['role_id' => 2, 'section_id' => 100302, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100303], ['role_id' => 2, 'section_id' => 100303, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100320], ['role_id' => 2, 'section_id' => 100320, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100321], ['role_id' => 2, 'section_id' => 100321, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100330], ['role_id' => 2, 'section_id' => 100330, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100331], ['role_id' => 2, 'section_id' => 100331, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100332], ['role_id' => 2, 'section_id' => 100332, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100333], ['role_id' => 2, 'section_id' => 100333, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100334], ['role_id' => 2, 'section_id' => 100334, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100335], ['role_id' => 2, 'section_id' => 100335, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100336], ['role_id' => 2, 'section_id' => 100336, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100337], ['role_id' => 2, 'section_id' => 100337, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100360], ['role_id' => 2, 'section_id' => 100360, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100365], ['role_id' => 2, 'section_id' => 100365, 'allow' => 1]);
        \App\Models\Permission::updateOrCreate(['id' => 100366], ['role_id' => 2, 'section_id' => 100366, 'allow' => 1]);
    }
}