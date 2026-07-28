<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <!-- POS Terminal Layout -->
    <main class="flex-1 flex flex-col bg-surface overflow-hidden" x-data="posCart()"
        @add-to-cart="addToCart($event.detail)">


        <!-- Header: Scan Bar + Actions -->
        <header
            class="flex items-center gap-2 flex-wrap w-full pl-3 pr-3 lg:px-6 py-2 lg:py-3 bg-white/90 backdrop-blur-xl z-30 shadow-sm font-manrope lg:pl-8">
            <!-- Barcode Scan Input -->
            <div class="flex-1 relative">
                <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-xl">barcode_scanner</span>
                <input id="scanInput" type="text" x-model="scanQuery" @keydown.enter="scanProduct()"
                    class="w-full pl-12 pr-4 py-2.5 lg:py-3 bg-slate-100/50 border-2 border-transparent focus:border-primary rounded-xl focus:ring-0 transition-all text-sm lg:text-base font-bold outline-none placeholder:text-slate-400 placeholder:font-normal"
                    placeholder="Scan barcode..." autocomplete="off" autofocus />
            </div>

            <!-- Camera Scan Button (Mobile) -->
            <button @click="openCameraScanner()"
                class="flex lg:hidden items-center justify-center w-10 h-10 bg-primary/10 text-primary font-bold rounded-xl shadow-md hover:bg-primary/20 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-xl">qr_code_scanner</span>
            </button>

            <!-- Product List Button -->
            <button @click="showProductModal = true"
                class="flex items-center gap-2 px-3 lg:px-4 py-2.5 bg-primary text-white font-bold rounded-xl shadow-md hover:bg-primary-container active:scale-95 transition-all text-xs lg:text-sm whitespace-nowrap">
                <span class="material-symbols-outlined text-lg">grid_view</span>
                <span class="hidden sm:inline">List Produk</span>
            </button>

            <!-- Tutup Shift Button -->
            @if(!auth()->user()->isSuperAdmin())
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-clerek-modal'))"
                    class="hidden lg:flex items-center gap-2 px-4 py-2.5 bg-error/10 text-error font-bold rounded-xl shadow-md hover:bg-error/20 active:scale-95 transition-all text-sm whitespace-nowrap">
                    <span class="material-symbols-outlined text-lg">point_of_sale</span>
                    <span>Tutup Shift</span>
                </button>
            @endif
        </header>

        <!-- ==================== DESKTOP LAYOUT ==================== -->
        <div class="hidden lg:flex flex-1 overflow-hidden">
            <!-- Cart Items -->
            <div class="flex-1 flex flex-col overflow-hidden border-r border-slate-200/50">
                <div
                    class="p-5 border-b border-slate-200/50 flex justify-between items-center bg-white/50 backdrop-blur">
                    <div class="flex items-center gap-2">
                        <h2 class="font-headline font-extrabold text-lg text-blue-900">Keranjang</h2>
                        <span x-show="cart.length > 0" x-text="'(' + cart.length + ')'"
                            class="text-xs text-slate-400 font-medium"></span>
                    </div>
                    <button @click="clearCart()"
                        class="text-error text-xs font-bold uppercase tracking-widest hover:bg-error/10 px-3 py-1.5 rounded-lg transition-colors">Clear
                        All</button>
                </div>
                <div class="flex-1 overflow-y-auto p-5 space-y-3 no-scrollbar">
                    <template x-for="item in cart" :key="item.id">
                        <div
                            class="flex items-center gap-4 bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                            <div
                                class="w-14 h-14 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                                <template x-if="item.image">
                                    <img :src="'/storage/' + item.image" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.image">
                                    <span class="material-symbols-outlined text-slate-400 text-xl">image</span>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-on-surface leading-tight truncate" x-text="item.name">
                                </h4>
                                <p class="font-headline text-primary text-xs font-bold mt-0.5"
                                    x-text="formatCurrency(item.price)"></p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center bg-slate-100 rounded-lg p-1 gap-1">
                                    <button type="button" @click="decreaseQuantity(item.id)"
                                        class="w-7 h-7 flex items-center justify-center hover:bg-white rounded transition-colors text-slate-600">
                                        <span class="material-symbols-outlined text-sm">remove</span>
                                    </button>
                                    <input type="number" min="1" :max="item.stock" x-model.number="item.quantity"
                                        :data-item-id="item.id" @input="validateQuantity(item.id, item.stock)"
                                        @change="validateQuantity(item.id, item.stock)"
                                        class="text-xs font-bold w-12 text-center bg-transparent border-none outline-none focus:ring-1 focus:ring-primary/20 rounded-md p-0"
                                        style="-moz-appearance: textfield;">
                                    <button type="button" @click="increaseQuantity(item.id)"
                                        class="w-7 h-7 flex items-center justify-center hover:bg-white rounded transition-colors text-slate-600">
                                        <span class="material-symbols-outlined text-sm">add</span>
                                    </button>
                                </div>
                                <div class="text-right min-w-[5rem]">
                                    <div class="text-sm font-bold text-on-surface"
                                        x-text="formatCurrency(item.price * item.quantity)"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="cart.length === 0"
                        class="flex flex-col items-center justify-center h-full text-center">
                        <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-slate-300 text-4xl">shopping_cart</span>
                        </div>
                        <p class="text-sm text-slate-400 font-medium">Keranjang kosong</p>
                        <p class="text-xs text-slate-300 mt-1">Scan produk atau pilih dari list</p>
                    </div>
                </div>
            </div>

            <!-- Right: Info Panel (Desktop) -->
            <div class="w-[26rem] bg-surface-container-low flex flex-col shadow-[-12px_0_32px_rgba(0,26,64,0.04)]">
                <div class="flex-1 overflow-y-auto p-5 space-y-4 no-scrollbar">
                    <!-- Member Section -->
                    <div class="bg-white rounded-xl p-4 shadow-sm space-y-2 relative">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-primary text-lg">person</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Member</span>
                        </div>
                        <div class="flex gap-2 relative">
                            <div class="relative flex-1">
                                <span
                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-base">search</span>
                                <input type="text" id="memberSearch" placeholder="Cari nama atau HP..."
                                    class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"
                                    autocomplete="off">
                            </div>
                            <button type="button"
                                onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                                class="px-3 py-2 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors">
                                <span class="material-symbols-outlined text-lg">person_add</span>
                            </button>
                        </div>
                        <div id="memberDropdown"
                            class="hidden absolute left-0 right-0 z-50 mt-1 bg-white rounded-lg shadow-xl border border-slate-200 max-h-48 overflow-y-auto">
                        </div>
                        <div id="selectedMember" class="hidden flex items-center gap-2 p-3 bg-primary/10 rounded-lg">
                            <span class="material-symbols-outlined text-primary text-lg">check_circle</span>
                            <div>
                                <div class="text-xs font-bold text-primary" id="selectedMemberName"></div>
                                <div class="text-[10px] text-slate-400" id="selectedMemberTier"></div>
                            </div>
                            <button type="button" onclick="clearMember()"
                                class="ml-auto text-slate-400 hover:text-error">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                    </div>

                    <!-- Promo Info -->
                    <div class="bg-white rounded-xl p-4 shadow-sm space-y-2">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-orange-500 text-lg">sell</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Promo</span>
                        </div>
                        <div x-show="cart.length === 0" class="text-center py-3">
                            <p class="text-xs text-slate-400">Tambahkan produk untuk melihat promo</p>
                        </div>
                        <div x-show="cart.length > 0" class="space-y-2">
                            <template x-for="promo in applicablePromos" :key="promo.id">
                                <div
                                    class="flex items-start gap-2 p-2.5 bg-orange-50 rounded-lg border border-orange-100">
                                    <span class="material-symbols-outlined text-orange-500 text-lg flex-shrink-0 mt-0.5"
                                        x-text="promo.icon"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-orange-700" x-text="promo.message"></p>
                                        <p class="text-[10px] text-orange-500 mt-0.5" x-text="promo.subtext"></p>
                                    </div>
                                </div>
                            </template>
                            <div x-show="applicablePromos.length === 0" class="text-center py-2">
                                <p class="text-xs text-slate-400">Tidak ada promo aktif untuk keranjang ini</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="bg-white rounded-xl p-4 shadow-sm space-y-3">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-blue-600 text-lg">receipt_long</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Ringkasan</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Jumlah Item</span>
                                <span class="font-bold text-on-surface" x-text="cart.length + ' produk'"></span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Total Qty</span>
                                <span class="font-bold text-on-surface"
                                    x-text="totalQty.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Subtotal</span>
                                <span class="font-bold text-on-surface" x-text="formatCurrency(subtotal)"></span>
                            </div>
                            <div x-show="serviceCharge > 0" class="flex justify-between text-xs">
                                <span class="text-slate-500">Service Charge</span>
                                <span class="font-bold text-on-surface" x-text="formatCurrency(serviceCharge)"></span>
                            </div>
                            <div class="border-t border-slate-100 pt-3 mt-3">
                                <div class="flex justify-between">
                                    <span class="text-sm font-bold text-on-surface">Total Belanja</span>
                                    <span class="text-lg font-extrabold text-primary"
                                        x-text="formatCurrency(total)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom: Checkout Actions -->
                <div class="p-5 bg-white border-t border-slate-200/50 space-y-3">
                    <form action="/payment/checkout" method="POST">
                        @csrf
                        <input type="hidden" name="cart" x-model="JSON.stringify(cart)">
                        <input type="hidden" name="customer_id" id="customerId">
                        <button :disabled="cart.length === 0"
                            class="w-full py-4 bg-gradient-to-r from-primary to-primary-container text-white rounded-xl font-headline font-extrabold text-base shadow-lg shadow-primary/20 hover:scale-[0.98] transition-transform active:opacity-90 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined text-lg">payments</span>
                            <span>Process Checkout</span>
                        </button>
                    </form>
                    <button :disabled="cart.length === 0" @click="savePendingOrder()"
                        class="w-full py-3 bg-slate-100 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-200 transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined text-lg">save</span>
                        <span>Save for Later</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== MOBILE LAYOUT ==================== -->
        <div class="lg:hidden flex-1 flex flex-col overflow-hidden">
            <!-- Cart Items (Scrollable) -->
            <div class="flex-1 overflow-y-auto pl-3 pr-3 py-3 space-y-2 no-scrollbar pb-32">
                <template x-for="item in cart" :key="item.id">
                    <div class="flex items-center gap-3 bg-white p-3 rounded-xl shadow-sm">
                        <div
                            class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                            <template x-if="item.image">
                                <img :src="'/storage/' + item.image" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!item.image">
                                <span class="material-symbols-outlined text-slate-400 text-xl">image</span>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-on-surface leading-tight truncate" x-text="item.name">
                            </h4>
                            <p class="font-headline text-primary text-[10px] font-bold mt-0.5"
                                x-text="formatCurrency(item.price)"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center bg-slate-100 rounded-lg p-0.5 gap-0.5">
                                <button type="button" @click="decreaseQuantity(item.id)"
                                    class="w-6 h-6 flex items-center justify-center hover:bg-white rounded transition-colors text-slate-600">
                                    <span class="material-symbols-outlined text-sm">remove</span>
                                </button>
                                <input type="number" min="1" :max="item.stock" x-model.number="item.quantity"
                                    :data-item-id="item.id" @input="validateQuantity(item.id, item.stock)"
                                    @change="validateQuantity(item.id, item.stock)"
                                    class="text-xs font-bold w-10 text-center bg-transparent border-none outline-none p-0"
                                    style="-moz-appearance: textfield;">
                                <button type="button" @click="increaseQuantity(item.id)"
                                    class="w-6 h-6 flex items-center justify-center hover:bg-white rounded transition-colors text-slate-600">
                                    <span class="material-symbols-outlined text-sm">add</span>
                                </button>
                            </div>
                            <div class="text-right min-w-[4rem]">
                                <div class="text-xs font-bold text-on-surface"
                                    x-text="formatCurrency(item.price * item.quantity)"></div>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-full text-center">
                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-slate-300 text-3xl">shopping_cart</span>
                    </div>
                    <p class="text-xs text-slate-400 font-medium">Keranjang kosong</p>
                    <p class="text-[10px] text-slate-300 mt-1">Scan produk atau pilih dari list</p>
                </div>
            </div>

            <!-- Mobile Bottom Bar (Fixed) -->
            <div class="absolute bottom-0 left-0 right-0 bg-white border-t border-slate-200 shadow-lg z-20">
                <!-- Cart Summary Row -->
                <div class="px-3 py-2 flex justify-between items-center border-b border-slate-100"
                    x-show="cart.length > 0">
                    <div>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Total</p>
                        <p class="text-base font-extrabold text-primary" x-text="formatCurrency(total)"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider"
                            x-text="cart.length + ' item • ' + totalQty + ' qty'"></p>
                        <p x-show="selectedMemberName.textContent" class="text-[10px] text-primary font-bold">
                            <span class="material-symbols-outlined text-xs inline-block align-middle">person</span>
                            <span id="mobileMemberName"></span>
                        </p>
                    </div>
                </div>

                <!-- Member Input (Mobile) -->
                <div class="px-3 py-2 relative" x-show="cart.length > 0">
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <span
                                class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-base">person_search</span>
                            <input type="text" id="mobileMemberSearch" placeholder="Cari member..."
                                class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none h-12"
                                autocomplete="off">
                        </div>
                        <button type="button"
                            onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                            class="px-2.5 py-2 bg-primary/10 text-primary rounded-lg">
                            <span class="material-symbols-outlined text-lg">person_add</span>
                        </button>
                    </div>
                    <div id="mobileMemberDropdown"
                        class="hidden absolute left-3 right-3 bottom-full z-50 mb-1 bg-white rounded-lg shadow-xl border border-slate-200 max-h-48 overflow-y-auto">
                    </div>
                    <div id="mobileSelectedMember"
                        class="hidden flex items-center gap-2 p-2 bg-primary/10 rounded-lg mt-2">
                        <span class="material-symbols-outlined text-primary text-base">check_circle</span>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-bold text-primary truncate" id="mobileSelectedMemberName"></div>
                            <div class="text-[9px] text-slate-400" id="mobileSelectedMemberTier"></div>
                        </div>
                        <button type="button" onclick="clearMobileMember()" class="text-slate-400 hover:text-error">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                </div>

                <!-- Payment Button -->
                <div class="px-3 py-2 space-y-2">
                    <button @click="cart.length > 0 && openCheckoutModal()" :disabled="cart.length === 0"
                        class="w-full py-3 bg-gradient-to-r from-primary to-primary-container text-white rounded-xl font-headline font-extrabold text-sm shadow-lg shadow-primary/20 active:scale-[0.98] transition-transform flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined text-lg">payments</span>
                        <span>Bayar Sekarang</span>
                    </button>
                    <button :disabled="cart.length === 0" @click="savePendingOrder()"
                        class="w-full py-2 bg-slate-100 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-200 transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined text-base">save</span>
                        <span>Simpan Nanti</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== CHECKOUT MODAL (Mobile) ==================== -->
        <div x-show="showCheckoutModal" x-cloak class="fixed inset-0 z-[100] flex items-end justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showCheckoutModal = false"></div>
            <div class="relative w-full bg-surface-container-lowest rounded-t-3xl shadow-2xl overflow-hidden max-h-[85vh] flex flex-col"
                @click.outside="showCheckoutModal = false">
                <!-- Modal Header -->
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white">
                    <div>
                        <h3 class="text-lg font-black text-primary">Checkout</h3>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest"
                            x-text="cart.length + ' item • ' + totalQty + ' qty'"></p>
                    </div>
                    <button @click="showCheckoutModal = false"
                        class="w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-slate-400">close</span>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4 no-scrollbar">
                    <!-- Promo Notifications -->
                    <div x-show="applicablePromos.length > 0" class="space-y-2">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="material-symbols-outlined text-orange-500 text-lg">notifications_active</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-orange-600">Promo
                                Tersedia!</span>
                        </div>
                        <template x-for="promo in applicablePromos" :key="promo.id">
                            <div class="flex items-start gap-2 p-3 bg-orange-50 rounded-xl border border-orange-200">
                                <span class="material-symbols-outlined text-orange-500 text-lg flex-shrink-0"
                                    x-text="promo.icon"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-orange-700" x-text="promo.message"></p>
                                    <p class="text-[10px] text-orange-500 mt-0.5" x-text="promo.subtext"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Cart Items Summary -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Item</h4>
                        <template x-for="item in cart" :key="item.id">
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-on-surface truncate" x-text="item.name"></p>
                                    <p class="text-[10px] text-slate-400"
                                        x-text="item.quantity + ' x ' + formatCurrency(item.price)"></p>
                                </div>
                                <p class="text-xs font-bold text-on-surface ml-2"
                                    x-text="formatCurrency(item.price * item.quantity)"></p>
                            </div>
                        </template>
                    </div>

                    <!-- Summary -->
                    <div class="bg-slate-50 rounded-xl p-4 space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-bold" x-text="formatCurrency(subtotal)"></span>
                        </div>
                        <div x-show="serviceCharge > 0" class="flex justify-between text-xs">
                            <span class="text-slate-500">Service Charge</span>
                            <span class="font-bold" x-text="formatCurrency(serviceCharge)"></span>
                        </div>
                        <div class="border-t border-slate-200 pt-2 flex justify-between">
                            <span class="text-sm font-bold">Total</span>
                            <span class="text-lg font-extrabold text-primary" x-text="formatCurrency(total)"></span>
                        </div>
                    </div>
                </div>

                <!-- Checkout Button -->
                <div class="p-4 bg-white border-t border-slate-200">
                    <form action="/payment/checkout" method="POST">
                        @csrf
                        <input type="hidden" name="cart" x-model="JSON.stringify(cart)">
                        <input type="hidden" name="customer_id" id="customerId">
                        <button :disabled="cart.length === 0"
                            class="w-full py-4 bg-gradient-to-r from-primary to-primary-container text-white rounded-xl font-headline font-extrabold text-base shadow-lg shadow-primary/20 active:scale-[0.98] transition-transform flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined text-lg">payments</span>
                            <span>Process Checkout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ==================== PRODUCT LIST MODAL ==================== -->
        <div x-show="showProductModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showProductModal = false"></div>
            <div class="relative w-full max-w-5xl bg-surface-container-lowest rounded-2xl shadow-2xl overflow-hidden border border-white/20 flex flex-col max-h-[90vh]"
                x-data="productListModal()">
                <!-- Modal Header -->
                <div class="p-4 lg:p-6 border-b border-slate-100 flex justify-between items-center bg-white">
                    <div>
                        <h3 class="text-xl font-black text-primary">Pilih Produk</h3>
                        <p class="text-[10px] text-slate-500 mt-1 uppercase font-bold tracking-widest"
                            x-text="filteredProducts.length + ' produk tersedia'"></p>
                    </div>
                    <button @click="showProductModal = false"
                        class="w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-slate-400">close</span>
                    </button>
                </div>

                <!-- Search + Category Filter -->
                <div class="p-4 border-b border-slate-100 space-y-3">
                    <div class="relative">
                        <span
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined">search</span>
                        <input x-model="searchQuery" type="text" placeholder="Cari produk..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-100/50 border-none rounded-xl focus:ring-2 focus:ring-primary/10 transition-all text-sm outline-none" />
                    </div>
                    <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1">
                        <button @click="selectedCategory = ''"
                            :class="selectedCategory === '' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600'"
                            class="px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all">Semua</button>
                        <template x-for="cat in categories" :key="cat.id">
                            <button @click="selectedCategory = cat.id"
                                :class="selectedCategory === cat.id ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600'"
                                class="px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all"
                                x-text="cat.name"></button>
                        </template>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="flex-1 overflow-y-auto p-4 no-scrollbar">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div @click="addToCartFromModal(product)"
                                :class="product.stock === 0 ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:shadow-lg active:scale-[0.97]'"
                                class="bg-surface-container-lowest rounded-xl overflow-hidden transition-all duration-200 border border-transparent hover:border-primary/20">
                                <div
                                    class="relative h-28 overflow-hidden bg-slate-100 flex items-center justify-center">
                                    <template x-if="product.image">
                                        <img :src="'/storage/' + product.image" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!product.image">
                                        <span class="material-symbols-outlined text-slate-400 text-2xl">image</span>
                                    </template>
                                    <div class="absolute top-1.5 right-1.5">
                                        <template x-if="product.stock > 0">
                                            <span
                                                class="px-1.5 py-0.5 bg-surface-container-high/90 backdrop-blur text-[8px] font-bold rounded"
                                                x-text="'(' + product.stock + ')'"></span>
                                        </template>
                                        <template x-if="product.stock === 0">
                                            <span
                                                class="px-1.5 py-0.5 bg-error/90 text-white backdrop-blur text-[8px] font-bold rounded">Habis</span>
                                        </template>
                                    </div>
                                </div>
                                <div class="p-3">
                                    <h4 class="font-bold text-on-surface text-xs truncate" x-text="product.name"></h4>
                                    <p class="text-[10px] text-slate-400 truncate" x-text="product.sku"></p>
                                    <div class="mt-2">
                                        <template x-if="isPromoActive(product)">
                                            <div>
                                                <span class="font-extrabold text-error text-xs"
                                                    x-text="formatCurrency(product.promo_price)"></span>
                                                <span class="text-[9px] text-slate-400 line-through ml-1"
                                                    x-text="formatCurrency(product.selling_price)"></span>
                                            </div>
                                        </template>
                                        <template x-if="!isPromoActive(product)">
                                            <span class="font-extrabold text-primary text-xs"
                                                x-text="formatCurrency(product.selling_price)"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div x-show="filteredProducts.length === 0" class="text-center text-slate-400 text-sm py-10">
                        Tidak ada produk ditemukan.
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== CAMERA SCANNER MODAL ==================== -->
        <div x-show="showCameraScanner" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="closeCameraScanner()"></div>
            <div class="relative w-full max-w-lg bg-surface-container-lowest rounded-2xl shadow-2xl overflow-hidden border border-white/20 flex flex-col max-h-[90vh]"
                @click.outside="closeCameraScanner()">
                <!-- Modal Header -->
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white">
                    <div>
                        <h3 class="text-lg font-black text-primary">Scan Barcode</h3>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Arahkan kamera ke
                            barcode</p>
                    </div>
                    <button @click="closeCameraScanner()"
                        class="w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-slate-400">close</span>
                    </button>
                </div>

                <!-- Scanner Area -->
                <div class="p-4 space-y-4">
                    <!-- Camera Viewfinder -->
                    <div class="relative bg-black rounded-xl overflow-hidden aspect-square">
                        <div id="barcode-reader" class="w-full h-full"></div>

                        <!-- Scanning Overlay -->
                        <div x-show="!scannerActive"
                            class="absolute inset-0 flex flex-col items-center justify-center text-white">
                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">qr_code_scanner</span>
                            <p class="text-sm font-bold opacity-70">Klik "Start Camera" untuk mulai scan</p>
                        </div>

                        <!-- Scanning Indicator -->
                        <div x-show="scannerActive" class="absolute inset-0 pointer-events-none">
                            <div class="absolute inset-0 border-2 border-primary/50 rounded-xl"></div>
                            <div
                                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-32 border-2 border-primary rounded-lg">
                                <div
                                    class="absolute top-0 left-0 w-4 h-4 border-t-4 border-l-4 border-primary -mt-1 -ml-1">
                                </div>
                                <div
                                    class="absolute top-0 right-0 w-4 h-4 border-t-4 border-r-4 border-primary -mt-1 -mr-1">
                                </div>
                                <div
                                    class="absolute bottom-0 left-0 w-4 h-4 border-b-4 border-l-4 border-primary -mb-1 -ml-1">
                                </div>
                                <div
                                    class="absolute bottom-0 right-0 w-4 h-4 border-b-4 border-r-4 border-primary -mb-1 -mr-1">
                                </div>
                            </div>
                            <!-- Scan line animation -->
                            <div
                                class="absolute top-1/2 left-1/2 -translate-x-1/2 w-48 h-0.5 bg-primary/80 animate-pulse">
                            </div>
                        </div>
                    </div>

                    <!-- Status Message -->
                    <div x-show="scanMessage" class="p-3 rounded-xl text-center text-sm font-bold"
                        :class="scanSuccess ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'"
                        x-text="scanMessage"></div>

                    <!-- Controls -->
                    <div class="flex gap-3">
                        <button x-show="!scannerActive" @click="startScanner()"
                            class="flex-1 py-3 bg-gradient-to-r from-primary to-primary-container text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20 active:scale-[0.98] transition-transform flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">videocam</span>
                            Start Camera
                        </button>
                        <button x-show="scannerActive" @click="stopScanner()"
                            class="flex-1 py-3 bg-error text-white rounded-xl font-bold text-sm shadow-lg shadow-error/20 active:scale-[0.98] transition-transform flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">videocam_off</span>
                            Stop Camera
                        </button>
                    </div>

                    <!-- Manual Input Fallback -->
                    <div class="pt-2 border-t border-slate-100">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-2">Atau input manual
                        </p>
                        <div class="flex gap-2">
                            <input x-model="manualBarcode" @keydown.enter="scanManualBarcode()" type="text"
                                placeholder="Masukkan barcode..."
                                class="flex-1 px-3 py-2 bg-slate-100 border-none rounded-xl text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20" />
                            <button @click="scanManualBarcode()"
                                class="px-4 py-2 bg-primary text-white rounded-xl font-bold text-sm active:scale-95 transition-transform">
                                <span class="material-symbols-outlined text-lg">search</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    @if(isset($pendingCart) && !empty($pendingCart))
        <script>localStorage.setItem('pos_cart', JSON.stringify({!! json_encode($pendingCart) !!}));</script>
    @endif

    <script>
        const allProducts = {!! json_encode($products->map(function ($p) {
    return [
        'id' => $p->id,
        'name' => $p->name,
        'sku' => $p->sku,
        'barcode' => $p->barcode,
        'selling_price' => $p->selling_price,
        'promo_price' => $p->promo_price,
        'promo_start' => $p->promo_start,
        'promo_end' => $p->promo_end,
        'image' => $p->image,
        'stock' => $p->current_stock,
        'category_id' => $p->category_id,
    ];
})) !!};

        const allCategories = {!! json_encode($categories) !!};

        const allPromotions = {!! json_encode($promotions->map(function ($p) {
    return [
        'id' => $p->id,
        'name' => $p->name,
        'type' => $p->type,
        'description' => $p->description,
        'discount_percentage' => $p->discount_percentage,
        'discount_nominal' => $p->discount_nominal,
        'min_purchase_amount' => $p->min_purchase_amount,
        'products' => $p->products,
        'product_id' => $p->product_id,
        'category_id' => $p->category_id,
        'buy_product_id' => $p->buy_product_id,
        'get_product_id' => $p->get_product_id,
        'buy_quantity' => $p->buy_quantity,
        'get_quantity' => $p->get_quantity,
        'bundle_price' => $p->bundle_price,
        'is_active' => $p->is_active,
    ];
})) !!};

        document.addEventListener('alpine:init', () => {
            Alpine.data('posCart', () => ({
                cart: JSON.parse(localStorage.getItem('pos_cart')) || [],
                showProductModal: false,
                showCheckoutModal: false,
                scanQuery: '',

                // Camera scanner
                showCameraScanner: false,
                scanner: null,
                scannerActive: false,
                scanMessage: '',
                scanSuccess: false,
                manualBarcode: '',
                lastScannedBarcode: '',
                scanCooldown: false,

                init() {
                    document.getElementById('scanInput')?.focus();
                },

                currencyCode: '{{ $currency }}',
                serviceRate: {{ $service_charge }},
                promotions: allPromotions,

                openCheckoutModal() { this.showCheckoutModal = true; },

                get applicablePromos() {
                    if (this.cart.length === 0) return [];
                    const results = [];
                    const cartIds = this.cart.map(i => i.id);
                    const cartQtys = {};
                    this.cart.forEach(i => { cartQtys[i.id] = (cartQtys[i.id] || 0) + i.quantity; });

                    for (const promo of this.promotions) {
                        if (!promo.is_active) continue;
                        let message = '';
                        let subtext = '';
                        let icon = 'sell';

                        switch (promo.type) {
                            case 'bundle':
                                if (promo.products && promo.products.length > 0) {
                                    const allInCart = promo.products.every(pid => cartIds.includes(parseInt(pid)));
                                    if (allInCart) {
                                        const productNames = promo.products.map(pid => {
                                            const p = allProducts.find(ap => ap.id === parseInt(pid));
                                            return p ? p.name : 'Produk';
                                        });
                                        message = `Bundle: ${productNames.join(' + ')}`;
                                        const sets = Math.min(...promo.products.map(pid => cartQtys[parseInt(pid)] || 0));
                                        subtext = `Hemat! Harga spesial untuk ${sets} set`;
                                        icon = 'inventory_2';
                                    }
                                }
                                break;
                            case 'buy_x_get_y':
                                if (promo.buy_product_id && cartIds.includes(parseInt(promo.buy_product_id))) {
                                    const buyProduct = allProducts.find(p => p.id === parseInt(promo.buy_product_id));
                                    const getProduct = promo.get_product_id ? allProducts.find(p => p.id === parseInt(promo.get_product_id)) : buyProduct;
                                    message = `Beli ${promo.buy_quantity || 1} ${buyProduct ? buyProduct.name : 'Produk'} Gratis ${promo.get_quantity || 1} ${getProduct ? getProduct.name : 'Produk'}`;
                                    const isSelfBogo = !promo.get_product_id || parseInt(promo.get_product_id) === parseInt(promo.buy_product_id);
                                    const divisor = isSelfBogo ? (promo.buy_quantity || 1) + (promo.get_quantity || 1) : (promo.buy_quantity || 1);
                                    const eligibleSets = Math.floor(cartQtys[parseInt(promo.buy_product_id)] / divisor);
                                    subtext = eligibleSets > 0 ? `Kamu bisa dapat ${eligibleSets * (promo.get_quantity || 1)} gratis!` : `Tambahkan lagi untuk klaim`;
                                    icon = 'redeem';
                                }
                                break;
                            case 'percentage':
                                if (this.subtotal >= (promo.min_purchase_amount || 0)) {
                                    message = `Diskon ${promo.discount_percentage}%`;
                                    subtext = `Belanja min. Rp ${(promo.min_purchase_amount || 0).toLocaleString('id-ID')}`;
                                    icon = 'percent';
                                }
                                break;
                            case 'nominal':
                                if (this.subtotal >= (promo.min_purchase_amount || 0)) {
                                    message = `Potongan Rp ${(promo.discount_nominal || 0).toLocaleString('id-ID')}`;
                                    subtext = `Belanja min. Rp ${(promo.min_purchase_amount || 0).toLocaleString('id-ID')}`;
                                    icon = 'discount';
                                }
                                break;
                            case 'min_purchase':
                                if (this.subtotal >= (promo.min_purchase_amount || 0)) {
                                    message = promo.discount_nominal ? `Potongan Rp ${(promo.discount_nominal || 0).toLocaleString('id-ID')}` : `Diskon ${promo.discount_percentage}%`;
                                    subtext = 'Sudah tercapai!';
                                    icon = 'check_circle';
                                } else {
                                    message = `Belanja Rp ${((promo.min_purchase_amount || 0) - this.subtotal).toLocaleString('id-ID')} lagi untuk dapat promo`;
                                    subtext = promo.discount_nominal ? `Potongan Rp ${(promo.discount_nominal || 0).toLocaleString('id-ID')}` : `Diskon ${promo.discount_percentage}%`;
                                    icon = 'shopping_bag';
                                }
                                break;
                            case 'category':
                                if (promo.category_id) {
                                    const catProducts = allProducts.filter(p => p.category_id === promo.category_id);
                                    if (catProducts.some(p => cartIds.includes(p.id))) {
                                        message = promo.discount_nominal ? `Potongan Rp ${(promo.discount_nominal || 0).toLocaleString('id-ID')} untuk produk kategori ini` : `Diskon ${promo.discount_percentage}% untuk produk kategori ini`;
                                        subtext = promo.name;
                                        icon = 'category';
                                    }
                                }
                                break;
                            case 'product':
                                if (promo.product_id && cartIds.includes(parseInt(promo.product_id))) {
                                    const prod = allProducts.find(p => p.id === parseInt(promo.product_id));
                                    message = promo.discount_nominal ? `Potongan Rp ${(promo.discount_nominal || 0).toLocaleString('id-ID')} untuk ${prod ? prod.name : 'Produk'}` : `Diskon ${promo.discount_percentage}% untuk ${prod ? prod.name : 'Produk'}`;
                                    subtext = promo.name;
                                    icon = 'sell';
                                }
                                break;
                            case 'time_based':
                                if (this.subtotal >= (promo.min_purchase_amount || 0)) {
                                    message = promo.name;
                                    subtext = 'Berlaku pada waktu tertentu';
                                    icon = 'schedule';
                                }
                                break;
                            case 'member':
                                if (document.getElementById('customerId')?.value) {
                                    message = `Promo Member: Diskon ${promo.discount_percentage}%`;
                                    subtext = 'Nikmati diskon khusus member';
                                    icon = 'loyalty';
                                }
                                break;
                        }

                        if (message) results.push({ id: promo.id, message, subtext, icon });
                    }
                    return results;
                },

                get subtotal() { return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0); },
                get totalQty() { return this.cart.reduce((sum, item) => sum + item.quantity, 0); },
                get serviceCharge() { return this.subtotal * (this.serviceRate / 100); },
                get total() { return this.subtotal + this.serviceCharge; },

                formatCurrency(amount) {
                    return new Intl.NumberFormat(this.currencyCode, { style: 'currency', currency: 'IDR' }).format(amount);
                },

                saveCart() { localStorage.setItem('pos_cart', JSON.stringify(this.cart)); },

                scanProduct() {
                    const query = this.scanQuery.trim();
                    if (!query) return;
                    const product = allProducts.find(p =>
                        p.sku.toLowerCase() === query.toLowerCase() ||
                        (p.barcode && p.barcode.toLowerCase() === query.toLowerCase())
                    );
                    if (product) {
                        this.addToCart(product);
                        this.scanQuery = '';
                    } else {
                        Swal.fire({ icon: 'warning', title: 'Produk Tidak Ditemukan', text: 'SKU/Barcode "' + query + '" tidak ditemukan.', confirmButtonColor: '#3085d6' });
                        this.scanQuery = '';
                    }
                    document.getElementById('scanInput')?.focus();
                },

                addToCart(product) {
                    let existing = this.cart.find(i => i.id == product.id);
                    if (existing) {
                        if (existing.quantity < product.stock) {
                            existing.quantity = parseInt(existing.quantity) + 1;
                        } else {
                            Swal.fire({ icon: 'warning', title: 'Stok Tidak Cukup', text: 'Stok produk tidak mencukupi', confirmButtonColor: '#3085d6' });
                        }
                    } else {
                        let productPrice = product.selling_price;
                        if (product.promo_price && product.promo_price > 0) {
                            const today = new Date().toISOString().split('T')[0];
                            const start = product.promo_start || today;
                            const end = product.promo_end || today;
                            if (today >= start && today <= end) productPrice = product.promo_price;
                        }
                        this.cart.push({ id: product.id, name: product.name, price: productPrice, image: product.image, stock: product.stock, quantity: 1 });
                    }
                    this.saveCart();
                },

                increaseQuantity(id) {
                    let item = this.cart.find(i => i.id == id);
                    if (item) {
                        let currentQty = parseInt(item.quantity) || 0;
                        let max = parseInt(item.stock) || 9999;
                        if (currentQty < max) item.quantity = currentQty + 1;
                        else Swal.fire({ icon: 'warning', title: 'Stok Tidak Cukup', text: 'Stok produk tidak mencukupi', confirmButtonColor: '#3085d6' });
                        this.saveCart();
                    }
                },

                decreaseQuantity(id) {
                    let item = this.cart.find(i => i.id == id);
                    if (item) {
                        item.quantity = parseInt(item.quantity) - 1;
                        if (item.quantity <= 0) this.cart = this.cart.filter(i => i.id != id);
                        this.saveCart();
                    }
                },

                validateQuantity(id, maxStock) {
                    let item = this.cart.find(i => i.id == id);
                    if (item) {
                        let qty = parseInt(item.quantity);
                        if (isNaN(qty) || qty < 1) qty = 1;
                        if (qty > maxStock) qty = maxStock;
                        item.quantity = qty;
                        this.saveCart();
                    }
                },

                clearCart() { this.cart = []; this.saveCart(); },

                // Camera Scanner Methods
                openCameraScanner() {
                    this.showCameraScanner = true;
                    this.scanMessage = '';
                    this.scanSuccess = false;
                    this.manualBarcode = '';
                    this.lastScannedBarcode = '';
                    this.scanCooldown = false;
                },

                closeCameraScanner() {
                    this.stopScanner();
                    this.showCameraScanner = false;
                    this.lastScannedBarcode = '';
                    this.scanCooldown = false;
                },

                async startScanner() {
                    this.scanMessage = '';
                    this.scanSuccess = false;

                    if (!window.Html5Qrcode) {
                        this.scanMessage = 'Library scanner tidak tersedia';
                        return;
                    }

                    try {
                        this.scanner = new Html5Qrcode('barcode-reader');

                        const devices = await Html5Qrcode.getCameras();
                        if (!devices || devices.length === 0) {
                            this.scanMessage = 'Tidak ada kamera ditemukan';
                            return;
                        }

                        // Prefer back camera
                        const backCamera = devices.find(d => d.label.toLowerCase().includes('back')) || devices[0];

                        await this.scanner.start(
                            backCamera.id,
                            {
                                fps: 10,
                                qrbox: { width: 200, height: 120 },
                                aspectRatio: 1.0,
                            },
                            (decodedText) => {
                                this.onScanSuccess(decodedText);
                            },
                            (errorMessage) => {
                                // Ignore scan errors (normal when no barcode in view)
                            }
                        );

                        this.scannerActive = true;
                    } catch (err) {
                        console.error('Scanner error:', err);
                        if (err.name === 'NotAllowedError') {
                            this.scanMessage = 'Izin kamera ditolak. Harap izinkan akses kamera.';
                        } else {
                            this.scanMessage = 'Gagal memulai kamera: ' + err.message;
                        }
                    }
                },

                stopScanner() {
                    if (this.scanner && this.scannerActive) {
                        this.scanner.stop().then(() => {
                            this.scanner.clear();
                            this.scanner = null;
                            this.scannerActive = false;
                        }).catch(err => {
                            console.error('Stop scanner error:', err);
                            this.scannerActive = false;
                        });
                    }
                },

                onScanSuccess(decodedText) {
                    if (this.scanCooldown) return;

                    this.scanCooldown = true;
                    setTimeout(() => { this.scanCooldown = false; }, 1500);

                    if (decodedText === this.lastScannedBarcode) return;
                    this.lastScannedBarcode = decodedText;

                    if (this.scanner && this.scannerActive) {
                        this.scanner.pause();
                    }

                    this.playBeep();

                    const product = allProducts.find(p =>
                        (p.barcode && p.barcode === decodedText) ||
                        p.sku === decodedText
                    );

                    if (product) {
                        this.scanMessage = `✓ ${product.name}`;
                        this.scanSuccess = true;

                        this.$dispatch('add-to-cart', product);

                        if (navigator.vibrate) {
                            navigator.vibrate(100);
                        }

                        setTimeout(() => {
                            this.closeCameraScanner();
                        }, 800);
                    } else {
                        this.scanMessage = '✗ Produk tidak ditemukan';
                        this.scanSuccess = false;

                        if (navigator.vibrate) {
                            navigator.vibrate([100, 50, 100]);
                        }

                        if (this.scanner && this.scannerActive) {
                            this.scanner.resume();
                        }
                    }
                },

                scanManualBarcode() {
                    if (!this.manualBarcode.trim()) return;
                    this.onScanSuccess(this.manualBarcode.trim());
                    this.manualBarcode = '';
                },

                playBeep() {
                    try {
                        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();

                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);

                        oscillator.frequency.value = 1200;
                        oscillator.type = 'sine';

                        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);

                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + 0.15);
                    } catch (e) {
                        // Ignore audio errors
                    }
                },

                async savePendingOrder() {
                    if (this.cart.length === 0) return;
                    try {
                        let res = await fetch('/pending-orders', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ cart: JSON.stringify(this.cart) })
                        });
                        let data = await res.json();
                        if (data.success) {
                            this.clearCart();
                            Swal.fire({ icon: 'success', title: 'Order Tersimpan', text: `Order ${data.order.order_number} tersimpan! Berlaku ${data.order.remaining_minutes} menit.`, confirmButtonColor: '#3085d6' }).then(() => { window.location.href = '/pending-orders'; });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: data.error || 'Gagal menyimpan order', confirmButtonColor: '#3085d6' });
                        }
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan: ' + e.message, confirmButtonColor: '#3085d6' });
                    }
                }
            }));

            Alpine.data('productListModal', () => ({
                searchQuery: '',
                selectedCategory: '',
                categories: allCategories,
                products: allProducts,

                get filteredProducts() {
                    let result = this.products;
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        result = result.filter(p =>
                            p.name.toLowerCase().includes(q) ||
                            p.sku.toLowerCase().includes(q) ||
                            (p.barcode && p.barcode.toLowerCase().includes(q))
                        );
                    }
                    if (this.selectedCategory) result = result.filter(p => p.category_id == this.selectedCategory);
                    return result;
                },

                isPromoActive(product) {
                    if (!product.promo_price || product.promo_price <= 0) return false;
                    const today = new Date().toISOString().split('T')[0];
                    return today >= (product.promo_start || today) && today <= (product.promo_end || today);
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
                },

                addToCartFromModal(product) {
                    if (product.stock === 0) return;
                    this.$dispatch('add-to-cart', product);
                    this.showProductModal = false;
                }
            }));
        });

        // Desktop Member search
        const memberSearch = document.getElementById('memberSearch');
        const memberDropdown = document.getElementById('memberDropdown');
        const customerIdInput = document.getElementById('customerId');
        const selectedMemberDiv = document.getElementById('selectedMember');
        const selectedMemberName = document.getElementById('selectedMemberName');
        let searchTimeout;

        if (memberSearch) {
            memberSearch.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                const query = this.value;
                if (query.length < 2) { memberDropdown.classList.add('hidden'); return; }
                searchTimeout = setTimeout(() => {
                    fetch('/customers/search?q=' + encodeURIComponent(query))
                        .then(res => res.json())
                        .then(data => {
                            if (data.length === 0) {
                                memberDropdown.innerHTML = '<div class="p-3 text-xs text-slate-500">Tidak ditemukan</div>';
                            } else {
                                memberDropdown.innerHTML = data.map(c => `
                                    <div class="p-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0"
                                        onclick="selectMember(${c.id}, '${c.name}', '${c.phone}')">
                                        <div class="text-xs font-bold text-on-surface">${c.name}</div>
                                        <div class="text-[10px] text-slate-500">${c.phone} • ${c.tier.toUpperCase()} (${c.available_points} pts)</div>
                                    </div>
                                `).join('');
                            }
                            memberDropdown.classList.remove('hidden');
                        });
                }, 300);
            });
            document.addEventListener('click', function (e) {
                if (!memberSearch.contains(e.target) && !memberDropdown.contains(e.target)) { memberDropdown.classList.add('hidden'); }
            });
        }

        // Mobile Member search
        const mobileMemberSearch = document.getElementById('mobileMemberSearch');
        const mobileMemberDropdown = document.getElementById('mobileMemberDropdown');
        const mobileSelectedMemberDiv = document.getElementById('mobileSelectedMember');
        const mobileSelectedMemberName = document.getElementById('mobileSelectedMemberName');
        const mobileMemberName = document.getElementById('mobileMemberName');
        let mobileSearchTimeout;

        if (mobileMemberSearch) {
            mobileMemberSearch.addEventListener('input', function () {
                clearTimeout(mobileSearchTimeout);
                const query = this.value;
                if (query.length < 2) { mobileMemberDropdown.classList.add('hidden'); return; }
                mobileSearchTimeout = setTimeout(() => {
                    fetch('/customers/search?q=' + encodeURIComponent(query))
                        .then(res => res.json())
                        .then(data => {
                            if (data.length === 0) {
                                mobileMemberDropdown.innerHTML = '<div class="p-3 text-xs text-slate-500">Tidak ditemukan</div>';
                            } else {
                                mobileMemberDropdown.innerHTML = data.map(c => `
                                    <div class="p-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0"
                                        onclick="selectMobileMember(${c.id}, '${c.name}', '${c.phone}')">
                                        <div class="text-xs font-bold text-on-surface">${c.name}</div>
                                        <div class="text-[10px] text-slate-500">${c.phone} • ${c.tier.toUpperCase()}</div>
                                    </div>
                                `).join('');
                            }
                            mobileMemberDropdown.classList.remove('hidden');
                        });
                }, 300);
            });
            document.addEventListener('click', function (e) {
                if (!mobileMemberSearch.contains(e.target) && !mobileMemberDropdown.contains(e.target)) { mobileMemberDropdown.classList.add('hidden'); }
            });
        }

        function selectMember(id, name, phone) {
            customerIdInput.value = id;
            memberSearch.value = `${name} (${phone})`;
            memberSearch.disabled = true;
            selectedMemberName.textContent = name;
            selectedMemberDiv.classList.remove('hidden');
            memberDropdown.classList.add('hidden');
            if (mobileMemberName) mobileMemberName.textContent = name;
        }

        function selectMobileMember(id, name, phone) {
            customerIdInput.value = id;
            mobileMemberSearch.value = `${name} (${phone})`;
            mobileMemberSearch.disabled = true;
            mobileSelectedMemberName.textContent = name;
            document.getElementById('mobileSelectedMemberTier').textContent = phone;
            mobileSelectedMemberDiv.classList.remove('hidden');
            mobileMemberDropdown.classList.add('hidden');
            if (mobileMemberName) mobileMemberName.textContent = name;
        }

        function clearMember() {
            customerIdInput.value = '';
            memberSearch.value = '';
            memberSearch.disabled = false;
            selectedMemberDiv.classList.add('hidden');
            if (mobileMemberName) mobileMemberName.textContent = '';
        }

        function clearMobileMember() {
            customerIdInput.value = '';
            mobileMemberSearch.value = '';
            mobileMemberSearch.disabled = false;
            mobileSelectedMemberDiv.classList.add('hidden');
            if (mobileMemberName) mobileMemberName.textContent = '';
        }

        async function addNewMember(name, phone, email) {
            try {
                let res = await fetch('/customers/quick-register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name, phone, email })
                });
                let data = await res.json();
                if (data.success) {
                    selectMember(data.customer.id, data.customer.name, data.customer.phone);
                    document.getElementById('addMemberModal').classList.add('hidden');
                    document.getElementById('newMemberName').value = '';
                    document.getElementById('newMemberPhone').value = '';
                    document.getElementById('newMemberEmail').value = '';
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Member berhasil ditambahkan!', confirmButtonColor: '#3085d6' });
                } else { Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal menambahkan member', confirmButtonColor: '#3085d6' }); }
            } catch (e) { Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan: ' + e.message, confirmButtonColor: '#3085d6' }); }
        }
    </script>

    <!-- Add Member Modal -->
    <div id="addMemberModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
            onclick="document.getElementById('addMemberModal').classList.add('hidden')"></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-md bg-surface-container-lowest rounded-2xl shadow-2xl p-6 border border-outline-variant/20">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-on-surface">Tambah Member Baru</h3>
                <button onclick="document.getElementById('addMemberModal').classList.add('hidden')"
                    class="text-slate-400 hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Nama</label>
                    <input id="newMemberName" type="text"
                        class="w-full bg-surface-container border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 outline-none"
                        placeholder="Nama lengkap">
                </div>
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">No. HP</label>
                    <input id="newMemberPhone" type="tel"
                        class="w-full bg-surface-container border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 outline-none"
                        placeholder="0812xxxx">
                </div>
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Email
                        (Opsional)</label>
                    <input id="newMemberEmail" type="email"
                        class="w-full bg-surface-container border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 outline-none"
                        placeholder="email@example.com">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addMemberModal').classList.add('hidden')"
                        class="flex-1 py-3 bg-surface-container text-on-surface font-bold rounded-lg hover:bg-surface-dim">Batal</button>
                    <button type="button"
                        onclick="const name=document.getElementById('newMemberName').value; const phone=document.getElementById('newMemberPhone').value; const email=document.getElementById('newMemberEmail').value; if(name&&phone){addNewMember(name,phone,email)}else{Swal.fire({ icon: 'warning', title: 'Wajib Diisi', text: 'Nama dan No. HP wajib diisi', confirmButtonColor: '#3085d6' })}"
                        class="flex-1 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary-container">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</x-layout>