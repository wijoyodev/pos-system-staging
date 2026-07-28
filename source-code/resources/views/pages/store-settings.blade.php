<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full">

        <header
            class="top-0 z-40 sticky bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border-b border-slate-200/20 dark:border-slate-800/20 shadow-sm dark:shadow-none flex justify-between items-center w-full px-4 lg:px-6 py-3">
            <div class="flex items-center gap-4 lg:gap-8">
                <span
                    class="text-base lg:text-xl font-bold text-blue-900 dark:text-blue-400 tracking-tight font-manrope">{{ $title }}</span>
            </div>
            <div class="flex items-center gap-2 lg:gap-4">
                @if(auth()->user()->isSuperAdmin() && $stores->count() > 1)
                    <div class="hidden sm:block">
                        <select onchange="location.href='/store-settings?store_id='+this.value"
                            class="text-xs font-bold bg-transparent border border-slate-200 rounded-lg py-1.5 px-3 text-on-surface-variant font-manrope">
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                                    {{ $store->branch->name }} - {{ $store->name }} ({{ $store->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="h-6 lg:h-8 w-[1px] bg-slate-200 mx-1 lg:mx-2 hidden sm:block"></div>
                <div class="flex items-center gap-2 lg:gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-[10px] lg:text-xs font-bold text-on-surface font-manrope">
                            {{ auth()->user()->name }}</p>
                        <p class="text-[8px] lg:text-[10px] text-slate-500 font-body uppercase tracking-widest">
                            {{ auth()->user()->role_label }}</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="w-full px-4 lg:px-8 py-6 lg:py-10">
            @php
                $storeDesc = auth()->user()->isSuperAdmin() ? 'Konfigurasi pengaturan toko per lokasi. Pilih toko di atas untuk mengedit pengaturan spesifik.' : 'Kelola konfigurasi toko Anda. Pengaturan hanya berlaku untuk toko Anda.';
            @endphp
            <div class="mb-6 lg:mb-10">
                <x-report-header title="Store Settings" module="Configuration" submodule="Store" :description="$storeDesc" />
            </div>

            @if(session('success'))
                <div class="mb-8 p-4 bg-primary-container text-on-primary-container rounded-lg font-semibold tracking-wide">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-8 p-4 bg-error-container text-on-error-container rounded-lg font-semibold tracking-wide">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(!$currentStore)
                <div class="p-8 bg-surface-container-lowest rounded-xl text-center">
                    <span class="material-symbols-outlined text-6xl text-slate-300 mb-4 block">store</span>
                    <h3 class="text-lg font-bold text-on-surface mb-2">Tidak ada toko yang tersedia</h3>
                    <p class="text-sm text-slate-500">Silakan hubungi administrator untuk assignment toko.</p>
                </div>
            @else

                <form action="/store-settings" method="POST" enctype="multipart/form-data" class="grid grid-cols-12 gap-8">
                    @csrf
                    <input type="hidden" name="store_id" value="{{ $storeId }}">

                    <section
                        class="col-span-12 lg:col-span-8 bg-surface-container-lowest rounded-xl p-8 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary"
                                    style="font-variation-settings: 'FILL' 1;">store</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold font-manrope text-blue-900">Store Profile</h3>
                                <p class="text-sm text-on-surface-variant">Identitas toko Anda di struk & sistem</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Store Name</label>
                                <input name="store_name"
                                    class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body"
                                    type="text" value="{{ old('store_name', $settings['store_name'] ?? '') }}" required />
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Phone Number</label>
                                <input name="store_phone"
                                    class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body"
                                    type="tel" value="{{ old('store_phone', $settings['store_phone'] ?? '') }}" />
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Store Address</label>
                                <textarea name="store_address"
                                    class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body"
                                    rows="2">{{ old('store_address', $settings['store_address'] ?? '') }}</textarea>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-semibold mb-3 text-on-surface-variant font-label">Store Logo</label>
                                <div class="flex items-center gap-6 p-6 border-2 border-dashed border-outline-variant/30 rounded-xl bg-surface-container-low">
                                    <div class="w-20 h-20 bg-white rounded-xl shadow-sm flex items-center justify-center overflow-hidden">
                                        @if(!empty($settings['store_logo']))
                                            <img src="{{ asset('storage/' . $settings['store_logo']) }}" alt="Store Logo"
                                                class="w-full h-full object-cover">
                                        @else
                                            <span class="material-symbols-outlined text-outline">image</span>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" name="store_logo"
                                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-container file:text-primary hover:file:bg-primary-container/80 transition-colors">
                                        <p class="text-xs text-on-surface-variant mt-2">Recommended size: 512x512px. JPG, PNG or SVG.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="col-span-12 lg:col-span-4 bg-surface-container-lowest rounded-xl p-8 shadow-sm">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-10 h-10 rounded-lg bg-secondary-container/30 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary">receipt_long</span>
                            </div>
                            <h3 class="font-bold font-manrope text-blue-900">Tax &amp; Fees</h3>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-surface rounded-lg">
                                <div>
                                    <h4 class="font-bold text-sm font-manrope">VAT (PPN)</h4>
                                    <p class="text-xs text-on-surface-variant font-body">Pajak pertambahan nilai</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input name="vat"
                                        class="w-16 bg-white border-none rounded text-right font-manrope font-bold text-primary focus:ring-2 focus:ring-primary/10"
                                        type="number" step="0.01" value="{{ old('vat', $settings['vat'] ?? '11') }}" />
                                    <span class="font-bold text-primary">%</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-surface rounded-lg">
                                <div>
                                    <h4 class="font-bold text-sm font-manrope">Service Charge</h4>
                                    <p class="text-xs text-on-surface-variant font-body">Biaya layanan internal</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input name="service_charge"
                                        class="w-16 bg-white border-none rounded text-right font-manrope font-bold text-primary focus:ring-2 focus:ring-primary/10"
                                        type="number" step="0.01"
                                        value="{{ old('service_charge', $settings['service_charge'] ?? '0') }}" />
                                    <span class="font-bold text-primary">%</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-surface rounded-lg">
                                <div>
                                    <h4 class="font-bold text-sm font-manrope">Opening Balance</h4>
                                    <p class="text-xs text-on-surface-variant font-body">Modal awal kasir</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-primary">Rp</span>
                                    <input name="opening_balance"
                                        class="w-24 bg-white border-none rounded text-right font-manrope font-bold text-primary focus:ring-2 focus:ring-primary/10"
                                        type="number"
                                        value="{{ old('opening_balance', $settings['opening_balance'] ?? '0') }}" />
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="col-span-12 lg:col-span-4 bg-surface-container-lowest rounded-xl p-8 shadow-sm">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-slate-600">print</span>
                            </div>
                            <div>
                                <h3 class="font-bold font-manrope text-blue-900">Printer</h3>
                                <p class="text-xs text-on-surface-variant">Konfigurasi pencetakan struk</p>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-on-surface font-body">Auto-print receipts</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="auto_print" value="1" class="sr-only peer" {{ ($settings['auto_print'] ?? '0') == '1' ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                    </div>
                                </label>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-2 text-on-surface-variant font-label">Printer Device</label>
                                <input name="printer_device"
                                    class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-2 px-3 font-body text-sm"
                                    type="text"
                                    value="{{ old('printer_device', $settings['printer_device'] ?? 'PDF Virtual Printer') }}" />
                            </div>
                        </div>
                    </section>

                    <section class="col-span-12 lg:col-span-4 bg-surface-container-lowest rounded-xl p-8 shadow-sm">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-orange-600">pending_actions</span>
                            </div>
                            <div>
                                <h3 class="font-bold font-manrope text-blue-900">Pending Order</h3>
                                <p class="text-xs text-on-surface-variant">Konfigurasi order pending</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Expiry Time (minutes)</label>
                                <input name="pending_order_expiry"
                                    class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body"
                                    type="number" min="1" max="1440"
                                    value="{{ old('pending_order_expiry', $settings['pending_order_expiry'] ?? '10') }}" />
                                <p class="text-xs text-slate-400 mt-1">Batas waktu sebelum order expired (1-1440 menit)</p>
                            </div>
                        </div>
                    </section>

                    @if(auth()->user()->isSuperAdmin())
                        <section class="col-span-12 lg:col-span-4 bg-surface-container-lowest rounded-xl p-8 shadow-sm">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-yellow-600">stars</span>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold font-manrope text-blue-900">Loyalty Points</h3>
                                    <p class="text-sm text-on-surface-variant">Konfigurasi poin member per toko</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Points Earned (per Rp)</label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-slate-500">1 point per</span>
                                        <input name="loyalty_points_per_rupiah"
                                            class="w-24 bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-2 px-3 font-body text-sm text-center"
                                            type="number" min="1000" step="1000"
                                            value="{{ old('loyalty_points_per_rupiah', $settings['loyalty_points_per_rupiah'] ?? '10000') }}" />
                                        <span class="text-sm text-slate-500">Rupiah</span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">Example: 10000 = 1 point per Rp 10,000</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Points Redemption Value</label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-slate-500">1 point =</span>
                                        <input name="loyalty_point_value"
                                            class="w-24 bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-2 px-3 font-body text-sm text-center"
                                            type="number" min="100" step="100"
                                            value="{{ old('loyalty_point_value', $settings['loyalty_point_value'] ?? '1000') }}" />
                                        <span class="text-sm text-slate-500">Rupiah</span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">Example: 1000 = 1 point = Rp 1,000 discount</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Minimum Points to Redeem</label>
                                    <input name="loyalty_min_redeem"
                                        class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body"
                                        type="number" min="1"
                                        value="{{ old('loyalty_min_redeem', $settings['loyalty_min_redeem'] ?? '10') }}" />
                                </div>
                            </div>
                        </section>
                    @endif

                    @if(auth()->user()->isSuperAdmin())
                        <section class="col-span-12 lg:col-span-8 bg-surface-container-lowest rounded-xl p-8 shadow-sm">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-12 h-12 rounded-lg bg-tertiary/10 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-tertiary">language</span>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold font-manrope text-blue-900">Global Settings</h3>
                                    <p class="text-sm text-on-surface-variant">Pengaturan sistem yang berlaku untuk semua toko</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-on-surface-variant mb-1 uppercase tracking-tighter">Currency Code</label>
                                    <select name="global_currency_code"
                                        class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-2 px-3 font-body text-sm">
                                        <option value="id-ID" {{ ($settings['currency_code'] ?? 'id-ID') == 'id-ID' ? 'selected' : '' }}>IDR - Indonesian Rupiah</option>
                                        <option value="en-US" {{ ($settings['currency_code'] ?? 'id-ID') == 'en-US' ? 'selected' : '' }}>USD - US Dollar</option>
                                        <option value="en-SG" {{ ($settings['currency_code'] ?? 'id-ID') == 'en-SG' ? 'selected' : '' }}>SGD - Singapore Dollar</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-on-surface-variant mb-1 uppercase tracking-tighter">Date Format</label>
                                    <select name="global_date_format"
                                        class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-2 px-3 font-body text-sm">
                                        <option value="d/m/Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                        <option value="m/d/Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                        <option value="Y-m-d" {{ ($settings['date_format'] ?? 'd/m/Y') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-on-surface-variant mb-1 uppercase tracking-tighter">Timezone</label>
                                    <select name="global_timezone"
                                        class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-2 px-3 font-body text-sm">
                                        <option value="Asia/Jakarta" {{ ($settings['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jakarta' ? 'selected' : '' }}>(GMT+07:00) Jakarta</option>
                                        <option value="Asia/Singapore" {{ ($settings['timezone'] ?? 'Asia/Jakarta') == 'Asia/Singapore' ? 'selected' : '' }}>(GMT+08:00) Singapore</option>
                                    </select>
                                </div>
                            </div>
                        </section>
                    @endif

                    <div class="col-span-12 flex justify-end items-center gap-4 mt-4 py-6 border-t border-slate-200">
                        <button type="reset"
                            class="px-8 py-3 bg-transparent text-primary font-bold font-manrope rounded-lg hover:bg-slate-100 transition-all">
                            Reset Forms
                        </button>
                        <button type="submit"
                            class="px-10 py-3 bg-primary-container text-white font-bold font-manrope rounded-lg shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                            Save Changes
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </main>
</x-layout>
