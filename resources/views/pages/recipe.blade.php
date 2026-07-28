<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    @php
        $storeId = $history->store_id ?? auth()->user()->store_id;
        $storeName = \App\Models\StoreSetting::getVal('store_name', $storeId, 'Toko Saya');
        $pmLabel = $history->payment_method === 'split'
            ? $history->payments->pluck('method')->map(fn($m) => ucfirst($m))->join(' + ')
            : ucfirst($history->payment_method);
        $waText = "Terima kasih telah berbelanja di *{$storeName}*!\n\n";
        $waText .= "Invoice: {$history->invoice_id}\n";
        $waText .= "Total: Rp" . number_format($history->total_amount, 0, ',', '.') . "\n";
        $waText .= "Metode: {$pmLabel}\n";
        if ($history->customer && $pointsEarned > 0) {
            $waText .= "\nAnda mendapat " . number_format($pointsEarned) . " poin!";
        }
        $waText .= "\n\nSelamat datang kembali!";

        $customerPhone = $history->customer ? preg_replace('/[^0-9]/', '', $history->customer->phone) : '';
        if ($customerPhone && substr($customerPhone, 0, 1) == '0') {
            $customerPhone = '62' . substr($customerPhone, 1);
        } elseif ($customerPhone && substr($customerPhone, 0, 2) != '62') {
            $customerPhone = '62' . $customerPhone;
        }
        $waLink = $customerPhone ? "https://wa.me/{$customerPhone}?text=" . urlencode($waText) : "https://wa.me/?text=" . urlencode($waText);
    @endphp

    <main class="flex-1 flex flex-col min-h-screen relative w-full pb-8 print:pb-0 print:bg-white">

        <div class="max-w-xl mx-auto w-full px-4 sm:px-6 mt-6 sm:mt-10 flex flex-col items-center">

            <!-- Receipt Card -->
            <div class="w-full bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden print:shadow-none print:border-none print:rounded-none">

                <!-- Store Header -->
                <div class="p-5 sm:p-6 border-b-2 border-dashed border-slate-100 text-center">
                    <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">{{ $storeName }}</h2>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                        {{ \App\Models\StoreSetting::getVal('store_address', $storeId, '-') }}<br />
                        {{ \App\Models\StoreSetting::getVal('store_phone', $storeId, '-') }}
                    </p>
                </div>

                <!-- Info Row -->
                <div class="px-5 sm:px-6 py-3 bg-slate-50 flex flex-wrap justify-between gap-1 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-slate-400">badge</span>
                        <span class="font-medium text-slate-600">{{ $history->cashier_name }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-slate-400">calendar_today</span>
                        <span>{{ $history->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                @if($history->customer)
                <div class="px-5 sm:px-6 py-3 bg-amber-50 border-y border-amber-100 flex flex-wrap justify-between items-center gap-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500 text-lg">person</span>
                        <span class="text-sm font-bold text-amber-800">{{ $history->customer->name }}</span>
                        <span class="px-1.5 py-0.5 bg-amber-200 text-amber-700 text-[10px] font-bold rounded uppercase">{{ $history->customer->tier }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-amber-500 text-sm">stars</span>
                        <span class="text-sm font-bold text-amber-700">{{ number_format($history->customer->available_points) }} pts</span>
                    </div>
                </div>
                @endif

                <!-- Invoice ID -->
                <div class="px-5 sm:px-6 py-2 text-center">
                    <span class="text-[10px] font-mono text-slate-300 tracking-[0.2em]">{{ $history->invoice_id }}</span>
                </div>

                <!-- Items Header -->
                <div class="px-5 sm:px-6">
                    <div class="grid grid-cols-12 gap-2 py-2 border-t border-dashed border-slate-200 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <div class="col-span-6">Item</div>
                        <div class="col-span-2 text-center">Qty</div>
                        <div class="col-span-4 text-right">Subtotal</div>
                    </div>
                </div>

                <!-- Items -->
                <div class="px-5 sm:px-6 pb-3">
                    @foreach($history->items as $item)
                    <div class="grid grid-cols-12 gap-2 py-2.5 border-b border-slate-50">
                        <div class="col-span-6">
                            <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $item->product ? $item->product->name : 'Produk dihapus' }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">@ Rp{{ number_format($item->price, 0, ',', '.') }}</p>
                        </div>
                        <div class="col-span-2 text-center text-sm text-slate-600 self-center">{{ $item->quantity }}</div>
                        <div class="col-span-4 text-right text-sm font-semibold text-slate-800 self-center">Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</div>
                    </div>
                    @if($item->discount > 0)
                    <div class="grid grid-cols-12 gap-2 py-1 text-xs text-green-600">
                        <div class="col-span-6 pl-3">{{ $item->discount_description ?? 'Diskon' }}</div>
                        <div class="col-span-2"></div>
                        <div class="col-span-4 text-right font-medium">-Rp{{ number_format($item->discount, 0, ',', '.') }}</div>
                    </div>
                    @endif
                    @endforeach
                </div>

                <!-- Summary -->
                <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between text-slate-500">
                            <span>Total Qty</span>
                            <span class="font-semibold text-slate-700">{{ $total_qty }} items</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal</span>
                            <span class="font-semibold text-slate-700">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>

                        @if($history->promo_discount > 0)
                        <div class="flex justify-between text-green-600 font-medium">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">sell</span>
                                Diskon Promo
                            </span>
                            <span>-Rp{{ number_format($history->promo_discount, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($history->tier_discount > 0)
                        <div class="flex justify-between text-green-600 font-medium">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">military_tech</span>
                                Diskon {{ ucfirst($history->customer->tier ?? 'Member') }}
                            </span>
                            <span>-Rp{{ number_format($history->tier_discount, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($history->voucher_discount > 0)
                        <div class="flex justify-between text-green-600 font-medium">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">redeem</span>
                                Voucher
                                @if($history->usedVoucher)
                                    ({{ $history->usedVoucher->code }})
                                @endif
                            </span>
                            <span>-Rp{{ number_format($history->voucher_discount, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($history->points_discount > 0)
                        <div class="flex justify-between text-amber-600 font-medium">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">stars</span>
                                Poin ({{ $history->points_redeemed }} pts)
                            </span>
                            <span>-Rp{{ number_format($history->points_discount, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($service > 0)
                        <div class="flex justify-between text-slate-500">
                            <span>Service ({{ \App\Models\StoreSetting::getVal('service_charge', $storeId, '0') }}%)</span>
                            <span class="font-semibold text-slate-700">Rp{{ number_format($service, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        <div class="border-t-2 border-slate-300 pt-2.5 mt-2.5 flex justify-between text-base font-extrabold">
                            <span class="text-slate-800">Total</span>
                            <span class="text-primary">Rp{{ number_format($history->total_amount, 0, ',', '.') }}</span>
                        </div>

                        <!-- Payment -->
                        <div class="border-t border-dashed border-slate-200 pt-3 mt-3 space-y-1">
                            @if($history->payment_method === 'split')
                                @foreach($history->payments as $pmt)
                                <div class="flex justify-between text-slate-600 text-sm">
                                    <span class="flex items-center gap-1.5">
                                        @if($pmt->method === 'cash')
                                        <span class="material-symbols-outlined text-sm text-green-600">payments</span>
                                        @elseif($pmt->method === 'qris')
                                        <span class="material-symbols-outlined text-sm text-blue-600">qr_code</span>
                                        @elseif($pmt->method === 'debit')
                                        <span class="material-symbols-outlined text-sm text-purple-600">credit_card</span>
                                        @elseif($pmt->method === 'credit')
                                        <span class="material-symbols-outlined text-sm text-orange-600">account_balance_wallet</span>
                                        @endif
                                        {{ ucfirst($pmt->method) }}
                                    </span>
                                    <span class="font-semibold">Rp{{ number_format($pmt->amount, 0, ',', '.') }}</span>
                                </div>
                                @endforeach
                            @else
                            <div class="flex justify-between text-slate-600 text-sm">
                                <span class="flex items-center gap-1.5">
                                    @if($history->payment_method === 'cash')
                                    <span class="material-symbols-outlined text-sm text-green-600">payments</span>
                                    @elseif($history->payment_method === 'qris')
                                    <span class="material-symbols-outlined text-sm text-blue-600">qr_code</span>
                                    @elseif($history->payment_method === 'debit')
                                    <span class="material-symbols-outlined text-sm text-purple-600">credit_card</span>
                                    @elseif($history->payment_method === 'credit')
                                    <span class="material-symbols-outlined text-sm text-orange-600">account_balance_wallet</span>
                                    @endif
                                    {{ ucfirst($history->payment_method) }}
                                </span>
                                <span class="font-semibold">Rp{{ number_format($history->amount_received, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between text-slate-400 text-xs">
                                <span>Kembalian</span>
                                <span class="font-medium">Rp{{ number_format($history->change_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Points Earned -->
                @if($history->customer && $pointsEarned > 0)
                <div class="px-5 sm:px-6 py-4 bg-amber-50 border-t border-amber-100 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-amber-500" style="font-variation-settings: 'FILL' 1;">stars</span>
                        <span class="text-sm font-extrabold text-amber-800">+{{ number_format($pointsEarned) }} Poin</span>
                    </div>
                    <p class="text-xs text-amber-600 mt-1">Total: {{ number_format($history->customer->total_points) }} pts</p>
                </div>
                @endif

                <!-- Earned Voucher -->
                @if($history->earnedVoucher)
                <div class="px-5 sm:px-6 py-4 border-t border-dashed border-slate-200 text-center">
                    <div class="bg-gradient-to-br from-primary/5 to-primary/10 rounded-xl p-4 border border-primary/20">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">redeem</span>
                            <span class="text-sm font-extrabold text-primary">Voucher Spesial!</span>
                        </div>
                        @php
                            $voucherMinRedeem = \App\Models\Promotion::where('type', 'voucher')
                                ->whereNull('code')
                                ->where('is_active', true)
                                ->first()?->min_purchase_amount ?? 75000;
                        @endphp
                        <p class="text-[11px] text-slate-500 mb-2">Min. belanja Rp{{ number_format($voucherMinRedeem, 0, ',', '.') }}</p>
                        <div class="bg-white px-5 py-2 rounded-lg border-2 border-dashed border-primary/40 inline-block">
                            <span class="font-mono font-extrabold text-lg text-primary tracking-wider">{{ $history->earnedVoucher->code }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Diskon Rp{{ number_format($history->earnedVoucher->discount_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
                @endif

                <!-- Footer -->
                <div class="px-5 sm:px-6 py-4 text-center border-t border-slate-100">
                    <p class="text-[11px] text-slate-400 font-medium">Terima kasih atas kunjungan Anda!</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="w-full mt-6 space-y-3 print:hidden">
                <div class="grid grid-cols-3 gap-3">
                    <a href="/payment/receipt/{{ $history->id }}/print" target="_blank"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                        <span class="material-symbols-outlined">receipt</span>
                        Struk
                    </a>
                    <a href="/payment/receipt/{{ $history->id }}/print-faktur" target="_blank"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                        <span class="material-symbols-outlined">print</span>
                        Faktur
                    </a>
                    @if($history->customer && $customerPhone)
                        <a href="https://wa.me/{{ $customerPhone }}?text={{ urlencode($waText) }}" target="_blank"
                            class="bg-green-500 hover:bg-green-600 text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                            <span class="material-symbols-outlined">chat</span>
                            Kirim WA
                        </a>
                    @else
                        <a href="https://wa.me/?text={{ urlencode($waText) }}" target="_blank"
                            class="bg-green-500 hover:bg-green-600 text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                            <span class="material-symbols-outlined">chat</span>
                            Bagikan
                        </a>
                    @endif
                </div>
                <a href="/"
                    class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-xl flex items-center justify-center gap-2 transition-all active:scale-[0.98] shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined">add</span>
                    Transaksi Baru
                </a>
            </div>
        </div>
    </main>

    <style>
        @media print {
            body { background: white !important; }
            * { text-shadow: none !important; box-shadow: none !important; }
            .print\:hidden { display: none !important; }
            .print\:bg-white { background: white !important; }
            .print\:rounded-none { border-radius: 0 !important; }
            .print\:pb-0 { padding-bottom: 0 !important; }
        }
        @page { margin: 0; }
    </style>

    <script>
        localStorage.removeItem('pos_cart');
        @if(\App\Models\StoreSetting::getVal('auto_print', $storeId, '0') == '1')
            window.onload = function () {
                setTimeout(function() { window.print(); }, 500);
            };
        @endif
    </script>
</x-layout>
