<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col bg-surface overflow-hidden">

        <!-- Header -->
        <header
            class="flex justify-between items-center w-full px-4 lg:px-8 py-4 bg-white/80 backdrop-blur-xl sticky top-0 z-30 border-b border-surface-container">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-xl"
                        style="font-variation-settings: 'FILL' 1;">pending_actions</span>
                </div>
                <div>
                    <h1 class="font-headline font-extrabold text-xl text-on-surface">Pending Orders</h1>
                    <p class="text-xs text-on-surface-variant">{{ $pendingOrders->count() }} pesanan tertunda</p>
                </div>
            </div>
            <a href="/"
                class="px-4 py-2.5 bg-surface-container text-on-surface-variant rounded-xl text-sm font-bold hover:bg-surface-container-high transition-all flex items-center gap-2 group">
                <span
                    class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span>
                Kembali ke POS
            </a>
        </header>

        <!-- Content -->
        <div class="flex-1 p-4 lg:p-8 overflow-y-auto">

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    <span class="font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            @if($pendingOrders->isEmpty())
                <!-- Empty State -->
                <div class="flex flex-col items-center justify-center py-20">
                    <div class="w-24 h-24 rounded-3xl bg-surface-container flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-5xl text-outline/40">pending_actions</span>
                    </div>
                    <h3 class="font-headline font-extrabold text-xl text-on-surface mb-2">Tidak Ada Pesanan Tertunda</h3>
                    <p class="text-on-surface-variant text-center max-w-sm mb-6">
                        Pesanan yang disimpan namun belum dibayar akan muncul di sini.
                    </p>
                    <a href="/"
                        class="px-6 py-3 bg-gradient-to-r from-primary to-primary-container text-white rounded-xl font-bold hover:shadow-lg hover:shadow-primary/20 transition-all flex items-center gap-2 group">
                        <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                        Buat Pesanan Baru
                    </a>
                </div>
            @else
                <!-- Orders Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" x-data="pendingOrders()">
                    @foreach($pendingOrders as $order)
                        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/20 overflow-hidden hover:shadow-lg hover:shadow-primary/5 transition-all group"
                            x-data="countdown({{ $order->expires_at->timestamp }}, {{ $order->id }})">
                            <!-- Card Header -->
                            <div class="p-4 border-b border-outline-variant/10 bg-gradient-to-r from-primary/5 to-transparent">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
                                            <span class="material-symbols-outlined text-primary text-xl">receipt_long</span>
                                        </div>
                                        <div>
                                            <h3 class="font-headline font-bold text-base text-on-surface">
                                                {{ $order->order_number }}
                                            </h3>
                                            <p class="text-xs text-on-surface-variant mt-0.5">
                                                {{ $order->created_at->format('d M Y, H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-4 space-y-3">
                                <!-- Creator Info -->
                                <div class="flex items-center gap-2 text-sm text-on-surface-variant">
                                    <span class="material-symbols-outlined text-base">person</span>
                                    <span class="font-medium">{{ $order->user->name ?? 'Unknown' }}</span>
                                </div>

                                <!-- Items Summary -->
                                <div class="bg-surface-container rounded-xl p-3">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                            {{ count($order->cart_items) }} Item
                                        </span>
                                    </div>
                                    <div class="space-y-1.5 max-h-24 overflow-y-auto no-scrollbar">
                                        @foreach(array_slice($order->cart_items, 0, 3) as $item)
                                            <div class="flex justify-between text-xs">
                                                <span class="text-on-surface truncate flex-1">{{ $item['name'] }} <span
                                                        class="text-on-surface-variant">x{{ $item['quantity'] }}</span></span>
                                                <span
                                                    class="font-semibold text-on-surface ml-2">Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                        @if(count($order->cart_items) > 3)
                                            <div
                                                class="text-xs text-on-surface-variant font-medium pt-1 border-t border-outline-variant/20">
                                                +{{ count($order->cart_items) - 3 }} item lainnya
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Total & Timer -->
                                <div class="flex justify-between items-center pt-2 border-t border-outline-variant/10">
                                    <div>
                                        <p class="text-xs text-on-surface-variant mb-0.5">Total</p>
                                        <p class="font-headline font-extrabold text-xl text-primary">
                                            Rp{{ number_format($order->total, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <!-- Timer Badge -->
                                        <div class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl transition-colors"
                                            :class="timerClass">
                                            <span class="material-symbols-outlined text-sm" :class="iconClass">
                                                <span x-text="timeIcon"></span>
                                            </span>
                                            <div class="flex flex-col items-end">
                                                <span class="text-xs font-bold leading-tight" :class="textClass">
                                                    <span x-text="displayTime"></span>
                                                </span>
                                                <span class="text-[9px] font-medium leading-tight opacity-75"
                                                    :class="textClass">
                                                    tersisa
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Actions -->
                            <div class="p-4 pt-0 flex gap-2">
                                <a href="{{ route('pending-orders.load', $order->id) }}"
                                    class="flex-1 py-2.5 bg-gradient-to-r from-primary to-primary-container text-white rounded-xl font-bold text-sm hover:shadow-md hover:shadow-primary/20 transition-all flex items-center justify-center gap-2 group"
                                    :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': isExpired }">
                                    <span
                                        class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform">open_in_new</span>
                                    <span x-text="isExpired ? 'Kadaluarsa' : 'Lanjutkan'"></span>
                                </a>
                                <form action="{{ route('pending-orders.destroy', $order->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-2.5 bg-error/10 text-error rounded-xl font-bold text-sm hover:bg-error/20 transition-all flex items-center justify-center"
                                        onclick="return confirm('Hapus pesanan ini?')">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('countdown', (expiresAt, orderId) => ({
                expiresAt: expiresAt * 1000,
                orderId: orderId,
                now: Date.now(),
                interval: null,
                isExpired: false,

                init() {
                    this.updateTime();
                    this.interval = setInterval(() => {
                        this.now = Date.now();
                        this.updateTime();
                    }, 1000);
                },

                updateTime() {
                    const diff = this.expiresAt - this.now;
                    if (diff <= 0) {
                        this.isExpired = true;
                        clearInterval(this.interval);
                    }
                },

                get remainingSeconds() {
                    return Math.max(0, Math.floor((this.expiresAt - this.now) / 1000));
                },

                get remainingMinutes() {
                    return Math.floor(this.remainingSeconds / 60);
                },

                get remainingHours() {
                    return Math.floor(this.remainingMinutes / 60);
                },

                get displayTime() {
                    if (this.isExpired) return '00:00';

                    if (this.remainingHours > 0) {
                        const mins = this.remainingMinutes % 60;
                        const secs = this.remainingSeconds % 60;
                        return `${this.remainingHours}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                    }

                    const mins = this.remainingMinutes;
                    const secs = this.remainingSeconds % 60;
                    return `${mins}:${String(secs).padStart(2, '0')}`;
                },

                get timerClass() {
                    if (this.isExpired) return 'bg-gray-100';
                    if (this.remainingMinutes <= 5) return 'bg-red-100 animate-pulse';
                    if (this.remainingMinutes <= 15) return 'bg-orange-100';
                    return 'bg-green-100';
                },

                get iconClass() {
                    if (this.isExpired) return 'text-gray-500';
                    if (this.remainingMinutes <= 5) return 'text-red-600';
                    if (this.remainingMinutes <= 15) return 'text-orange-600';
                    return 'text-green-600';
                },

                get textClass() {
                    if (this.isExpired) return 'text-gray-500';
                    if (this.remainingMinutes <= 5) return 'text-red-600';
                    if (this.remainingMinutes <= 15) return 'text-orange-600';
                    return 'text-green-600';
                },

                get timeIcon() {
                    if (this.isExpired) return 'timer_off';
                    if (this.remainingMinutes <= 5) return 'warning';
                    return 'hourglass_top';
                }
            }));

            Alpine.data('pendingOrders', () => ({}));
        });
    </script>
</x-layout>