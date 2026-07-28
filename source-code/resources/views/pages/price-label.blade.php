<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full">
        <header class="bg-white/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm">
            <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
                <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900">Cetak Label Harga</h1>
            </div>
        </header>

        <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
            <div class="max-w-5xl mx-auto">

                @if(session('error') || $errors->any())
                <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6 flex items-start gap-3">
                    <span class="material-symbols-outlined text-red-500 flex-shrink-0 mt-0.5">error</span>
                    <div>
                        <p class="text-sm font-bold text-red-700">{{ session('error') ?: $errors->first() }}</p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-red-400 hover:text-red-600 flex-shrink-0">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
                @endif

                <!-- Filter & Search -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-6">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1 relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-lg text-slate-400">search</span>
                            <input id="searchInput" value="{{ request('search') }}"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/10 text-sm outline-none"
                                placeholder="Cari nama produk, SKU, atau barcode..." />
                        </div>
                        <select id="categoryFilter" class="py-2.5 px-4 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/10 outline-none">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <button id="searchBtn"
                            class="px-6 py-2.5 bg-primary text-white font-bold rounded-xl hover:bg-primary/90 transition-all active:scale-95 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">search</span>
                            Cari
                        </button>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 mt-3 pt-3 border-t border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-500">Harga:</span>
                            <input id="minPrice" type="number" value="{{ request('min_price') }}"
                                class="w-24 py-2 px-3 bg-slate-50 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary/10 outline-none"
                                placeholder="Min" min="0" />
                            <span class="text-slate-300">—</span>
                            <input id="maxPrice" type="number" value="{{ request('max_price') }}"
                                class="w-24 py-2 px-3 bg-slate-50 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary/10 outline-none"
                                placeholder="Maks" min="0" />
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-500">Promo:</span>
                            <input id="minPromo" type="number" value="{{ request('min_promo_price') }}"
                                class="w-24 py-2 px-3 bg-slate-50 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary/10 outline-none"
                                placeholder="Min" min="0" />
                            <span class="text-slate-300">—</span>
                            <input id="maxPromo" type="number" value="{{ request('max_promo_price') }}"
                                class="w-24 py-2 px-3 bg-slate-50 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary/10 outline-none"
                                placeholder="Maks" min="0" />
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input id="promoOnly" type="checkbox" {{ request()->boolean('promo_only') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20" />
                            <span class="text-xs font-bold text-slate-500">Promo saja</span>
                        </label>
                    </div>
                </div>

                <!-- Controls -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold text-slate-600">Jumlah Cetak per Produk:</span>
                            <select id="copiesSelect" class="py-2 px-3 bg-slate-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-primary/10 outline-none">
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>{{ $i }}x</option>
                                @endfor
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="selectedCount" class="text-sm font-bold text-slate-600">0 produk dipilih</span>
                            <button id="printBtn" disabled
                                class="px-6 py-2.5 bg-primary text-white font-bold rounded-xl hover:bg-primary/90 transition-all active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">print</span>
                                Cetak Label
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div id="productsContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($products as $product)
                    <label class="product-card bg-white rounded-xl border-2 border-slate-200 p-4 cursor-pointer transition-all hover:border-primary/30 hover:shadow-md has-[:checked]:border-primary has-[:checked]:bg-primary/5 has-[:checked]:shadow-lg has-[:checked]:shadow-primary/10">
                        <input type="checkbox" value="{{ $product->id }}" class="product-checkbox hidden" />
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1 min-w-0 mr-2">
                                <p class="text-sm font-extrabold text-slate-800 truncate">{{ $product->name }}</p>
                                @if($product->sku)
                                    <p class="text-[10px] font-mono text-slate-400 mt-0.5">SKU: {{ $product->sku }}</p>
                                @endif
                            </div>
                            <div class="w-5 h-5 rounded border-2 border-slate-300 flex-shrink-0 flex items-center justify-center has-[:checked]:bg-primary has-[:checked]:border-primary transition-all">
                                <span class="material-symbols-outlined text-white text-sm has-[:checked]:block hidden" style="font-variation-settings:'FILL' 1;">check</span>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            @if($product->isPromoActive())
                                <span class="text-lg font-black text-red-600">Rp{{ number_format($product->getCurrentPrice(), 0, ',', '.') }}</span>
                                <span class="text-xs text-slate-400 line-through">Rp{{ number_format($product->selling_price, 0, ',', '.') }}</span>
                                <span class="text-[10px] font-bold text-white bg-red-500 px-1.5 py-0.5 rounded">-{{ $product->getDiscountPercentage() }}%</span>
                            @else
                                <span class="text-lg font-black text-slate-800">Rp{{ number_format($product->selling_price, 0, ',', '.') }}</span>
                            @endif
                        </div>
                        @if($product->barcode)
                            <p class="text-[10px] font-mono text-slate-400 mt-1">Barcode: {{ $product->barcode }}</p>
                        @endif
                    </label>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $products->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            const selectedCount = document.getElementById('selectedCount');
            const printBtn = document.getElementById('printBtn');
            const copiesSelect = document.getElementById('copiesSelect');
            const searchBtn = document.getElementById('searchBtn');
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const minPrice = document.getElementById('minPrice');
            const maxPrice = document.getElementById('maxPrice');
            const minPromo = document.getElementById('minPromo');
            const maxPromo = document.getElementById('maxPromo');
            const promoOnly = document.getElementById('promoOnly');

            function updateSelected() {
                const checked = document.querySelectorAll('.product-checkbox:checked');
                const count = checked.length;
                selectedCount.textContent = count + ' produk dipilih';
                printBtn.disabled = count === 0;
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateSelected);
            });

            printBtn.addEventListener('click', function () {
                const ids = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);
                if (ids.length === 0) return;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/price-label/print';
                form.target = '_blank';

                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = '{{ csrf_token() }}';
                form.appendChild(token);

                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'product_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                const copies = document.createElement('input');
                copies.type = 'hidden';
                copies.name = 'copies';
                copies.value = copiesSelect.value;
                form.appendChild(copies);

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            });

            searchBtn.addEventListener('click', function () {
                const params = new URLSearchParams(window.location.search);
                if (searchInput.value) params.set('search', searchInput.value);
                else params.delete('search');
                if (categoryFilter.value) params.set('category_id', categoryFilter.value);
                else params.delete('category_id');
                if (minPrice.value) params.set('min_price', minPrice.value);
                else params.delete('min_price');
                if (maxPrice.value) params.set('max_price', maxPrice.value);
                else params.delete('max_price');
                if (minPromo.value) params.set('min_promo_price', minPromo.value);
                else params.delete('min_promo_price');
                if (maxPromo.value) params.set('max_promo_price', maxPromo.value);
                else params.delete('max_promo_price');
                params.set('promo_only', promoOnly.checked ? '1' : '0');
                params.delete('page');
                window.location.search = params.toString();
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') searchBtn.click();
            });

            categoryFilter.addEventListener('change', function () {
                searchBtn.click();
            });
        });
    </script>
</x-layout>
