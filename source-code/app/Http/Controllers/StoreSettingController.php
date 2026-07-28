<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Store;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class StoreSettingController extends Controller
{
    private $perStoreKeys = [
        'store_name', 'store_phone', 'store_address', 'store_logo',
        'vat', 'service_charge',
        'loyalty_points_per_rupiah', 'loyalty_point_value', 'loyalty_min_redeem',
        'opening_balance', 'auto_print', 'printer_device',
        'pending_order_expiry',
    ];

    private $globalKeys = [
        'currency_code', 'date_format', 'timezone',
    ];

    public function index()
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengubah pengaturan toko.');
        }

        $user = auth()->user();
        $stores = Store::where('status', 'active')->with('branch')->get();
        $storeId = request('store_id', $stores->first()?->id);
        $currentStore = $stores->firstWhere('id', $storeId);

        // Get settings
        if ($storeId && $currentStore) {
            $storeSettings = StoreSetting::getAllForStore($storeId);
        } else {
            $storeSettings = [];
        }

        // Merge with global settings for display defaults
        $allSettings = [];
        foreach ($this->perStoreKeys as $key) {
            $allSettings[$key] = $storeSettings[$key] ?? $this->getGlobalDefault($key);
        }
        foreach ($this->globalKeys as $key) {
            $allSettings[$key] = Setting::getVal($key, $this->getGlobalDefault($key));
        }

        return view('pages.store-settings', [
            'title' => 'Pengaturan Toko',
            'stores' => $user->isSuperAdmin() ? $stores : collect([$currentStore]),
            'currentStore' => $currentStore,
            'settings' => $allSettings,
            'storeId' => $storeId,
        ]);
    }

    public function update(Request $request)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $user = auth()->user();
        $storeId = $request->store_id;

        if (! $storeId) {
            return back()->with('error', 'Toko tidak valid.');
        }

        $baseRules = [
            'store_name' => 'nullable|string|max:255',
            'store_phone' => 'nullable|string|max:50',
            'store_address' => 'nullable|string',
            'store_logo' => 'nullable|image|max:2048',
            'vat' => 'nullable|numeric|min:0|max:100',
            'service_charge' => 'nullable|numeric|min:0|max:100',
            'opening_balance' => 'nullable|numeric|min:0',
            'auto_print' => 'nullable|boolean',
            'printer_device' => 'nullable|string|max:255',
            'pending_order_expiry' => 'nullable|integer|min:1|max:1440',
        ];

        // Voucher and Loyalty settings only for Super Admin
        if ($user->isSuperAdmin()) {
            $baseRules['loyalty_points_per_rupiah'] = 'nullable|integer|min:1';
            $baseRules['loyalty_point_value'] = 'nullable|integer|min:1';
            $baseRules['loyalty_min_redeem'] = 'nullable|integer|min:1';
        }

        $validated = $request->validate($baseRules);

        // Handle file upload
        if ($request->hasFile('store_logo')) {
            $path = $request->file('store_logo')->store('logos', 'public');
            $validated['store_logo'] = $path;
        }

        // Boolean handling
        $validated['auto_print'] = $request->has('auto_print') ? '1' : '0';

        // Remove voucher/loyalty keys if admin (not super admin)
        if (! $user->isSuperAdmin()) {
            unset($validated['loyalty_points_per_rupiah']);
            unset($validated['loyalty_point_value']);
            unset($validated['loyalty_min_redeem']);
        }

        StoreSetting::setMany($validated, $storeId);

        // Handle global settings (Super Admin only)
        if (auth()->user()->isSuperAdmin()) {
            if ($request->filled('global_currency_code')) {
                Setting::setVal('currency_code', $request->global_currency_code);
            }
            if ($request->filled('global_date_format')) {
                Setting::setVal('date_format', $request->global_date_format);
            }
            if ($request->filled('global_timezone')) {
                Setting::setVal('timezone', $request->global_timezone);
            }
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function updateGlobal(Request $request)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'currency_code' => 'nullable|string|max:10',
            'date_format' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            Setting::setVal($key, $value);
        }

        return back()->with('success', 'Pengaturan global berhasil disimpan.');
    }

    public function paymentSetting()
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $keys = [
            'midtrans_server_key', 'midtrans_client_key', 'midtrans_is_production',
            'aws_key', 'aws_secret', 'aws_region', 'aws_bucket',
            'mail_host', 'mail_port', 'mail_username', 'mail_password',
            'mail_encryption', 'mail_from_address', 'mail_from_name',
            'slack_webhook_url',
            'postmark_api_key',
            'resend_api_key',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = Setting::getVal($key, '');
        }

        return view('pages.payment-setting', [
            'title' => 'Pengaturan Integrasi',
            'settings' => $settings,
        ]);
    }

    public function updatePayment(Request $request)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'midtrans_server_key' => 'nullable|string|max:255',
            'midtrans_client_key' => 'nullable|string|max:255',
            'midtrans_is_production' => 'nullable|boolean',
            'aws_key' => 'nullable|string|max:255',
            'aws_secret' => 'nullable|string|max:255',
            'aws_region' => 'nullable|string|max:50',
            'aws_bucket' => 'nullable|string|max:255',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|string|max:10',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|max:10',
            'mail_from_address' => 'nullable|string|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'slack_webhook_url' => 'nullable|string|max:500',
            'postmark_api_key' => 'nullable|string|max:255',
            'resend_api_key' => 'nullable|string|max:255',
        ]);

        $map = [
            'midtrans_server_key', 'midtrans_client_key',
            'aws_key', 'aws_secret', 'aws_region', 'aws_bucket',
            'mail_host', 'mail_port', 'mail_username', 'mail_password',
            'mail_encryption', 'mail_from_address', 'mail_from_name',
            'slack_webhook_url', 'postmark_api_key', 'resend_api_key',
        ];

        foreach ($map as $key) {
            Setting::setVal($key, $request->$key ?? '');
        }

        Setting::setVal('midtrans_is_production', $request->has('midtrans_is_production') ? '1' : '0');

        return back()->with('success', 'Pengaturan integrasi berhasil disimpan.');
    }

    private function getGlobalDefault($key)
    {
        $defaults = [
            'store_name' => 'Main Branch',
            'store_phone' => '+62 21 5550 1234',
            'store_address' => '-',
            'store_logo' => null,
            'vat' => '11',
            'service_charge' => '0',
            'loyalty_points_per_rupiah' => '10000',
            'loyalty_point_value' => '1000',
            'loyalty_min_redeem' => '10',
            'opening_balance' => '0',
            'auto_print' => '0',
            'printer_device' => 'PDF Virtual Printer',
            'pending_order_expiry' => '10',
            'currency_code' => 'id-ID',
            'date_format' => 'd/m/Y',
            'timezone' => 'Asia/Jakarta',
        ];

        return $defaults[$key] ?? null;
    }
}
