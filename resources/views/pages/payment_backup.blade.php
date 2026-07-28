<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex h-[calc(100vh-64px)] w-full">

    <!-- Top Bar -->
    <header class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">{{ $title ?? 'Page' }}</h1>
      </div>
    </header>


        <!-- Main Content (Payment Canvas) -->
        <section class="flex-1 p-8 flex flex-col gap-8 bg-surface" x-data="paymentCheckout()">
            <div class="mb-6 lg:mb-8">
                <x-report-header title="Complete Transaction" module="POS" submodule="Payment" description="Finalize the transaction and process customer payment." />
            </div>

            <form action="/payment/process" method="POST" class="grid grid-cols-12 gap-8 flex-1" @submit="handleSubmit($event)">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice_id }}">
                <input type="hidden" name="payment_method" x-model="method">
                <input type="hidden" name="amount_received" x-model="received">
                <input type="hidden" name="voucher_code" x-model="voucherCode" x-bind:disabled="!voucherApplied">
                <input type="hidden" name="points_to_redeem" x-model="pointsToRedeem">

                <!-- Left Column: Receipt Preview -->
                <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
                    <div
                        class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-6 flex flex-col h-full overflow-hidden">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="material-symbols-outlined text-primary">receipt_long</span>
                            <h3 class="font-headline text-lg font-bold">Receipt Preview</h3>
                        </div>
                        <div class="border-t border-dashed border-outline-variant/50 mb-4"></div>
                        <div class="flex-1 overflow-y-auto no-scrollbar space-y-4">
                            @foreach($cart as $item)
                                <div class="flex justify-between items-start text-sm">
                                    <div>
                                        <h4 class="font-bold text-on-surface">{{ $item['name'] }}</h4>
                                        <p class="text-xs text-on-surface-variant">Qty: {{ $item['quantity'] }} x
                                            Rp{{ number_format($item['price'], 0, ',', '.') }}</p>
                                    </div>
                                    <span
                                        class="font-bold text-on-surface">Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="border-t border-dashed border-outline-variant/50 mt-4 pt-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-on-surface-variant font-medium">Subtotal</span>
                                <span
                                    class="font-bold text-on-surface">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            
                            @if($promo_discount > 0)
                            <div class="flex justify-between text-green-600 font-bold">
                                <span>Promo Discount ({{ count($applied_promos) > 0 ? collect($applied_promos)->pluck('name')->implode(', ') : 'Applied' }})</span>
                                <span>-Rp{{ number_format($promo_discount, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            
                            <div class="flex justify-between">
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
                                <span class="font-bold font-headline text-base text-on-surface">Total Due</span>
                                <span class="font-extrabold font-headline text-lg text-primary"
                                    x-text="formatCurrency(totalDue)"></span>
                            </div>
                            @if($customer)
                            <div class="flex justify-between text-yellow-600 text-xs mt-2" x-show="totalDue > 0">
                                <span>Points to Earn:</span>
                                <span class="font-bold" x-text="Math.floor({{ $subtotal }} / {{ $loyalty_points_per_rupiah }}).toLocaleString('id-ID') + ' pts'"></span>
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
                <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
                    <!-- Total Due Card -->
                    <div
                        class="bg-primary-container rounded-xl p-6 text-white shadow-xl relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 opacity-10 font-bold text-[100px]">
                            <span class="material-symbols-outlined text-[80px]">payments</span>
                        </div>
                        <p class="text-blue-100 font-label tracking-wider uppercase text-xs mb-2">Total Due</p>
                        <h2 class="text-4xl font-extrabold font-headline" x-text="formatCurrency(totalDue)"></h2>
                        <div class="mt-4 flex items-center gap-2 bg-white/10 w-fit px-3 py-1 rounded-full text-xs">
                            <span class="material-symbols-outlined text-sm">shopping_basket</span>
                            <span>{{ count($cart) }} items in cart</span>
                        </div>
                    </div>
                    <!-- Payment Methods -->
                    <div
                        class="bg-surface-container-lowest rounded-xl p-6 shadow-sm flex-1 border border-outline-variant/10">
                        <h3 class="text-base font-bold mb-4 font-headline">Select Payment Method</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="setMethod('cash')" :disabled="qrisActive"
                                :class="method === 'cash' ? 'border-primary bg-primary-fixed text-on-primary-fixed-variant' : 'border-outline-variant/30 bg-surface text-on-surface-variant hover:bg-surface-container'"
                                class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl border-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="material-symbols-outlined text-2xl">payments</span>
                                <span class="font-bold text-xs flex-1 text-center">Cash</span>
                            </button>
                            <button type="button" @click="setMethod('qris')" :disabled="qrisActive && method !== 'qris'"
                                :class="method === 'qris' ? 'border-primary bg-primary-fixed text-on-primary-fixed-variant' : 'border-outline-variant/30 bg-surface text-on-surface-variant hover:bg-surface-container'"
                                class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl border-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed group">
                                <span
                                    class="material-symbols-outlined text-2xl group-hover:text-primary transition-colors">qr_code_2</span>
                                <span class="font-bold text-xs flex-1 text-center">QRIS</span>
                            </button>
                            <button type="button" @click="setMethod('debit')" :disabled="qrisActive"
                                :class="method === 'debit' ? 'border-primary bg-primary-fixed text-on-primary-fixed-variant' : 'border-outline-variant/30 bg-surface text-on-surface-variant hover:bg-surface-container'"
                                class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl border-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed group">
                                <span
                                    class="material-symbols-outlined text-2xl group-hover:text-primary transition-colors">credit_card</span>
                                <span class="font-bold text-xs flex-1 text-center">Debit Card</span>
                            </button>
                            <button type="button" @click="setMethod('credit')" :disabled="qrisActive"
                                :class="method === 'credit' ? 'border-primary bg-primary-fixed text-on-primary-fixed-variant' : 'border-outline-variant/30 bg-surface text-on-surface-variant hover:bg-surface-container'"
                                class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl border-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed group">
                                <span
                                    class="material-symbols-outlined text-2xl group-hover:text-primary transition-colors">account_balance_wallet</span>
                                <span class="font-bold text-xs flex-1 text-center">Credit Card</span>
                            </button>
                        </div>
                    </div>

                    <!-- Voucher -->
                    <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm border border-outline-variant/10">
                        <h3 class="text-base font-bold mb-3 font-headline">Apply Voucher</h3>
                        <div class="flex gap-2">
                            <input type="text" x-model="voucherCode" :disabled="voucherApplied || qrisActive"
                                class="flex-1 w-full bg-surface border border-outline-variant/30 rounded-lg px-3 py-2 text-sm font-body text-on-surface focus:ring-2 focus:ring-primary/20 outline-none uppercase min-w-0 disabled:opacity-50"
                                placeholder="Code">
                            <button type="button" @click="checkVoucher()" x-show="!voucherApplied"
                                :disabled="qrisActive"
                                class="bg-primary text-white px-4 py-2 rounded-lg font-bold text-sm hover:opacity-90 active:scale-95 transition-all w-24 disabled:opacity-50 disabled:cursor-not-allowed">
                                Apply
                            </button>
                            <button type="button" @click="removeVoucher()" x-cloak x-show="voucherApplied"
                                :disabled="qrisActive"
                                class="bg-error text-white px-4 py-2 rounded-lg font-bold text-sm hover:opacity-90 active:scale-95 transition-all w-24 disabled:opacity-50 disabled:cursor-not-allowed">
                                Remove
                            </button>
                        </div>
                        <p class="text-xs mt-2 font-medium" :class="voucherMessageClass" x-text="voucherMessage"
                            x-show="voucherMessage"></p>
                    </div>

                    <!-- Loyalty Points -->
                    @if($customer)
                    <div class="bg-yellow-50 rounded-xl p-6 shadow-sm border border-yellow-200">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-base font-bold font-headline text-yellow-800">Loyalty Points</h3>
                            <span class="px-2 py-1 bg-yellow-200 text-yellow-800 text-xs font-bold rounded-full uppercase">
                                {{ $customer->tier }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm mb-3">
                            <span class="text-yellow-700">Your Points:</span>
                            <span class="font-bold text-yellow-800">{{ number_format($customer->available_points) }} pts</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" x-model="pointsToRedeem" min="0" max="{{ $customer->available_points }}"
                                class="flex-1 w-full bg-white border border-yellow-300 rounded-lg px-3 py-2 text-sm font-body text-on-surface focus:ring-2 focus:ring-yellow-400/20 outline-none"
                                placeholder="Points to redeem">
                            <button type="button" @click="applyPoints()"
                                :disabled="pointsToRedeem < {{ $loyalty_min_redeem }}"
                                class="bg-yellow-500 text-white px-4 py-2 rounded-lg font-bold text-sm hover:opacity-90 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                Apply
                            </button>
                        </div>
                        <p class="text-xs text-yellow-600 mt-2">
                            1 point = Rp{{ number_format($loyalty_point_value, 0, ',', '.') }} discount
                        </p>
                        <p x-show="pointsApplied" class="text-xs text-green-600 font-bold mt-1" x-text="'-Rp' + new Intl.NumberFormat('id-ID').format(pointsDiscount) + ' (' + pointsRedeemed + ' pts)'"></p>
                    </div>
                    @endif
                </div>

                <!-- Right Column: Action Area -->
                <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
                    <div
                        class="bg-surface-container-lowest rounded-xl p-6 shadow-sm flex flex-col h-full border border-outline-variant/10 relative overflow-hidden">

                        <!-- ================= CASH UI ================= -->
                        <div x-show="method !== 'qris'" class="flex flex-col h-full gap-4">
                            @if($errors->any())
                                <div class="p-3 bg-error/10 text-error text-sm rounded-lg">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-on-surface-variant">Amount
                                    Received</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-xl font-bold text-on-surface-variant">Rp</span>
                                    </div>
                                    <input x-model="receivedInput" readonly
                                        class="block w-full pl-12 pr-4 py-4 border border-outline-variant/20 bg-surface rounded-xl text-3xl font-headline font-bold focus:border-transparent focus:ring-2 focus:ring-primary/20 transition-all text-on-surface outline-none"
                                        placeholder="0" type="text" />
                                </div>
                            </div>

                            <!-- Custom Numpad Keypad -->
                            <div class="grid grid-cols-3 gap-2 mt-2">
                                <button type="button" @click="keypadPress(1)"
                                    class="bg-surface hover:bg-surface-container py-4 rounded-xl font-headline font-bold text-xl text-on-surface transition-colors border border-outline-variant/20">1</button>
                                <button type="button" @click="keypadPress(2)"
                                    class="bg-surface hover:bg-surface-container py-4 rounded-xl font-headline font-bold text-xl text-on-surface transition-colors border border-outline-variant/20">2</button>
                                <button type="button" @click="keypadPress(3)"
                                    class="bg-surface hover:bg-surface-container py-4 rounded-xl font-headline font-bold text-xl text-on-surface transition-colors border border-outline-variant/20">3</button>

                                <button type="button" @click="keypadPress(4)"
                                    class="bg-surface hover:bg-surface-container py-4 rounded-xl font-headline font-bold text-xl text-on-surface transition-colors border border-outline-variant/20">4</button>
                                <button type="button" @click="keypadPress(5)"
                                    class="bg-surface hover:bg-surface-container py-4 rounded-xl font-headline font-bold text-xl text-on-surface transition-colors border border-outline-variant/20">5</button>
                                <button type="button" @click="keypadPress(6)"
                                    class="bg-surface hover:bg-surface-container py-4 rounded-xl font-headline font-bold text-xl text-on-surface transition-colors border border-outline-variant/20">6</button>

                                <button type="button" @click="keypadPress(7)"
                                    class="bg-surface hover:bg-surface-container py-4 rounded-xl font-headline font-bold text-xl text-on-surface transition-colors border border-outline-variant/20">7</button>
                                <button type="button" @click="keypadPress(8)"
                                    class="bg-surface hover:bg-surface-container py-4 rounded-xl font-headline font-bold text-xl text-on-surface transition-colors border border-outline-variant/20">8</button>
                                <button type="button" @click="keypadPress(9)"
                                    class="bg-surface hover:bg-surface-container py-4 rounded-xl font-headline font-bold text-xl text-on-surface transition-colors border border-outline-variant/20">9</button>

                                <button type="button" @click="keypadPress('000')"
                                    class="bg-surface hover:bg-surface-container py-4 rounded-xl font-headline font-bold text-xl text-on-surface transition-colors border border-outline-variant/20">000</button>
                                <button type="button" @click="keypadPress(0)"
                                    class="bg-surface hover:bg-surface-container py-4 rounded-xl font-headline font-bold text-xl text-on-surface transition-colors border border-outline-variant/20">0</button>
                                <button type="button" @click="keypadClear()"
                                    class="bg-error/10 hover:bg-error/20 text-error py-4 rounded-xl font-headline font-bold text-xl transition-colors border border-error/20 flex items-center justify-center">
                                    <span class="material-symbols-outlined">backspace</span>
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <button type="button" @click="setAmount(50000)"
                                    class="bg-surface hover:bg-primary-fixed py-2.5 rounded-lg font-headline font-bold text-sm text-on-surface transition-colors border border-outline-variant/30 hover:border-primary/20">
                                    50.000
                                </button>
                                <button type="button" @click="setAmount(100000)"
                                    class="bg-surface hover:bg-primary-fixed py-2.5 rounded-lg font-headline font-bold text-sm text-on-surface transition-colors border border-outline-variant/30 hover:border-primary/20">
                                    100.000
                                </button>
                                <button type="button" @click="setAmount(totalDue)"
                                    class="col-span-2 bg-primary/10 text-primary py-2.5 rounded-lg font-headline font-extrabold text-sm hover:bg-primary/20 transition-colors border border-primary/20">
                                    Exact Amount
                                </button>
                            </div>

                            <!-- Real-time Calculation Display -->
                            <div class="mt-auto pt-4 border-t border-surface-container flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-on-surface-variant font-medium">Change to Give</p>
                                    <p class="text-2xl font-headline font-extrabold"
                                        :class="change < 0 ? 'text-error' : 'text-primary'"
                                        x-text="formatCurrency(Math.max(0, change))"></p>
                                </div>

                                <button type="submit" :disabled="change < 0 || !received"
                                    class="w-full bg-gradient-to-r from-primary to-primary-container text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 tracking-wide disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span>Complete Payment</span>
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                </button>
                            </div>
                        </div>

                        <!-- ================= QRIS / E-WALLET UI ================= -->
                        <div x-cloak x-show="method === 'qris'"
                            class="flex flex-col h-full items-center justify-center text-center">

                            <!-- State: Not Generated -->
                            <div x-show="!qrisActive && !qrisSuccess && !qrisLoading"
                                class="flex flex-col items-center gap-4 py-8">
                                <div class="w-24 h-24 bg-primary/10 rounded-full flex items-center justify-center mb-2">
                                    <span class="material-symbols-outlined text-primary text-5xl">qr_code_scanner</span>
                                </div>
                                <h3 class="font-headline font-bold text-xl text-on-surface">Pay with QRIS</h3>
                                <p class="text-sm text-on-surface-variant max-w-[250px]">Generate kode QR untuk
                                    pelanggan scan dengan e-Wallet atau aplikasi bank.</p>
                                <button type="button" @click="generateQris()"
                                    class="mt-4 w-full bg-primary text-white font-bold py-3 px-6 rounded-lg shadow-md shadow-primary/20 hover:bg-primary-container active:scale-95 transition-all flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-sm">qr_code_2</span>
                                    Generate QR Code
                                </button>
                            </div>

                            <!-- State: Generating/Loading -->
                            <div x-show="qrisLoading" class="flex flex-col items-center gap-4 py-12">
                                <span
                                    class="material-symbols-outlined animate-spin text-primary text-4xl">autorenew</span>
                                <p class="text-sm font-semibold text-on-surface-variant animate-pulse">Connecting to
                                    Midtrans...</p>
                            </div>

                            <!-- State: Active QR -->
                            <div x-show="qrisActive && !qrisSuccess" class="flex flex-col w-full h-full">
                                <div class="flex-1 flex flex-col items-center justify-center py-4">
                                    <!-- Status Indicator -->
                                    <div
                                        class="bg-primary/10 text-primary px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mb-6 flex items-center gap-2 animate-pulse">
                                        <span class="w-2 h-2 rounded-full bg-primary"></span>
                                        Menunggu pembayaran...
                                    </div>

                                    <!-- QR Image -->
                                    <div
                                        class="bg-white p-4 rounded-2xl shadow-sm border border-outline-variant/20 mb-6 relative group">
                                        <template x-if="qrisUrl">
                                            <img :src="qrisUrl" alt="QRIS Code" class="w-48 h-48 object-contain">
                                        </template>
                                        <template x-if="!qrisUrl">
                                            <div
                                                class="w-48 h-48 bg-slate-100 flex items-center justify-center relative overflow-hidden rounded-xl border-2 border-dashed border-slate-300">
                                                <span
                                                    class="material-symbols-outlined text-slate-400 text-6xl">qr_code_2</span>
                                                <div
                                                    class="absolute inset-0 bg-gradient-to-b from-transparent via-primary/5 to-transparent w-full h-full animate-[scan_2s_ease-in-out_infinite]">
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Countdown -->
                                    <p class="text-sm font-medium text-on-surface-variant mb-1">Habis dalam</p>
                                    <p class="font-headline font-extrabold text-3xl text-on-surface mb-8 tracking-widest font-mono"
                                        x-text="formatTime(qrisTimeLeft)"></p>
                                </div>

                                <div class="mt-auto grid grid-cols-2 gap-3 pt-4 border-t border-outline-variant/10">
                                    <button type="button" @click="cancelQris()"
                                        class="bg-surface hover:bg-error/10 text-error font-bold py-3 rounded-lg border border-error/20 transition-colors">
                                        Batal
                                    </button>
                                    <button type="button" @click="generateQris()"
                                        class="bg-surface hover:bg-primary-fixed text-primary font-bold py-3 rounded-lg border border-primary/20 transition-colors flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-sm">refresh</span> Segarkan QR
                                    </button>
                                </div>
                            </div>

                            <!-- State: Success -->
                            <div x-show="qrisSuccess"
                                class="flex flex-col items-center justify-center h-full w-full py-12 gap-4">
                                <div
                                    class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center animate-[bounce_0.5s_ease-out]">
                                    <span class="material-symbols-outlined text-green-600 text-6xl"
                                        style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                </div>
                                <h3 class="font-headline font-extrabold text-2xl text-on-surface mt-2">Pembayaran
                                    Berhasil!</h3>
                                <p class="text-on-surface-variant text-sm">Mengalihkan ke struk...</p>
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

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer>
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

                get change() {
                    let r = parseInt(this.received) || 0;
                    return r - this.totalDue;
                },

                setMethod(m) {
                    if (this.qrisActive && m !== 'qris') return;
                    this.method = m;
                    if (m === 'qris') {
                        this.received = this.totalDue;
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
                            this.totalDue = Math.max(0, {{ $total }} - data.discount);
                            if (this.method === 'qris') this.received = this.totalDue;
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
                    this.totalDue = {{ $total }};
                    if (this.method === 'qris') this.received = this.totalDue;
                },

                applyPoints() {
                    if (this.pointsToRedeem < this.loyaltyMinRedeem) return;
                    this.pointsRedeemed = Math.min(this.pointsToRedeem, {{ $customer ? $customer->available_points : 0 }});
                    this.pointsDiscount = this.pointsRedeemed * this.loyaltyPointValue;
                    this.pointsApplied = true;
                    this.totalDue = Math.max(0, {{ $total }} - this.voucherDiscount - this.pointsDiscount);
                    if (this.method === 'qris') this.received = this.totalDue;
                },

                removePoints() {
                    this.pointsToRedeem = 0;
                    this.pointsApplied = false;
                    this.pointsDiscount = 0;
                    this.pointsRedeemed = 0;
                    this.totalDue = Math.max(0, {{ $total }} - this.voucherDiscount);
                    if (this.method === 'qris') this.received = this.totalDue;
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
                            Swal.fire({ icon: 'error', title: 'Gagal Generate QR', text: 'Gagal generate QR: ' + data.message, confirmButtonColor: '#3085d6' });
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