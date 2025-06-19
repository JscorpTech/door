<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\EmailTemplate;
use App\Models\HelpTopic;
use App\Models\Product;
use App\Models\VendorRegistrationReason;
use App\Traits\EmailTemplateTrait;
use App\Traits\SettingsTrait;
use App\Traits\UpdateClass;
use App\Utils\Helpers;
use App\Models\ShippingType;
use App\Models\BusinessSetting;
use App\Models\NotificationMessage;
use App\Traits\ActivationClass;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class InstallController extends Controller
{
    use ActivationClass, EmailTemplateTrait, SettingsTrait, UpdateClass;

    public function step0()
    {
        return view('installation.step0');
    }

    public function step1()
    {
        $permission['curl_enabled'] = function_exists('curl_version');
        $permission['db_file_write_perm'] = is_writable(base_path('.env'));
        $permission['routes_file_write_perm'] = is_writable(base_path('app/Providers/RouteServiceProvider.php'));
        return view('installation.step1', compact('permission'));
    }

    public function step2()
    {
        return view('installation.step2');
    }

    public function step3()
    {
        return view('installation.step3');
    }

    public function step4()
    {
        return view('installation.step4');
    }

    public function step5()
    {
        // Start symlink
        if (DOMAIN_POINTED_DIRECTORY == 'public' && function_exists('shell_exec')) {
            shell_exec('ln -s ../resources/themes themes');
            Artisan::call('storage:link');
        }
        // End symlink
        try {
            $this->setEnvironmentValue(envKey: 'APP_URL', envValue: url('/'));
        } catch (Exception $exception) {
        }

        self::updateRobotTexFile();
        Artisan::call('file:permission');
        Artisan::call('config:cache');
        Artisan::call('config:clear');
        return view('installation.step5');
    }

    public function purchase_code(Request $request)
    {
        Helpers::setEnvironmentValue('SOFTWARE_ID', 'MzE0NDg1OTc=');
        Helpers::setEnvironmentValue('BUYER_USERNAME', $request['username']);
        Helpers::setEnvironmentValue('PURCHASE_CODE', $request['purchase_key']);

        $post = [
            'name' => $request['name'],
            'email' => $request['email'],
            'username' => $request['username'],
            'purchase_key' => $request['purchase_key'],
            'domain' => preg_replace("#^[^:/.]*[:/]+#i", "", url('/')),
        ];
        $response = $this->dmvf($post);

        return redirect($response . '?token=' . bcrypt('step_3'));
    }

    public function system_settings(Request $request)
    {
        DB::table('admins')->insertOrIgnore([
            'name' => $request['admin_name'],
            'email' => $request['admin_email'],
            'admin_role_id' => 1,
            'password' => bcrypt($request['admin_password']),
            'phone' => $request['admin_phone'],
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('admin_wallets')->insert([
            'admin_id' => 1,
            'withdrawn' => 0,
            'commission_earned' => 0,
            'inhouse_earning' => 0,
            'delivery_charge_earned' => 0,
            'pending_amount' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->businessSettingGetOrInsert(type: 'company_name', value: $request['company_name']);
        $this->businessSettingGetOrInsert(type: 'product_brand', value: 1);
        $this->businessSettingGetOrInsert(type: 'digital_product', value: 1);
        $this->businessSettingGetOrInsert(type: 'delivery_boy_expected_delivery_date_message', value: json_encode(['status' => 0, 'message' => '']));
        $this->businessSettingGetOrInsert(type: 'order_canceled', value: json_encode(['status' => 0, 'message' => '']));
        $this->businessSettingGetOrInsert(type: 'offline_payment', value: json_encode(['status' => 0]));

        DB::table('business_settings')->updateOrInsert(['type' => 'currency_model'], [
            'value' => $request['currency_model']
        ]);

        $this->updateOrInsertPolicy(type: 'refund-policy');
        $this->updateOrInsertPolicy(type: 'return-policy');
        $this->updateOrInsertPolicy(type: 'cancellation-policy');

        $this->businessSettingGetOrInsert(type: 'temporary_close', value: json_encode(['status' => 0]));
        $this->businessSettingGetOrInsert(type: 'vacation_add', value: json_encode([
            'status' => 0,
            'vacation_start_date' => null,
            'vacation_end_date' => null,
            'vacation_note' => null
        ]));

        $this->businessSettingGetOrInsert(type: 'cookie_setting', value: json_encode([
            'status' => 0,
            'cookie_text' => null
        ]));

        DB::table('colors')->whereIn('id', [16, 38, 93])->delete();

        $this->businessSettingGetOrInsert(type: 'apple_login', value: json_encode([[
            'login_medium' => 'apple',
            'client_id' => '',
            'client_secret' => '',
            'status' => 0,
            'team_id' => '',
            'key_id' => '',
            'service_file' => '',
            'redirect_url' => '',
        ]]));

        $this->businessSettingGetOrInsert(type: 'ref_earning_status', value: 0);
        $this->businessSettingGetOrInsert(type: 'ref_earning_exchange_rate', value: 0);

        // new payment module necessary table insert
        try {
            if (!Schema::hasTable('addon_settings')) {
                $sql = File::get(base_path('database/migrations/addon_settings.sql'));
                DB::unprepared($sql);
            }

            if (!Schema::hasTable('payment_requests')) {
                $sql = File::get(base_path('database/migrations/payment_requests.sql'));
                DB::unprepared($sql);
            }

            $this->set_data();
        } catch (\Exception $exception) {
            //
        }

        $this->businessSettingGetOrInsert(type: 'guest_checkout', value: 0);
        $this->businessSettingGetOrInsert(type: 'minimum_order_amount', value: 0);
        $this->businessSettingGetOrInsert(type: 'minimum_order_amount_by_seller', value: 0);
        $this->businessSettingGetOrInsert(type: 'minimum_order_amount_status', value: 0);
        $this->businessSettingGetOrInsert(type: 'admin_login_url', value: 'admin');
        $this->businessSettingGetOrInsert(type: 'employee_login_url', value: 'employee');
        $this->businessSettingGetOrInsert(type: 'free_delivery_status', value: 0);
        $this->businessSettingGetOrInsert(type: 'free_delivery_responsibility', value: 'admin');
        $this->businessSettingGetOrInsert(type: 'free_delivery_over_amount', value: 0);
        $this->businessSettingGetOrInsert(type: 'free_delivery_over_amount_seller', value: 0);
        $this->businessSettingGetOrInsert(type: 'add_funds_to_wallet', value: 0);
        $this->businessSettingGetOrInsert(type: 'minimum_add_fund_amount', value: 0);
        $this->businessSettingGetOrInsert(type: 'whatsapp', value: json_encode(['status' => 1, 'phone' => '00000000000']));
        $this->businessSettingGetOrInsert(type: 'currency_symbol_position', value: 'left');

        // Data insert into shipping table
        $new_shipping_type = new ShippingType;
        $new_shipping_type->seller_id = 0;
        $new_shipping_type->shipping_type = 'order_wise';
        $new_shipping_type->save();

        self::notification_message_import(); // notification message add in the new table
        self::company_riliability_import(); // company riliability add in the new table

        $this->businessSettingGetOrInsert(type: 'app_activation', value: json_encode(['software_id' => '', 'is_active' => 0]));

        if (!NotificationMessage::where(['key' => 'product_request_approved_message'])->first()) {
            DB::table('notification_messages')->updateOrInsert([
                'key' => 'product_request_approved_message'
            ],
                [
                    'user_type' => 'seller',
                    'key' => 'product_request_approved_message',
                    'message' => 'customize your product request approved message message',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        if (!NotificationMessage::where(['key' => 'product_request_rejected_message'])->first()) {
            DB::table('notification_messages')->updateOrInsert([
                'key' => 'product_request_rejected_message'
            ],
                [
                    'user_type' => 'seller',
                    'key' => 'product_request_rejected_message',
                    'message' => 'customize your product request rejected message message',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->businessSettingGetOrInsert(type: 'map_api_status', value: 1);

        // Priority setup and vendor registration data process
        $this->getPrioritySetupAndVendorRegistrationData();

        if (Admin::count() > 0 && EmailTemplate::count() < 1) {
            $emailTemplateUserData = [
                'admin',
                'customer',
                'vendor',
                'delivery-man',
            ];
            foreach ($emailTemplateUserData as $key => $value) {
                $this->getEmailTemplateDataForUpdate($value);
            }
        }

        $this->businessSettingGetOrInsert(type: 'storage_connection_type', value: 'public');
        $this->businessSettingGetOrInsert(type: 'google_search_console_code', value: '');
        $this->businessSettingGetOrInsert(type: 'bing_webmaster_code', value: '');
        $this->businessSettingGetOrInsert(type: 'baidu_webmaster_code', value: '');
        $this->businessSettingGetOrInsert(type: 'yandex_webmaster_code', value: '');

        $previousRouteServiceProvider = base_path('app/Providers/RouteServiceProvider.php');
        $newRouteServiceProvider = base_path('app/Providers/RouteServiceProvider.txt');
        copy($newRouteServiceProvider, $previousRouteServiceProvider);
        //sleep(5);
        return view('installation.step6');
    }

    public static function updateRobotTexFile(): void
    {
        try {
            $path = DOMAIN_POINTED_DIRECTORY == 'public' ? public_path('robots.txt') : base_path('robots.txt');
            if (!File::exists($path)) {
                fopen($path, "w") or die("Unable to open file!");
            }
            $content = "User-agent: *\nDisallow: /login/admin/\nSitemap: " . url('/sitemap.xml');
            if (!File::exists($path)) {
                File::put($path, $content);
            }
            File::put($path, $content);
        } catch (\Exception $exception) {
        }
    }

    public static function notification_message_import()
    {
        /** for customer */
        $user_type_customer = NotificationMessage::where('user_type', 'customer')->get();
        $array_for_customer_message_key = [
            'order_pending_message',
            'order_confirmation_message',
            'order_processing_message',
            'out_for_delivery_message',
            'order_delivered_message',
            'order_returned_message',
            'order_failed_message',
            'order_canceled',
            'order_refunded_message',
            'refund_request_canceled_message',
            'message_from_delivery_man',
            'message_from_seller',
            'fund_added_by_admin_message',
        ];
        foreach ($array_for_customer_message_key as $key => $value) {
            $key_check = $user_type_customer->where('key', $value)->first();
            if ($key_check == null) {
                DB::table('notification_messages')->updateOrInsert(['user_type' => 'customer'],
                    [
                        'user_type' => 'customer',
                        'key' => $value,
                        'message' => 'customize your' . ' ' . str_replace('_', ' ', $value) . ' ' . 'message',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
        /**end for customer*/

        $user_type_seller = NotificationMessage::where('user_type', 'seller')->get();
        $array_for_seller_message_key = [
            'new_order_message',
            'refund_request_message',
            'order_edit_message',
            'withdraw_request_status_message',
            'message_from_customer',
            'delivery_man_assign_by_admin_message',
            'order_delivered_message',
            'order_canceled',
            'order_refunded_message',
            'refund_request_canceled_message',
            'refund_request_status_changed_by_admin',

        ];
        foreach ($array_for_seller_message_key as $key => $value) {
            $key_check = $user_type_seller->where('key', $value)->first();
            if ($key_check == null) {
                DB::table('notification_messages')->insert([
                    'user_type' => 'seller',
                    'key' => $value,
                    'message' => 'customize your' . ' ' . str_replace('_', ' ', $value) . ' ' . 'message',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        /**end for seller*/

        /**start delivery man*/
        $user_type_delivery_man = NotificationMessage::where('user_type', 'delivery_man')->get();
        $array_for_delivery_man_message_key = [
            'new_order_assigned_message',
            'expected_delivery_date',
            'delivery_man_charge',
            'order_canceled',
            'order_rescheduled_message',
            'order_edit_message',
            'message_from_seller',
            'message_from_admin',
            'message_from_customer',
            'cash_collect_by_admin_message',
            'cash_collect_by_seller_message',
            'withdraw_request_status_message',

        ];
        foreach ($array_for_delivery_man_message_key as $key => $value) {
            $key_check = $user_type_delivery_man->where('key', $value)->first();
            if ($key_check == null) {
                DB::table('notification_messages')->insert([
                    'user_type' => 'delivery_man',
                    'key' => $value,
                    'message' => 'customize your' . ' ' . str_replace('_', ' ', $value) . ' ' . 'message',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        /**end for delivery man*/
    }

    public static function company_riliability_import()
    {
        $data = [
            [
                'item' => 'delivery_info',
                'title' => 'Fast Delivery all across the country',
                'image' => '',
                'status' => 1,
            ],
            [
                'item' => 'safe_payment',
                'title' => 'Safe Payment',
                'image' => '',
                'status' => 1,
            ],
            [
                'item' => 'return_policy',
                'title' => '7 Days Return Policy',
                'image' => '',
                'status' => 1,
            ],
            [
                'item' => 'authentic_product',
                'title' => '100% Authentic Products',
                'image' => '',
                'status' => 1,
            ],
        ];

        if (BusinessSetting::where(['type' => 'company_reliability'])->first() == false) {
            BusinessSetting::insert(['type' => 'company_reliability'], [
                'value' => json_encode($data),
            ]);
        }
    }

    public function database_installation(Request $request)
    {
        if (self::check_database_connection($request->DB_HOST, $request->DB_DATABASE, $request->DB_USERNAME, $request->DB_PASSWORD)) {

            $key = base64_encode(random_bytes(32));
            $output = 'APP_NAME=6valley' . time() . '
                    APP_ENV=live
                    APP_KEY=base64:' . $key . '
                    APP_DEBUG=false
                    APP_INSTALL=true
                    APP_LOG_LEVEL=debug
                    APP_MODE=live
                    APP_URL=' . URL::to('/') . '

                    DB_CONNECTION=mysql
                    DB_HOST=' . $request->DB_HOST . '
                    DB_PORT=3306
                    DB_DATABASE=' . $request->DB_DATABASE . '
                    DB_USERNAME=' . $request->DB_USERNAME . '
                    DB_PASSWORD=' . $request->DB_PASSWORD . '

                    BROADCAST_DRIVER=log
                    CACHE_DRIVER=file
                    SESSION_DRIVER=file
                    SESSION_LIFETIME=60
                    QUEUE_DRIVER=sync

                    AWS_ENDPOINT=
                    AWS_ACCESS_KEY_ID=
                    AWS_SECRET_ACCESS_KEY=
                    AWS_DEFAULT_REGION=us-east-1
                    AWS_BUCKET=

                    REDIS_HOST=127.0.0.1
                    REDIS_PASSWORD=null
                    REDIS_PORT=6379

                    PUSHER_APP_ID=
                    PUSHER_APP_KEY=
                    PUSHER_APP_SECRET=
                    PUSHER_APP_CLUSTER=mt1

                    PURCHASE_CODE=' . session('purchase_key') . '
                    BUYER_USERNAME=' . session('username') . '
                    SOFTWARE_ID=MzE0NDg1OTc=

                    SOFTWARE_VERSION=' . SOFTWARE_VERSION . '
                    ';
            $file = fopen(base_path('.env'), 'w');
            fwrite($file, $output);
            fclose($file);

            $path = base_path('.env');
            if (file_exists($path)) {
                return redirect('step4');
            } else {
                session()->flash('error', 'Database error!');
                return redirect('step3');
            }
        } else {
            session()->flash('error', 'Database error!');
            return redirect('step3');
        }
    }

    public function import_sql()
    {
        try {
            $sql_path = base_path('installation/backup/database.sql');
            DB::unprepared(file_get_contents($sql_path));
            return redirect('step5');
        } catch (\Exception $exception) {
            session()->flash('error', 'Your database is not clean, do you want to clean database then import?');
            return back();
        }
    }

    public function force_import_sql()
    {
        try {
            Artisan::call('db:wipe');
            $sql_path = base_path('installation/backup/database.sql');
            DB::unprepared(file_get_contents($sql_path));
            return redirect('step5');
        } catch (\Exception $exception) {
            session()->flash('error', 'Check your database permission!');
            return back();
        }
    }

    function check_database_connection($db_host = "", $db_name = "", $db_user = "", $db_pass = "")
    {

        if (@mysqli_connect($db_host, $db_user, $db_pass, $db_name)) {
            return true;
        } else {
            return false;
        }
    }
}
