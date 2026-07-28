<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col w-full overflow-hidden">

        <!-- Top Bar -->
        <header
            class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
            <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
                <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">
                    {{ $title ?? 'Page' }}</h1>
            </div>
        </header>

        <!-- Main Content (Payment Canvas) -->
        <section class="flex-1 p-4 sm:p-6 lg:p-8 flex flex-col gap-4 sm:gap-6 lg:gap-8 bg-surface overflow-y-auto"
            x-data="paymentCheckout()">

            <form action="/payment/process" method="POST"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 sm:gap-6 lg:gap-8 flex-1"
                @submit="handleSubmit($event)">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice_id }}">
                <input type="hidden" name="payment_method" x-model="method">
                <input type="hidden" name="amount_received" :value="amountReceivedForSubmit">
                <input type="hidden" name="payment_data" :value="JSON.stringify(paymentDataForSubmit)">
                <input type="hidden" name="voucher_code" x-model="voucherCode" x-bind:disabled="!voucherApplied">
                <input type="hidden" name="points_to_redeem" x-model="pointsToRedeem">

                <!-- Left Column: Receipt Preview -->
                <div class="col-span-12 lg:col-span-4 flex flex-col gap-4 sm:gap-6">
                    <div
                        class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-4 sm:p-6 flex flex-col h-full overflow-hidden">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="material-symbols-outlined text-primary">receipt_long</span>
                            <h3 class="font-headline text-base sm:text-lg font-bold">Receipt Preview</h3>
                        </div>
                        <div class="border-t border-dashed border-outline-variant/50 mb-4"></div>
                        <div class="flex-1 overflow-y-auto no-scrollbar space-y-3 sm:space-y-4">
                            @foreach($cart as $item)
                                <div class="flex justify-between items-start text-xs sm:text-sm">
                                    <div class="min-w-0 flex-1 mr-2">
                                        <h4 class="font-bold text-on-surface truncate">{{ $item['name'] }}</h4>
                                        <p class="text-[10px] sm:text-xs text-on-surface-variant">Qty:
                                            {{ $item['quantity'] }} x
                                            Rp{{ number_format($item['price'], 0, ',', '.') }}</p>
                                    </div>
                                    <span
                                        class="font-bold text-on-surface text-xs sm:text-sm whitespace-nowrap">Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div
                            class="border-t border-dashed border-outline-variant/50 mt-4 pt-4 space-y-2 text-xs sm:text-sm">
                            <div class="flex justify-between">
                                <span class="text-on-surface-variant font-medium">Total Qty</span>
                                <span class="font-bold text-on-surface">{{ $total_qty }} items</span>
                            </div>

                            @if($promo_discount > 0)
                                <div class="flex justify-between text-green-600 font-bold">
                                    <span class="truncate mr-2">Promo Discount</span>
                                    <span
                                        class="whitespace-nowrap">-Rp{{ number_format($promo_discount, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            @if($tier_discount > 0)
                                <div class="flex justify-between text-indigo-600 font-bold">
                                    <span class="truncate mr-2">Member Discount ({{ ucfirst($customer->tier) }})</span>
                                    <span
                                        class="whitespace-nowrap">-Rp{{ number_format($tier_discount, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            {{-- Tax sudah termasuk dalam harga produk --}}
                            <div class="flex justify-between" x-show="tax > 0" x-cloak>
                                <span class="text-on-surface-variant font-medium">Tax</span>
                                <span class="font-bold text-on-surface">Rp{{ number_format($tax, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-primary font-bold" x-show="voucherApplied" x-cloak>
                                <span>Voucher Discount</span>
                                <span x-text="'-Rp' + new Intl.NumberFormat('id-ID').format(voucherDiscount)"></span>
                            </div>
                            <div class="flex justify-between text-yellow-600 font-bold" x-show="pointsApplied" x-cloak>
                                <span>Points Redemption</span>
                                <span x-text="'-Rp' + new Intl.NumberFormat('id-ID').format(pointsDiscount)"></span>
                            </div>
                            <div class="border-t border-outline-variant/30 mt-3 pt-3 flex justify-between">
                                <span class="font-bold font-headline text-sm sm:text-base text-on-surface">Total</span>
                                <span class="font-extrabold font-headline text-base sm:text-lg text-primary"
                                    x-text="formatCurrency(totalDue)"></span>
                            </div>
                            @if($customer)
                                <div class="flex justify-between text-yellow-600 text-[10px] sm:text-xs mt-2"
                                    x-show="totalDue > 0">
                                    <div class="flex flex-col">
                                        <span class="font-medium">Points to Earn:</span>
                                        @if($customer->tier_multiplier > 1)
                                            <span class="text-[8px] sm:text-[9px] opacity-70">({{ $customer->tier_multiplier }}x
                                                {{ ucfirst($customer->tier) }} Multiplier)</span>
                                        @endif
                                    </div>
                                    <span class="font-bold"
                                        x-text="Math.floor((totalDue / {{ $loyalty_points_per_rupiah }}) * {{ $customer->tier_multiplier ?? 1 }}).toLocaleString('id-ID') + ' pts'"></span>
                                </div>
                            @endif
                            <div
                                class="flex justify-between text-on-surface-variant mt-3 pt-3 border-t border-dashed border-outline-variant/50">
                                <span>Received</span>
                                <span class="font-bold" x-text="received ? formatCurrency(received) : 'Rp0'"></span>
                            </div>
                            <div class="flex justify-between text-on-surface-variant">
                                <span>Change</span>
                                <span class="font-bold" :class="change < 0 ? 'text-error' : ''"
                                    x-text="formatCurrency(Math.max(0, change))"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle Column: Summary & Method -->
                <div class="col-span-12 lg:col-span-4 flex flex-col gap-4 sm:gap-6">
                    <!-- Total Due Card -->
                    <div
                        class="bg-primary-container rounded-xl p-4 sm:p-6 text-white shadow-xl relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-10 font-bold text-[80px] sm:text-[100px]">
                            <span class="material-symbols-outlined text-[60px] sm:text-[80px]">payments</span>
                        </div>
                        <p class="text-blue-100 font-label tracking-wider uppercase text-[10px] sm:text-xs mb-2">Total
                            Due</p>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold font-headline"
                            x-text="formatCurrency(totalDue)"></h2>
                        <div
                            class="mt-3 sm:mt-4 flex items-center gap-2 bg-white/10 w-fit px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-xs">
                            <span class="material-symbols-outlined text-sm">shopping_basket</span>
                            <span>{{ count($cart) }} items in cart</span>
                        </div>
                    </div>
                    <!-- Payment Methods -->
                    <div
                        class="bg-surface-container-lowest rounded-xl p-4 sm:p-6 shadow-sm flex-1 border border-outline-variant/10">
                        <h3 class="text-sm sm:text-base font-bold mb-3 sm:mb-4 font-headline">Select Payment Method</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-2 gap-2 sm:gap-3">
                            <button type="button" @click="setMethod('cash')" :disabled="qrisActive"
                                :class="method === 'cash' ? 'border-primary bg-primary-fixed text-on-primary-fixed-variant' : 'border-outline-variant/30 bg-surface text-on-surface-variant hover:bg-surface-container'"
                                class="flex flex-col items-center justify-center gap-2 p-3 sm:p-4 rounded-xl border-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="material-symbols-outlined text-xl sm:text-2xl">payments</span>
                                <span class="font-bold text-[10px] sm:text-xs flex-1 text-center">Cash</span>
                            </button>
                            <button type="button" @click="setMethod('qris')" :disabled="qrisActive && method !== 'qris'"
                                :class="method === 'qris' ? 'border-primary bg-primary-fixed text-on-primary-fixed-variant' : 'border-outline-variant/30 bg-surface text-on-surface-variant hover:bg-surface-container'"
                                class="flex flex-col items-center justify-center gap-2 p-3 sm:p-4 rounded-xl border-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed group">
                                <span
                                    class="material-symbols-outlined text-xl sm:text-2xl group-hover:text-primary transition-colors">qr_code_2</span>
                                <span class="font-bold text-[10px] sm:text-xs flex-1 text-center">QRIS</span>
                            </button>
                            <button type="button" @click="setMethod('debit')" :disabled="qrisActive"
                                :class="method === 'debit' ? 'border-primary bg-primary-fixed text-on-primary-fixed-variant' : 'border-outline-variant/30 bg-surface text-on-surface-variant hover:bg-surface-container'"
                                class="flex flex-col items-center justify-center gap-2 p-3 sm:p-4 rounded-xl border-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed group">
                                <span
                                    class="material-symbols-outlined text-xl sm:text-2xl group-hover:text-primary transition-colors">credit_card</span>
                                <span class="font-bold text-[10px] sm:text-xs flex-1 text-center">Debit Card</span>
                            </button>
                            <button type="button" @click="setMethod('credit')" :disabled="qrisActive"
                                :class="method === 'credit' ? 'border-primary bg-primary-fixed text-on-primary-fixed-variant' : 'border-outline-variant/30 bg-surface text-on-surface-variant hover:bg-surface-container'"
                                class="flex flex-col items-center justify-center gap-2 p-3 sm:p-4 rounded-xl border-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed group">
                                <span
                                    class="material-symbols-outlined text-xl sm:text-2xl group-hover:text-primary transition-colors">account_balance_wallet</span>
                                <span class="font-bold text-[10px] sm:text-xs flex-1 text-center">Credit Card</span>
                            </button>
                        </div>
                    </div>

                    <!-- Voucher -->
                    <div
                        class="bg-surface-container-lowest rounded-xl p-4 sm:p-6 shadow-sm border border-outline-variant/10">
                        <h3 class="text-sm sm:text-base font-bold mb-3 font-headline">Apply Voucher</h3>

                        @php
                            $storeId = auth()->user()->store_id;
                            $availableVouchers = \App\Models\Promotion::where('type', 'voucher')
                                ->whereNotNull('code')
                                ->where('is_active', true)
                                ->where(function ($q) use ($storeId) {
                                    $q->whereNull('store_id')->orWhere('store_id', $storeId);
                                })
                                ->where(function ($q) {
                                    $q->whereNull('usage_limit')->orWhereColumn('usage_count', '<', 'usage_limit');
                                })
                                ->get();
                        @endphp

                        @if($availableVouchers->count() > 0)
                            <div class="mb-3 p-3 bg-primary/5 rounded-lg border border-primary/20">
                                <p class="text-xs font-bold text-primary mb-2">Available Vouchers:</p>
                                <div class="space-y-1">
                                    @foreach($availableVouchers as $av)
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="font-mono font-bold text-on-surface">{{ $av->code }}</span>
                                            <span class="text-on-surface-variant">
                                                @if($av->discount_nominal)
                                                    Rp{{ number_format($av->discount_nominal, 0, ',', '.') }}
                                                @elseif($av->discount_percentage)
                                                    {{ $av->discount_percentage }}%
                                                @endif
                                            </span>
                                            @if($av->min_purchase_amount)
                                                <span class="text-[10px] text-slate-400">Min:
                                                    Rp{{ number_format($av->min_purchase_amount, 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex gap-2">
                            <input type="text" x-model="voucherCode" :disabled="voucherApplied || qrisActive"
                                class="flex-1 w-full bg-surface border border-outline-variant/30 rounded-lg px-3 py-2 text-xs sm:text-sm font-body text-on-surface focus:ring-2 focus:ring-primary/20 outline-none uppercase min-w-0 disabled:opacity-50"
                                placeholder="Enter voucher code">
                            <button type="button" @click="checkVoucher()" x-show="!voucherApplied"
                                :disabled="qrisActive"
                                class="bg-primary text-white px-3 sm:px-4 py-2 rounded-lg font-bold text-xs sm:text-sm hover:opacity-90 active:scale-95 transition-all w-20 sm:w-24 disabled:opacity-50 disabled:cursor-not-allowed">
                                Apply
                            </button>
                            <button type="button" @click="removeVoucher()" x-cloak x-show="voucherApplied"
                                :disabled="qrisActive"
                                class="bg-error text-white px-3 sm:px-4 py-2 rounded-lg font-bold text-xs sm:text-sm hover:opacity-90 active:scale-95 transition-all w-20 sm:w-24 disabled:opacity-50 disabled:cursor-not-allowed">
                                Remove
                            </button>
                        </div>
                        <p class="text-[10px] sm:text-xs mt-2 font-medium" :class="voucherMessageClass"
                            x-text="voucherMessage" x-show="voucherMessage"></p>
                    </div>

                    <!-- Loyalty Points -->
                    @if($customer)
                        <div class="bg-yellow-50 rounded-xl p-4 sm:p-6 shadow-sm border border-yellow-200">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm sm:text-base font-bold font-headline text-yellow-800">Loyalty Points</h3>
                                <span
                                    class="px-2 py-1 bg-yellow-200 text-yellow-800 text-[10px] sm:text-xs font-bold rounded-full uppercase">
                                    {{ $customer->tier }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs sm:text-sm mb-3">
                                <span class="text-yellow-700">Your Points:</span>
                                <span class="font-bold text-yellow-800">{{ number_format($customer->available_points) }}
                                    pts</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number" x-model="pointsToRedeem" min="0"
                                    max="{{ $customer->available_points }}"
                                    class="flex-1 w-full bg-white border border-yellow-300 rounded-lg px-3 py-2 text-xs sm:text-sm font-body text-on-surface focus:ring-2 focus:ring-yellow-400/20 outline-none"
                                    placeholder="Points to redeem">
                                <button type="button" @click="applyPoints()"
                                    :disabled="pointsToRedeem < {{ $loyalty_min_redeem }}"
                                    class="bg-yellow-500 text-white px-3 sm:px-4 py-2 rounded-lg font-bold text-xs sm:text-sm hover:opacity-90 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                    Apply
                                </button>
                            </div>
                            <p class="text-[10px] sm:text-xs text-yellow-600 mt-2">
                                1 point = Rp{{ number_format($loyalty_point_value, 0, ',', '.') }} discount
                            </p>
                            <p x-show="pointsApplied" class="text-[10px] sm:text-xs text-green-600 font-bold mt-1"
                                x-text="'-Rp' + new Intl.NumberFormat('id-ID').format(pointsDiscount) + ' (' + pointsRedeemed + ' pts)'">
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Right Column: Action Area -->
                <div class="col-span-12 lg:col-span-4 flex flex-col gap-4 sm:gap-6">
                    <div
                        class="bg-surface-container-lowest rounded-xl p-4 sm:p-6 shadow-sm flex flex-col h-full border border-outline-variant/10 relative overflow-hidden">

                        <!-- ================= CASH UI ================= -->
                        <div x-show="method === 'cash'" class="flex flex-col h-full gap-3 sm:gap-4">
                            @if($errors->any())
                                <div class="p-3 bg-error/10 text-error text-sm rounded-lg">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div class="space-y-2">
                                <label class="block text-xs sm:text-sm font-semibold text-on-surface-variant">
                                    <span x-show="!secondaryMethod">Amount Received</span>
                                    <span x-show="secondaryMethod">Cash Amount</span>
                                </label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                        <span class="text-base sm:text-xl font-bold text-on-surface-variant">Rp</span>
                                    </div>
                                    <input x-model="receivedInput" readonly
                                        class="block w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 border border-outline-variant/20 bg-surface rounded-xl text-2xl sm:text-3xl font-headline font-bold focus:border-transparent focus:ring-2 focus:ring-primary/20 transition-all text-on-surface outline-none"
                                        placeholder="0" type="text" />
                                </div>
                            </div>

                            <!-- Custom Numpad Keypad -->
                            <div class="grid grid-cols-3 gap-1.5 sm:gap-2 mt-1 sm:mt-2">
                                <button type="button" @click="keypadPress(1)"
                                    class="bg-surface hover:bg-surface-container py-3 sm:py-4 rounded-xl font-headline font-bold text-lg sm:text-xl text-on-surface transition-colors border border-outline-variant/20">1</button>
                                <button type="button" @click="keypadPress(2)"
                                    class="bg-surface hover:bg-surface-container py-3 sm:py-4 rounded-xl font-headline font-bold text-lg sm:text-xl text-on-surface transition-colors border border-outline-variant/20">2</button>
                                <button type="button" @click="keypadPress(3)"
                                    class="bg-surface hover:bg-surface-container py-3 sm:py-4 rounded-xl font-headline font-bold text-lg sm:text-xl text-on-surface transition-colors border border-outline-variant/20">3</button>

                                <button type="button" @click="keypadPress(4)"
                                    class="bg-surface hover:bg-surface-container py-3 sm:py-4 rounded-xl font-headline font-bold text-lg sm:text-xl text-on-surface transition-colors border border-outline-variant/20">4</button>
                                <button type="button" @click="keypadPress(5)"
                                    class="bg-surface hover:bg-surface-container py-3 sm:py-4 rounded-xl font-headline font-bold text-lg sm:text-xl text-on-surface transition-colors border border-outline-variant/20">5</button>
                                <button type="button" @click="keypadPress(6)"
                                    class="bg-surface hover:bg-surface-container py-3 sm:py-4 rounded-xl font-headline font-bold text-lg sm:text-xl text-on-surface transition-colors border border-outline-variant/20">6</button>

                                <button type="button" @click="keypadPress(7)"
                                    class="bg-surface hover:bg-surface-container py-3 sm:py-4 rounded-xl font-headline font-bold text-lg sm:text-xl text-on-surface transition-colors border border-outline-variant/20">7</button>
                                <button type="button" @click="keypadPress(8)"
                                    class="bg-surface hover:bg-surface-container py-3 sm:py-4 rounded-xl font-headline font-bold text-lg sm:text-xl text-on-surface transition-colors border border-outline-variant/20">8</button>
                                <button type="button" @click="keypadPress(9)"
                                    class="bg-surface hover:bg-surface-container py-3 sm:py-4 rounded-xl font-headline font-bold text-lg sm:text-xl text-on-surface transition-colors border border-outline-variant/20">9</button>

                                <button type="button" @click="keypadPress('000')"
                                    class="bg-surface hover:bg-surface-container py-3 sm:py-4 rounded-xl font-headline font-bold text-lg sm:text-xl text-on-surface transition-colors border border-outline-variant/20">000</button>
                                <button type="button" @click="keypadPress(0)"
                                    class="bg-surface hover:bg-surface-container py-3 sm:py-4 rounded-xl font-headline font-bold text-lg sm:text-xl text-on-surface transition-colors border border-outline-variant/20">0</button>
                                <button type="button" @click="keypadClear()"
                                    class="bg-error/10 hover:bg-error/20 text-error py-3 sm:py-4 rounded-xl font-headline font-bold text-lg sm:text-xl transition-colors border border-error/20 flex items-center justify-center">
                                    <span class="material-symbols-outlined">backspace</span>
                                </button>
                            </div>

                            <!-- Cash Suggestions (only when no secondary method) -->
                            <div x-show="!secondaryMethod" class="grid grid-cols-4 gap-1.5 sm:gap-2 mt-1 sm:mt-2">
                                <template x-for="suggestion in cashSuggestions" :key="suggestion.amount">
                                    <button type="button" @click="setAmount(suggestion.amount)"
                                        class="bg-surface hover:bg-primary-fixed py-2 sm:py-2.5 rounded-lg font-headline font-bold text-[10px] sm:text-xs text-on-surface transition-colors border border-outline-variant/30 hover:border-primary/20"
                                        x-text="suggestion.label"></button>
                                </template>
                            </div>

                            <div x-show="!secondaryMethod" class="grid grid-cols-2 gap-1.5 sm:gap-2 mt-1 sm:mt-2">
                                <button type="button" @click="setAmount(totalDue)"
                                    class="col-span-2 bg-primary/10 text-primary py-2 sm:py-2.5 rounded-lg font-headline font-extrabold text-xs sm:text-sm hover:bg-primary/20 transition-colors border border-primary/20">
                                    Exact Amount
                                </button>
                            </div>

                            <!-- ================= SPLIT: remaining payment ================= -->
                            <div x-show="hasRemaining && !secondaryMethod"
                                class="mt-2 p-3 bg-primary/5 rounded-xl border border-primary/20">
                                <p class="text-xs font-bold text-on-surface mb-2">
                                    Sisa: <span class="text-primary" x-text="formatCurrency(remainingDue)"></span>
                                </p>
                                <p class="text-[10px] text-on-surface-variant mb-2">Bayar sisa dengan:</p>
                                <div class="flex gap-2">
                                    <button type="button" @click="selectSecondary('debit')"
                                        class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-white border border-primary/30 rounded-lg text-[10px] font-bold text-primary hover:bg-primary/10 transition-colors">
                                        <span class="material-symbols-outlined text-sm">credit_card</span>
                                        Debit
                                    </button>
                                    <button type="button" @click="selectSecondary('credit')"
                                        class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-white border border-primary/30 rounded-lg text-[10px] font-bold text-primary hover:bg-primary/10 transition-colors">
                                        <span class="material-symbols-outlined text-sm">account_balance_wallet</span>
                                        Credit
                                    </button>
                                    <button type="button" @click="selectSecondary('qris')"
                                        class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-white border border-primary/30 rounded-lg text-[10px] font-bold text-primary hover:bg-primary/10 transition-colors">
                                        <span class="material-symbols-outlined text-sm">qr_code</span>
                                        QRIS
                                    </button>
                                </div>
                            </div>

                            <!-- ================= SPLIT: secondary method card form ================= -->
                            <div x-show="secondaryMethod === 'debit' || secondaryMethod === 'credit'"
                                class="mt-2 p-3 bg-surface-container rounded-xl border border-outline-variant/20">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-on-surface" x-text="secondaryMethod === 'debit' ? 'Debit Card' : 'Credit Card'"></span>
                                    <button type="button" @click="clearSecondary()"
                                        class="text-[10px] text-error/70 hover:text-error font-bold">Batalkan</button>
                                </div>
                                <div class="space-y-2">
                                    <input type="text" x-model="secondaryCardNumber" maxlength="20"
                                        class="w-full bg-surface border border-outline-variant/30 rounded-lg px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-primary/20 outline-none"
                                        placeholder="Nomor Kartu">
                                    <div class="grid grid-cols-2 gap-2">
                                        <select x-model="secondaryBank"
                                            class="bg-surface border border-outline-variant/30 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-primary/20 outline-none">
                                            <option value="">Bank</option>
                                            <option value="BCA">BCA</option>
                                            <option value="Mandiri">Mandiri</option>
                                            <option value="BNI">BNI</option>
                                            <option value="BRI">BRI</option>
                                            <option value="CIMB Niaga">CIMB Niaga</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                        <input type="text" x-model="secondaryApprovalCode" maxlength="50"
                                            class="bg-surface border border-outline-variant/30 rounded-lg px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-primary/20 outline-none"
                                            placeholder="Kode Approval">
                                    </div>
                                </div>
                            </div>

                            <!-- ================= SPLIT: QRIS secondary ================= -->
                            <div x-show="secondaryMethod === 'qris'"
                                class="mt-2 bg-surface-container rounded-xl border border-outline-variant/20 overflow-hidden">
                                <div class="flex items-center justify-between p-3 border-b border-outline-variant/10">
                                    <span class="text-xs font-bold text-on-surface">QRIS</span>
                                    <button type="button" @click="clearSecondary()"
                                        class="text-[10px] text-error/70 hover:text-error font-bold">Batalkan</button>
                                </div>

                                <!-- Not generated -->
                                <div x-show="!qrisSplitActive && !qrisSplitLoading && !qrisSplitSuccess"
                                    class="flex flex-col items-center gap-3 py-4 px-3">
                                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                                        <span class="material-symbols-outlined text-primary text-2xl">qr_code_scanner</span>
                                    </div>
                                    <p class="text-xs text-on-surface-variant text-center">Sisa: <span class="font-bold text-primary" x-text="formatCurrency(remainingDue)"></span></p>
                                    <button type="button" @click="generateQrisSplit()"
                                        class="w-full bg-primary text-white font-bold py-2 rounded-lg text-xs hover:bg-primary-container active:scale-95 transition-all flex items-center justify-center gap-1">
                                        <span class="material-symbols-outlined text-sm">qr_code_2</span>
                                        Generate QR
                                    </button>
                                </div>

                                <!-- Loading -->
                                <div x-show="qrisSplitLoading" class="flex flex-col items-center gap-3 py-6">
                                    <span class="material-symbols-outlined animate-spin text-primary text-3xl">autorenew</span>
                                    <p class="text-xs font-semibold text-on-surface-variant animate-pulse">Menghubungkan Midtrans...</p>
                                </div>

                                <!-- Active QR -->
                                <div x-show="qrisSplitActive && !qrisSplitSuccess" class="flex flex-col items-center py-4 px-3">
                                    <div class="bg-white p-2 rounded-xl mb-3">
                                        <template x-if="qrisSplitUrl">
                                            <img :src="qrisSplitUrl" alt="QRIS" class="w-32 h-32 object-contain">
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-2 bg-primary/10 px-3 py-1 rounded-full text-[10px] font-bold text-primary animate-pulse mb-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                        Menunggu pembayaran...
                                    </div>
                                    <p class="font-extrabold text-xl text-on-surface font-mono tracking-widest" x-text="formatTime(qrisSplitTimeLeft)"></p>
                                </div>

                                <!-- Success -->
                                <div x-show="qrisSplitSuccess" class="flex flex-col items-center py-4 gap-2">
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                        <span class="material-symbols-outlined text-green-600 text-3xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                    </div>
                                    <p class="text-xs font-bold text-green-600">QRIS Berhasil!</p>
                                </div>
                            </div>

                            <!-- Real-time Calculation Display -->
                            <div class="mt-auto pt-3 sm:pt-4 border-t border-surface-container flex flex-col gap-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs sm:text-sm text-on-surface-variant font-medium">
                                        <span x-show="!secondaryMethod">Change to Give</span>
                                        <span x-show="secondaryMethod">Total Dibayar</span>
                                    </p>
                                    <p class="text-xl sm:text-2xl font-headline font-extrabold"
                                        :class="secondaryMethod ? 'text-green-600' : (change < 0 ? 'text-error' : 'text-primary')"
                                        x-text="secondaryMethod ? formatCurrency(totalPaidAmount) : formatCurrency(Math.max(0, change))"></p>
                                </div>

                                <button type="submit"
                                    :disabled="!cashReceived || (secondaryMethod === 'debit' || secondaryMethod === 'credit') && !secondaryCardNumber || secondaryMethod === 'qris' && !qrisSplitSuccess"
                                    class="w-full bg-gradient-to-r from-primary to-primary-container text-white font-bold py-3 sm:py-4 rounded-xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 tracking-wide disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base">
                                    <span x-text="secondaryMethod ? 'Bayar Rp' + formatShort(cashReceived) + ' + ' + formatShort(remainingDue) : 'Complete Payment'"></span>
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                </button>
                            </div>
                        </div>

                        <!-- ================= DEBIT / CREDIT UI (no numpad) ================= -->
                        <div x-show="method === 'debit' || method === 'credit'" class="flex flex-col h-full gap-4">
                            @if($errors->any())
                                <div class="p-3 bg-error/10 text-error text-sm rounded-lg">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div class="flex flex-col items-center justify-center py-6 gap-2">
                                <span class="material-symbols-outlined text-5xl text-primary">credit_card</span>
                                <h3 class="text-lg font-bold text-on-surface" x-text="method === 'debit' ? 'Debit Card' : 'Credit Card'"></h3>
                                <p class="text-3xl font-extrabold text-primary font-headline" x-text="formatCurrency(totalDue)"></p>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-on-surface-variant mb-1 uppercase tracking-wider">Nomor Kartu</label>
                                    <input type="text" x-model="cardNumber" maxlength="20"
                                        class="w-full bg-surface border border-outline-variant/30 rounded-lg px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-primary/20 outline-none"
                                        placeholder="**** **** **** 1234">
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-on-surface-variant mb-1 uppercase tracking-wider">Bank</label>
                                        <select x-model="bankName"
                                            class="w-full bg-surface border border-outline-variant/30 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 outline-none">
                                            <option value="">Pilih Bank</option>
                                            <option value="BCA">BCA</option>
                                            <option value="Mandiri">Mandiri</option>
                                            <option value="BNI">BNI</option>
                                            <option value="BRI">BRI</option>
                                            <option value="CIMB Niaga">CIMB Niaga</option>
                                            <option value="Danamon">Danamon</option>
                                            <option value="Maybank">Maybank</option>
                                            <option value="Permata">Permata</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-on-surface-variant mb-1 uppercase tracking-wider">Kode Approval</label>
                                        <input type="text" x-model="approvalCode" maxlength="50"
                                            class="w-full bg-surface border border-outline-variant/30 rounded-lg px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-primary/20 outline-none"
                                            placeholder="Dari EDC">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto pt-3 border-t border-surface-container">
                                <button type="submit" :disabled="!cardNumber"
                                    class="w-full bg-gradient-to-r from-primary to-primary-container text-white font-bold py-3 sm:py-4 rounded-xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 tracking-wide disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base">
                                    <span>Bayar dengan Kartu</span>
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                </button>
                            </div>
                        </div>

                        <!-- ================= QRIS / E-WALLET UI ================= -->
                        <div x-cloak x-show="method === 'qris'"
                            class="flex flex-col h-full items-center justify-center text-center">

                            <div x-show="!qrisActive && !qrisLoading && !qrisSuccess"
                                class="flex flex-col items-center gap-3 sm:gap-4 py-6 sm:py-8">
                                <div
                                    class="w-16 h-16 sm:w-24 sm:h-24 bg-primary/10 rounded-full flex items-center justify-center mb-2">
                                    <span
                                        class="material-symbols-outlined text-primary text-4xl sm:text-5xl">qr_code_scanner</span>
                                </div>
                                <h3 class="font-headline font-bold text-lg sm:text-xl text-on-surface">Pay with QRIS
                                </h3>
                                <p class="text-xs sm:text-sm text-on-surface-variant max-w-[250px]">Generate kode QR
                                    untuk
                                    pelanggan scan dengan e-Wallet atau aplikasi bank.</p>
                                <button type="button" @click="generateQris()"
                                    class="mt-2 sm:mt-4 w-full bg-primary text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg shadow-md shadow-primary/20 hover:bg-primary-container active:scale-95 transition-all flex items-center justify-center gap-2 text-sm sm:text-base">
                                    <span class="material-symbols-outlined text-sm">qr_code_2</span>
                                    Generate QR Code
                                </button>
                            </div>

                            <div x-show="qrisLoading" class="flex flex-col items-center gap-4 py-12">
                                <span
                                    class="material-symbols-outlined animate-spin text-primary text-4xl">autorenew</span>
                                <p class="text-sm font-semibold text-on-surface-variant animate-pulse">Connecting to
                                    Midtrans...</p>
                            </div>

                            <div x-show="qrisActive && !qrisSuccess" class="flex flex-col w-full h-full">
                                <div class="flex-1 flex flex-col items-center justify-center py-4">
                                    <div
                                        class="bg-primary/10 text-primary px-3 sm:px-4 py-1 sm:py-1.5 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-widest mb-4 sm:mb-6 flex items-center gap-2 animate-pulse">
                                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-primary"></span>
                                        Menunggu pembayaran...
                                    </div>
                                    <div
                                        class="bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-outline-variant/20 mb-4 sm:mb-6 relative group">
                                        <template x-if="qrisUrl">
                                            <img :src="qrisUrl" alt="QRIS Code"
                                                class="w-36 h-36 sm:w-48 sm:h-48 object-contain">
                                        </template>
                                        <template x-if="!qrisUrl">
                                            <div
                                                class="w-36 h-36 sm:w-48 sm:h-48 bg-slate-100 flex items-center justify-center relative overflow-hidden rounded-xl border-2 border-dashed border-slate-300">
                                                <span
                                                    class="material-symbols-outlined text-slate-400 text-5xl sm:text-6xl">qr_code_2</span>
                                                <div
                                                    class="absolute inset-0 bg-gradient-to-b from-transparent via-primary/5 to-transparent w-full h-full animate-[scan_2s_ease-in-out_infinite]">
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <p class="text-xs sm:text-sm font-medium text-on-surface-variant mb-1">Habis dalam
                                    </p>
                                    <p class="font-headline font-extrabold text-2xl sm:text-3xl text-on-surface mb-6 sm:mb-8 tracking-widest font-mono"
                                        x-text="formatTime(qrisTimeLeft)"></p>
                                </div>
                                <div
                                    class="mt-auto grid grid-cols-2 gap-2 sm:gap-3 pt-3 sm:pt-4 border-t border-outline-variant/10">
                                    <button type="button" @click="cancelQris()"
                                        class="bg-surface hover:bg-error/10 text-error font-bold py-2.5 sm:py-3 rounded-lg border border-error/20 transition-colors text-xs sm:text-sm">
                                        Batal
                                    </button>
                                    <button type="button" @click="generateQris()"
                                        class="bg-surface hover:bg-primary-fixed text-primary font-bold py-2.5 sm:py-3 rounded-lg border border-primary/20 transition-colors flex items-center justify-center gap-2 text-xs sm:text-sm">
                                        <span class="material-symbols-outlined text-sm">refresh</span> Segarkan QR
                                    </button>
                                </div>
                            </div>

                            <div x-show="qrisSuccess"
                                class="flex flex-col items-center justify-center h-full w-full py-8 sm:py-12 gap-3 sm:gap-4">
                                <div
                                    class="w-16 h-16 sm:w-24 sm:h-24 bg-green-100 rounded-full flex items-center justify-center animate-[bounce_0.5s_ease-out]">
                                    <span class="material-symbols-outlined text-green-600 text-5xl sm:text-6xl"
                                        style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                </div>
                                <h3 class="font-headline font-extrabold text-xl sm:text-2xl text-on-surface mt-2">
                                    Pembayaran Berhasil!</h3>
                                <p class="text-on-surface-variant text-xs sm:text-sm">Mengalihkan ke struk...</p>
                                <button type="submit" x-ref="qrisSubmitBtn" class="hidden"></button>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <style>
        @keyframes scan {
            0% {
                transform: translateY(-100%);
            }

            100% {
                transform: translateY(100%);
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('paymentCheckout', () => ({
                totalDue: {{ $total }},
                receivedInput: '',
                method: 'cash',
                voucherCode: '',
                voucherApplied: false,
                voucherDiscount: 0,
                voucherMessage: '',
                voucherMessageClass: '',

                // Points variables
                pointsToRedeem: 0,
                pointsApplied: false,
                pointsDiscount: 0,
                pointsRedeemed: 0,
                loyaltyPointValue: {{ $loyalty_point_value }},
                loyaltyMinRedeem: {{ $loyalty_min_redeem }},

                // QRIS variables
                qrisActive: false,
                qrisLoading: false,
                qrisSuccess: false,
                qrisUrl: null,
                qrisOrderId: null,
                qrisTimeLeft: 300,
                qrisTimer: null,
                qrisPollTimer: null,

                // Card details (single method debit/credit)
                cardNumber: '',
                approvalCode: '',
                bankName: '',

                // Split: secondary payment method
                secondaryMethod: null, // null | 'debit' | 'credit' | 'qris'
                secondaryCardNumber: '',
                secondaryApprovalCode: '',
                secondaryBank: '',

                // Split QRIS state
                qrisSplitActive: false,
                qrisSplitLoading: false,
                qrisSplitSuccess: false,
                qrisSplitUrl: null,
                qrisSplitOrderId: null,
                qrisSplitTimeLeft: 300,
                qrisSplitTimer: null,
                qrisSplitPollTimer: null,

                get paymentDataForSubmit() {
                    if (this.method === 'cash' && this.secondaryMethod) {
                        const data = [
                            { method: 'cash', amount: this.cashReceived },
                            {
                                method: this.secondaryMethod,
                                amount: this.remainingDue,
                            },
                        ];
                        if (this.secondaryMethod === 'debit' || this.secondaryMethod === 'credit') {
                            data[1].card_number = this.secondaryCardNumber || null;
                            data[1].approval_code = this.secondaryApprovalCode || null;
                            data[1].bank_name = this.secondaryBank || null;
                        }
                        return data;
                    }
                    return [{
                        method: this.method,
                        amount: (this.method === 'debit' || this.method === 'credit') ? this.totalDue : (parseInt(this.received) || 0),
                        card_number: (this.method === 'debit' || this.method === 'credit') ? (this.cardNumber || null) : null,
                        approval_code: (this.method === 'debit' || this.method === 'credit') ? (this.approvalCode || null) : null,
                        bank_name: (this.method === 'debit' || this.method === 'credit') ? (this.bankName || null) : null,
                    }];
                },

                get amountReceivedForSubmit() {
                    if (this.method === 'cash' && this.secondaryMethod) {
                        return this.cashReceived + this.remainingDue;
                    }
                    if (this.method === 'debit' || this.method === 'credit') {
                        return this.totalDue;
                    }
                    return parseInt(this.received) || 0;
                },

                get cashReceived() {
                    return parseInt(this.received) || 0;
                },

                get change() {
                    return this.cashReceived - this.totalDue;
                },

                get remainingDue() {
                    if (!this.cashReceived) return this.totalDue;
                    return Math.max(0, this.totalDue - this.cashReceived);
                },

                get hasRemaining() {
                    if (this.method !== 'cash') return false;
                    return this.cashReceived > 0 && this.cashReceived < this.totalDue;
                },

                get totalPaidAmount() {
                    if (this.secondaryMethod) {
                        return this.cashReceived + this.remainingDue;
                    }
                    return this.cashReceived;
                },

                get cashSuggestions() {
                    const total = Math.ceil(this.totalDue);
                    if (total <= 0) return [];

                    const suggestions = [];
                    const denominations = [1000, 2000, 5000, 10000, 20000, 50000, 100000];
                    const maxChange = 100000;
                    const maxAmount = total + maxChange;

                    suggestions.push({ amount: total, label: this.formatShort(total) });

                    for (let i = denominations.length - 1; i >= 0; i--) {
                        const rounded = Math.ceil(total / denominations[i]) * denominations[i];
                        if (rounded > total && rounded <= maxAmount) {
                            suggestions.push({ amount: rounded, label: this.formatShort(rounded) });
                            break;
                        }
                    }

                    const commonBills = [50000, 100000, 200000, 500000, 1000000];
                    for (const bill of commonBills) {
                        if (bill > total && bill <= maxAmount && suggestions.length < 4) {
                            const alreadyAdded = suggestions.some(s => s.amount === bill);
                            if (!alreadyAdded) {
                                suggestions.push({ amount: bill, label: this.formatShort(bill) });
                            }
                        }
                        if (suggestions.length >= 4) break;
                    }

                    if (suggestions.length < 4) {
                        for (let i = denominations.length - 1; i >= 0 && suggestions.length < 4; i--) {
                            const step = denominations[i];
                            let candidate = Math.ceil(total / step) * step;
                            while (candidate <= maxAmount && suggestions.length < 4) {
                                if (candidate > total && !suggestions.some(s => s.amount === candidate)) {
                                    suggestions.push({ amount: candidate, label: this.formatShort(candidate) });
                                }
                                candidate += step;
                            }
                        }
                    }

                    return suggestions.slice(0, 4);
                },

                // --- Method selection ---
                setMethod(m) {
                    if (this.qrisActive && m !== 'qris') return;
                    this.method = m;
                    this.clearSecondary();
                    if (m === 'qris') {
                        this.received = this.totalDue;
                        this.receivedInput = new Intl.NumberFormat('id-ID').format(this.totalDue);
                    } else if (m === 'debit' || m === 'credit') {
                        this.received = this.totalDue;
                        this.receivedInput = new Intl.NumberFormat('id-ID').format(this.totalDue);
                    } else {
                        this.received = '';
                        this.receivedInput = '';
                    }
                },

                keypadPress(num) {
                    let current = this.received.toString() || '';
                    if (num === '000') {
                        if (current.length > 0) current += '000';
                    } else {
                        current += num.toString();
                    }
                    this.received = parseInt(current) || 0;
                    this.receivedInput = new Intl.NumberFormat('id-ID').format(this.received);
                },

                keypadClear() {
                    let current = this.received.toString();
                    if (current.length > 1) {
                        current = current.slice(0, -1);
                        this.received = parseInt(current) || 0;
                    } else {
                        this.received = '';
                    }
                    this.receivedInput = this.received ? new Intl.NumberFormat('id-ID').format(this.received) : '';
                },

                setAmount(amount) {
                    this.received = Math.floor(amount);
                    this.receivedInput = new Intl.NumberFormat('id-ID').format(this.received);
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('{{ $currency_code }}', { style: 'currency', currency: 'IDR' }).format(amount);
                },

                formatShort(amount) {
                    if (amount >= 1000000) {
                        return (amount / 1000000).toFixed(amount % 1000000 === 0 ? 0 : 1) + 'jt';
                    }
                    if (amount >= 1000) {
                        return (amount / 1000).toFixed(amount % 1000 === 0 ? 0 : 1) + 'rb';
                    }
                    return amount.toString();
                },

                // --- Split: secondary method ---
                selectSecondary(method) {
                    this.secondaryMethod = method;
                    this.secondaryCardNumber = '';
                    this.secondaryApprovalCode = '';
                    this.secondaryBank = '';
                    this.cancelQrisSplit();
                },

                clearSecondary() {
                    this.secondaryMethod = null;
                    this.secondaryCardNumber = '';
                    this.secondaryApprovalCode = '';
                    this.secondaryBank = '';
                    this.cancelQrisSplit();
                },

                // --- Split QRIS ---
                async generateQrisSplit() {
                    this.qrisSplitLoading = true;
                    this.qrisSplitActive = false;
                    this.qrisSplitSuccess = false;
                    this.clearQrisSplitTimers();

                    try {
                        let res = await fetch('/payment/qris/generate', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({
                                invoice_id: '{{ $invoice_id }}',
                                amount: this.remainingDue,
                            })
                        });
                        let data = await res.json();

                        this.qrisSplitLoading = false;

                        if (data.status === 'success') {
                            this.qrisSplitUrl = data.qr_url;
                            this.qrisSplitOrderId = data.order_id;
                            this.qrisSplitActive = true;
                            this.startQrisSplitTimers();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal Generate QR', text: data.message, confirmButtonColor: '#3085d6' });
                        }
                    } catch (e) {
                        this.qrisSplitLoading = false;
                        Swal.fire({ icon: 'error', title: 'Koneksi Gagal', text: 'Gagal terhubung ke server.', confirmButtonColor: '#3085d6' });
                    }
                },

                startQrisSplitTimers() {
                    this.qrisSplitTimeLeft = 300;
                    this.qrisSplitTimer = setInterval(() => {
                        this.qrisSplitTimeLeft--;
                        if (this.qrisSplitTimeLeft <= 0) {
                            this.cancelQrisSplit();
                            Swal.fire({ icon: 'warning', title: 'QRIS Kadaluarsa', text: 'Waktu habis. Silakan generate ulang.', confirmButtonColor: '#3085d6' });
                        }
                    }, 1000);

                    this.qrisSplitPollTimer = setInterval(() => {
                        this.checkQrisSplitStatus();
                    }, 3000);
                },

                async checkQrisSplitStatus() {
                    if (!this.qrisSplitOrderId) return;
                    try {
                        let res = await fetch('/payment/qris/status/' + this.qrisSplitOrderId);
                        let data = await res.json();
                        if (data.transaction_status === 'settlement' || data.transaction_status === 'capture') {
                            this.qrisSplitSuccess = true;
                            this.clearQrisSplitTimers();
                        } else if (data.transaction_status === 'expire' || data.transaction_status === 'cancel' || data.transaction_status === 'deny') {
                            this.cancelQrisSplit();
                            Swal.fire({ icon: 'error', title: 'QRIS Gagal', text: 'Transaksi dibatalkan/kedaluwarsa.', confirmButtonColor: '#3085d6' });
                        }
                    } catch (e) {}
                },

                cancelQrisSplit() {
                    this.clearQrisSplitTimers();
                    this.qrisSplitActive = false;
                    this.qrisSplitLoading = false;
                    this.qrisSplitSuccess = false;
                    this.qrisSplitUrl = null;
                    this.qrisSplitOrderId = null;
                },

                clearQrisSplitTimers() {
                    if (this.qrisSplitTimer) clearInterval(this.qrisSplitTimer);
                    if (this.qrisSplitPollTimer) clearInterval(this.qrisSplitPollTimer);
                },

                // --- Voucher ---
                async checkVoucher() {
                    if (!this.voucherCode) return;
                    try {
                        let res = await fetch('/payment/voucher/check', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ code: this.voucherCode })
                        });
                        let data = await res.json();
                        if (data.valid) {
                            this.voucherApplied = true;
                            this.voucherDiscount = data.discount;
                            this.voucherMessage = data.message;
                            this.voucherMessageClass = 'text-green-600 dark:text-green-400';
                            this.totalDue = Math.max(0, {{ $total }} - data.discount - this.pointsDiscount);
                            if (this.method === 'qris') { this.received = this.totalDue; this.receivedInput = new Intl.NumberFormat('id-ID').format(this.totalDue); }
                        } else {
                            this.voucherMessage = data.message;
                            this.voucherMessageClass = 'text-error';
                        }
                    } catch (e) {
                        this.voucherMessage = 'Error checking voucher.';
                        this.voucherMessageClass = 'text-error';
                    }
                },

                removeVoucher() {
                    this.voucherApplied = false;
                    this.voucherDiscount = 0;
                    this.voucherMessage = '';
                    this.totalDue = Math.max(0, {{ $total }} - this.pointsDiscount);
                    if (this.method === 'qris') { this.received = this.totalDue; this.receivedInput = new Intl.NumberFormat('id-ID').format(this.totalDue); }
                },

                // --- Points ---
                applyPoints() {
                    if (this.pointsToRedeem < this.loyaltyMinRedeem) return;
                    this.pointsRedeemed = Math.min(this.pointsToRedeem, {{ $customer ? $customer->available_points : 0 }});
                    this.pointsDiscount = this.pointsRedeemed * this.loyaltyPointValue;
                    this.pointsApplied = true;
                    this.totalDue = Math.max(0, {{ $total }} - this.voucherDiscount - this.pointsDiscount);
                    if (this.method === 'qris') { this.received = this.totalDue; this.receivedInput = new Intl.NumberFormat('id-ID').format(this.totalDue); }
                },

                removePoints() {
                    this.pointsToRedeem = 0;
                    this.pointsApplied = false;
                    this.pointsDiscount = 0;
                    this.pointsRedeemed = 0;
                    this.totalDue = Math.max(0, {{ $total }} - this.voucherDiscount);
                    if (this.method === 'qris') { this.received = this.totalDue; this.receivedInput = new Intl.NumberFormat('id-ID').format(this.totalDue); }
                },

                // --- QRIS Logic (Core API) ---
                async generateQris() {
                    this.qrisLoading = true;
                    this.qrisActive = false;
                    this.qrisSuccess = false;
                    this.clearTimers();

                    try {
                        let res = await fetch('/payment/qris/generate', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({
                                invoice_id: '{{ $invoice_id }}',
                                voucher_code: this.voucherApplied ? this.voucherCode : null
                            })
                        });
                        let data = await res.json();

                        this.qrisLoading = false;

                        if (data.status === 'success') {
                            this.qrisUrl = data.qr_url;
                            this.qrisOrderId = data.order_id;
                            this.qrisActive = true;
                            this.received = this.totalDue;
                            this.startTimers();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal Generate QR', text: data.message, confirmButtonColor: '#3085d6' });
                        }
                    } catch (e) {
                        this.qrisLoading = false;
                        Swal.fire({ icon: 'error', title: 'Koneksi Gagal', text: 'Koneksi API Error', confirmButtonColor: '#3085d6' });
                    }
                },

                startTimers() {
                    this.qrisTimeLeft = 300;
                    this.qrisTimer = setInterval(() => {
                        this.qrisTimeLeft--;
                        if (this.qrisTimeLeft <= 0) {
                            this.cancelQris();
                            Swal.fire({ icon: 'warning', title: 'QRIS Kadaluarsa', text: 'QRIS Waktu Habis. Silakan generate ulang.', confirmButtonColor: '#3085d6' });
                        }
                    }, 1000);

                    this.qrisPollTimer = setInterval(async () => {
                        await this.checkStatus();
                    }, 3000);
                },

                async checkStatus() {
                    if (!this.qrisOrderId) return;
                    try {
                        let res = await fetch('/payment/qris/status/' + this.qrisOrderId);
                        let data = await res.json();
                        if (data.transaction_status === 'settlement' || data.transaction_status === 'capture') {
                            this.processQrisSuccess();
                        } else if (data.transaction_status === 'expire' || data.transaction_status === 'cancel' || data.transaction_status === 'deny') {
                            this.cancelQris();
                            Swal.fire({ icon: 'error', title: 'Transaksi Gagal', text: 'Transaksi QRIS Kedaluwarsa/Batal', confirmButtonColor: '#3085d6' });
                        }
                    } catch (e) {
                        // Ignore network errors in polling
                    }
                },

                processQrisSuccess() {
                    this.clearTimers();
                    this.qrisSuccess = true;
                    setTimeout(() => {
                        this.$refs.qrisSubmitBtn.click();
                    }, 2000);
                },

                cancelQris() {
                    this.clearTimers();
                    this.qrisActive = false;
                    this.qrisLoading = false;
                    this.qrisUrl = null;
                    this.qrisOrderId = null;
                },

                clearTimers() {
                    if (this.qrisTimer) clearInterval(this.qrisTimer);
                    if (this.qrisPollTimer) clearInterval(this.qrisPollTimer);
                },

                formatTime(seconds) {
                    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                    const s = (seconds % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                },

                async handleSubmit(event) {
                    // Let the form submit normally to server
                },
            }));
        });
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-layout>