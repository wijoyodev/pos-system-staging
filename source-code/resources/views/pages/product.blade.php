<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>

  <main class="flex-1 flex flex-col min-h-screen relative w-full">
    <!-- Top Bar -->
    <header
      class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Products</h1>
        <form method="GET" action="/product" class="relative group">
          <span
            class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-lg text-slate-400">search</span>
          <input name="search" value="{{ request('search') }}"
            class="w-full pl-10 pr-12 py-2.5 bg-slate-100/50 border-none rounded-xl focus:ring-2 focus:ring-primary/10 transition-all text-sm outline-none"
            placeholder="Quick search..." type="text" />
        </form>
        <!-- <div class="flex items-center gap-3 lg:gap-4">
          <button class="p-2 text-slate-500 hover:bg-blue-50 transition-colors rounded-full relative">
            <span class="material-symbols-outlined text-lg lg:text-xl">notifications</span>
            @if($low_stock_count > 0)
              <span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
            @endif
          </button>
        </div> -->
      </div>
    </header>

    <!-- Canvas -->
    <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
      @if($errors->any())
      <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
        <p class="text-sm font-bold text-red-700">{{ $errors->first() }}</p>
      </div>
      @endif
      <!-- Report Header Section -->
      <div class="mb-6 lg:mb-8">
        <x-report-header title="Product Catalog" module="Inventory" submodule="Catalog Management" description="Manage your entire product catalog, pricing, and stock levels.">
          <x-slot name="actions">
            @if(auth()->user()->isSuperAdmin())
            <button onclick="document.getElementById('addProductModal').classList.remove('hidden')"
              class="flex items-center px-4 lg:px-5 py-2 lg:py-2.5 bg-primary text-white font-bold rounded-lg shadow-md hover:bg-primary-container active:scale-95 transition-all text-xs lg:text-sm cursor-pointer">
              <span class="material-symbols-outlined mr-1 lg:mr-2 text-base lg:text-lg">add</span>
              Add Product
            </button>
            <button onclick="document.getElementById('promoImportModal').classList.remove('hidden')"
              class="flex items-center px-4 lg:px-5 py-2 lg:py-2.5 bg-green-600 text-white font-bold rounded-lg shadow-md hover:bg-green-700 active:scale-95 transition-all text-xs lg:text-sm cursor-pointer">
              <span class="material-symbols-outlined mr-1 lg:mr-2 text-base lg:text-lg">sell</span>
              Import Promo
            </button>
            @endif
          </x-slot>
        </x-report-header>
      </div>
        <!-- Dashboard Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 mb-6 lg:mb-8">
          <div
            class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
            <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total
              Products</span>
            <div class="flex items-baseline gap-2">
              <span class="text-lg lg:text-2xl font-extrabold text-blue-900 font-headline">{{ $totalProducts }}</span>
            </div>
          </div>
          <div
            class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
            <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total
              Value</span>
            <div class="flex items-baseline gap-2">
              <span class="text-lg lg:text-2xl font-extrabold text-blue-900 font-headline">Rp
                {{ number_format($totalValue, 0, ',', '.') }}</span>
            </div>
          </div>
          <div
            class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
            <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Units in
              Stock</span>
            <div class="flex items-baseline gap-2">
              <span
                class="text-lg lg:text-2xl font-extrabold text-blue-900 font-headline">{{ number_format($totalUnits) }}</span>
            </div>
          </div>
          <div
            class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
            <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Low
              Stock</span>
            <div class="flex items-baseline gap-2">
              <span class="text-lg lg:text-2xl font-extrabold text-error font-headline">{{ $low_stock_count }}</span>
              @if($low_stock_count > 0)
                <span
                  class="px-1.5 lg:px-2 py-0.5 bg-error-container text-[8px] lg:text-[10px] font-bold text-on-error-container rounded uppercase">Action</span>
              @endif
            </div>
          </div>
        </div>

        <!-- Filters & Table Section -->
        <div
          class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] overflow-hidden">
          <!-- Table Controls -->
          <div
            class="p-4 lg:p-6 bg-surface-container-low/30 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3 lg:gap-4">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2 sm:pb-0">
              <a href="/product"
                class="px-3 lg:px-4 py-1.5 lg:py-2 {{ !request('category_id') ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-slate-100' }} text-xs font-bold rounded-full whitespace-nowrap transition-all">All</a>
              @foreach($categories as $cat)
                <a href="/product?category_id={{ $cat->id }}"
                  class="px-3 lg:px-4 py-1.5 lg:py-2 {{ request('category_id') == $cat->id ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-slate-100' }} text-xs font-bold rounded-full whitespace-nowrap transition-all">{{ $cat->name }}</a>
              @endforeach
            </div>
          </div>

          <!-- Performance Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-surface-container-low/50">
                  <th
                    class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">
                    Product Info
                  </th>
                  <th
                    class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden sm:table-cell">
                    SKU</th>
                    <th
                    class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden md:table-cell">
                    Price</th>
                  <th
                    class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden md:table-cell">
                    Expired</th>
                  <th
                    class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden lg:table-cell">
                    Stock
                  </th>
                  <th
                    class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">
                    Act</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                @forelse($products as $product)
                  <tr class="hover:bg-blue-50/30 transition-colors group">
                    <td class="px-3 lg:px-6 py-3 lg:py-5">
                      <div class="flex items-center gap-3 lg:gap-4">
                        <div class="w-10 lg:w-12 h-10 lg:h-12 rounded-lg bg-slate-100 flex-shrink-0 overflow-hidden">
                          @if($product->image)
                            <img class="w-full h-full object-cover" src="{{ asset('storage/' . $product->image) }}" />
                          @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400">
                              <span class="material-symbols-outlined text-lg lg:text-xl">image</span>
                            </div>
                          @endif
                        </div>
                        <div>
                          <div class="text-xs lg:text-sm font-bold text-on-surface">{{ $product->name }}</div>
                          <div class="text-[10px] lg:text-[11px] text-slate-400 font-medium">
                            {{ $product->category->name ?? 'None' }}{{ $product->unit ? ' · '.$product->unit : '' }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td
                      class="px-3 lg:px-6 py-3 lg:py-5 font-mono text-[10px] lg:text-xs font-semibold text-slate-500 hidden sm:table-cell">
                      <div class="flex flex-col">
                        <span>{{ $product->sku }}</span>
                        @if($product->barcode)
                          <span class="text-[9px] text-slate-400">{{ $product->barcode }}</span>
                        @endif
                      </div>
                    </td>
                    <td class="px-3 lg:px-6 py-3 lg:py-5 hidden md:table-cell">
                      <div class="flex flex-col">
                        @if($product->isPromoActive())
                          <span class="text-xs lg:text-sm font-bold text-error">
                            Rp{{ number_format($product->promo_price, 0, ',', '.') }}
                            <span class="text-[10px] text-error ml-1">-{{ $product->getDiscountPercentage() }}%</span>
                          </span>
                          <span class="text-[8px] lg:text-[10px] text-slate-400 line-through">
                            Rp{{ number_format($product->selling_price, 0, ',', '.') }}
                          </span>
                        @else
                          <span class="text-xs lg:text-sm font-bold text-blue-900">
                            Rp{{ number_format($product->selling_price, 0, ',', '.') }}</span>
                        @endif
                      </div>
                    </td>
                    @php
                      $expiryDate = $product->getEarliestExpiryDate();
                      $isExpired = $expiryDate && \Carbon\Carbon::parse($expiryDate)->isPast();
                      $isNear = $expiryDate && \Carbon\Carbon::parse($expiryDate)->isFuture() && \Carbon\Carbon::parse($expiryDate)->diffInDays(now()) <= 30;
                    @endphp
                    <td class="px-3 lg:px-6 py-3 lg:py-5 hidden md:table-cell">
                      @if($expiryDate)
                        <span class="text-xs font-bold px-2 py-0.5 rounded {{ $isExpired ? 'bg-red-100 text-red-700' : ($isNear ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                          {{ \Carbon\Carbon::parse($expiryDate)->format('d/m/Y') }}
                          @if($isExpired) <span class="text-[9px]">EXP</span> @endif
                        </span>
                      @else
                        <span class="text-xs text-slate-400">-</span>
                      @endif
                    </td>
                    <td class="px-3 lg:px-6 py-3 lg:py-5 hidden lg:table-cell">
                      @php
                        $stockQty = $storeId ? $product->getStockForStore($storeId) : $product->getStockTotal();
                      @endphp
                      <div class="flex items-center gap-2 mb-1">
                        <span
                          class="text-xs lg:text-sm font-bold {{ $stockQty <= $product->threshold ? 'text-error' : 'text-blue-800' }}">{{ $stockQty }}</span>
                        @if($stockQty <= $product->threshold)
                          <span
                            class="px-1 lg:px-1.5 py-0.5 bg-error-container text-[8px] lg:text-[10px] font-bold text-error rounded">LOW</span>
                        @else
                          <span
                            class="px-1 lg:px-1.5 py-0.5 bg-blue-50 text-[8px] lg:text-[10px] font-bold text-blue-600 rounded">OK</span>
                        @endif
                      </div>
                      <div class="w-20 lg:w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden lg:block">
                        <div
                          class="h-full {{ $stockQty <= $product->threshold ? 'bg-error' : 'bg-primary-container' }} rounded-full"
                          style="width: {{ min(($stockQty / ($product->threshold * 3 ?: 1)) * 100, 100) }}%"></div>
                      </div>
                    </td>
                    <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                      <div class="flex items-center justify-end gap-1">
                        <button type="button" onclick="openDetailModal({{ $product->id }})"
                          class="p-1.5 lg:p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                          title="Detail Produk">
                          <span class="material-symbols-outlined text-base lg:text-sm">info</span>
                        </button>
                        @if(auth()->user()->isSuperAdmin())
                        <button type="button"
                           onclick="openEditModal({{ $product->id }}, '{{ $product->name }}', {{ $product->selling_price }}, {{ $product->cost_price ?? 0 }}, {{ $stockQty }}, {{ $product->threshold }}, {{ $product->promo_price ?? 'null' }}, '{{ $product->promo_start ?? '' }}', '{{ $product->promo_end ?? '' }}', {{ $product->category_id ?? 1 }}, {{ $product->profit_percentage ?? 0 }}, {{ $product->tax_amount ?? 0 }}, '{{ addslashes($product->barcode ?? '') }}', {{ $product->primary_supplier_id ?? 'null' }}, '{{ addslashes($product->unit ?? '') }}', '{{ $product->expired_date ?? '' }}')"
                          class="p-1.5 lg:p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors cursor-pointer"
                          title="Edit Product">
                          <span class="material-symbols-outlined text-base lg:text-sm">edit</span>
                        </button>
                        <form action="/product/{{ $product->id }}" method="POST" class="inline-block delete-form" data-name="{{ $product->name }}">
                          @csrf
                          @method('DELETE')
                          <button type="button" onclick="confirmDeleteProduct({{ $product->id }}, '{{ addslashes($product->name) }}')"
                            class="p-1.5 lg:p-2 text-slate-400 hover:text-error hover:bg-error-container/30 rounded-lg transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-base lg:text-sm">delete</span>
                          </button>
                        </form>
                        @endif

                        @if(auth()->user()->hasMinRole('admin'))
                        <button type="button" onclick="openRecipeModal({{ $product->id }}, '{{ $product->name }}')"
                          class="p-1.5 lg:p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors cursor-pointer"
                          title="Manage Recipe">
                          <span class="material-symbols-outlined text-base lg:text-sm">restaurant_menu</span>
                        </button>
                        @endif
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">No products found.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <!-- Table Pagination -->
          <div
            class="px-6 py-4 bg-surface-container-low/30 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500">Showing {{ $products->firstItem() ?? 0 }} -
              {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products</span>
            <div class="flex items-center gap-1">
              @if($products->onFirstPage())
                <span class="p-1.5 rounded bg-surface-container text-slate-300 cursor-not-allowed">
                  <span class="material-symbols-outlined text-sm">chevron_left</span>
                </span>
              @else
                <a href="{{ $products->previousPageUrl() }}"
                  class="p-1.5 rounded bg-surface-container hover:bg-surface-container-high transition-colors text-primary">
                  <span class="material-symbols-outlined text-sm">chevron_left</span>
                </a>
              @endif
              @foreach($products->getUrlRange(max($products->currentPage() - 2, 1), min($products->currentPage() + 2, $products->lastPage())) as $page => $url)
                <a href="{{ $url }}"
                  class="text-xs font-bold px-2.5 py-1 rounded {{ $page == $products->currentPage() ? 'bg-primary text-white' : 'hover:bg-surface-container cursor-pointer' }} transition-colors">{{ $page }}</a>
              @endforeach
              @if($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}"
                  class="p-1.5 rounded bg-surface-container hover:bg-surface-container-high transition-colors text-primary">
                  <span class="material-symbols-outlined text-sm">chevron_right</span>
                </a>
              @else
                <span class="p-1.5 rounded bg-surface-container text-slate-300 cursor-not-allowed">
                  <span class="material-symbols-outlined text-sm">chevron_right</span>
                </span>
              @endif
            </div>
          </div>
        </div>

      </div>
  </main>

  <!-- Add Product Modal -->
  <div id="addProductModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
      onclick="document.getElementById('addProductModal').classList.add('hidden')"></div>
    <div
      class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-lg bg-surface-container-lowest rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden border border-outline-variant/20">
      <div
        class="px-6 py-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low/30">
        <h3 class="text-lg font-bold text-on-surface">Add New Product</h3>
        <button onclick="document.getElementById('addProductModal').classList.add('hidden')"
          class="text-slate-400 hover:text-on-surface transition-colors cursor-pointer">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="p-6 overflow-y-auto">
        <form action="/product" method="POST" enctype="multipart/form-data" class="space-y-5">
          @csrf
          @if(!auth()->user()->store_id)
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Store</label>
            <select name="store_id" required
              class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
              <option value="">Select Store</option>
              @foreach(\App\Models\Store::where('status', 'active')->get() as $store)
                <option value="{{ $store->id }}">{{ $store->branch->name ?? 'Unknown' }} - {{ $store->name }} ({{ $store->code }})</option>
              @endforeach
            </select>
          </div>
          @endif
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Product Name</label>
            <input name="name" required
              class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              placeholder="Enter product name" type="text" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Barcode</label>
            <input name="barcode"
              class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              placeholder="Scan or enter barcode" type="text" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Category</label>
              <select name="category_id" required
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Supplier</label>
              <select name="primary_supplier_id"
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                <option value="">Select Supplier</option>
                @foreach($suppliers as $supplier)
                  <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Satuan</label>
            <select name="unit"
              class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
              <option value="">Pilih Satuan</option>
              @foreach($units as $unit)
                <option value="{{ $unit->name }}">{{ $unit->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Expired Date</label>
            <input name="expired_date"
              class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              type="date" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">HPP (Cost)</label>
              <div class="relative">
                <span class="absolute left-3 inset-y-0 flex items-center text-on-surface-variant text-xs font-semibold">Rp</span>
                <input name="cost_price" id="inputCostPrice" required
                  class="w-full bg-surface-container-low border-none rounded-lg py-2 pl-7 pr-2 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                  placeholder="0" type="number" min="0" oninput="calculateSellingPrice()" />
              </div>
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Profit (%)</label>
              <div class="relative">
                <span class="absolute left-3 inset-y-0 flex items-center text-on-surface-variant text-xs font-semibold">%</span>
                <input name="profit_percentage" id="inputProfitPercent" value="0"
                  class="w-full bg-surface-container-low border-none rounded-lg py-2 pl-7 pr-2 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                  placeholder="0" type="number" min="0" max="100" oninput="calculateSellingPrice()" />
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3 p-3 bg-surface-container-low rounded-lg">
            <input type="checkbox" name="include_tax" id="inputIncludeTax" value="1"
              class="w-4 h-4 text-primary rounded focus:ring-primary/20" onchange="calculateSellingPrice()">
            <label for="inputIncludeTax" class="text-sm text-on-surface-variant">
              Include Pajak (<span id="vatRateDisplay">11</span>%)
            </label>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Harga Jual (Auto Calculate)</label>
            <div class="relative">
              <span class="absolute left-4 inset-y-0 flex items-center text-on-surface-variant text-sm font-semibold">Rp</span>
              <input name="selling_price" id="inputSellingPrice"
                class="w-full bg-primary/10 border-none rounded-lg py-2.5 pl-10 pr-4 text-sm font-bold text-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                placeholder="0" type="number" min="0" />
              <span class="absolute right-3 text-xs text-slate-400">Auto</span>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Initial Stock</label>
              <input name="stock" required
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                placeholder="0" type="number" />
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Threshold</label>
              <input name="threshold" required
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                placeholder="5" type="number" value="5" />
            </div>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Product Image</label>
            <input name="image" type="file" accept="image/*"
              class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" />
          </div>

          <div class="border-t border-outline-variant/20 pt-4 mt-4">
            <h4 class="text-sm font-bold text-on-surface mb-3">Promo Pricing (Opsional)</h4>
            <div class="grid grid-cols-3 gap-3">
              <div class="space-y-1.5">
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Harga Promo</label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-xs">Rp</span>
                  <input name="promo_price"
                    class="w-full bg-surface-container-low border-none rounded-lg py-2 pl-8 pr-2 text-xs focus:ring-2 focus:ring-primary/20 outline-none"
                    placeholder="0" type="number" min="0" />
                </div>
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Mulai</label>
                <input name="promo_start"
                  class="w-full bg-surface-container-low border-none rounded-lg py-2 px-3 text-xs focus:ring-2 focus:ring-primary/20 outline-none"
                  type="date" />
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Berakhir</label>
                <input name="promo_end"
                  class="w-full bg-surface-container-low border-none rounded-lg py-2 px-3 text-xs focus:ring-2 focus:ring-primary/20 outline-none"
                  type="date" />
              </div>
            </div>
          </div>

          <div class="pt-4 flex gap-3">
            <button
              class="flex-1 bg-surface-container-high text-on-surface py-3 rounded-lg font-bold text-sm hover:bg-surface-dim transition-all cursor-pointer"
              type="button" onclick="document.getElementById('addProductModal').classList.add('hidden')">Cancel</button>
            <button
              class="flex-2 grow bg-primary text-white py-3 px-6 rounded-lg font-bold text-sm shadow-md hover:bg-primary-container transition-all cursor-pointer"
              type="submit">Save Product</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Recipe Modal -->
  <div id="recipeModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeRecipeModal()"></div>
    <div
      class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-lg bg-surface-container-lowest rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden border border-outline-variant/20">
      <div
        class="px-6 py-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low/30">
        <div>
          <h3 class="text-lg font-bold text-on-surface">Recipe: <span id="recipeProductName"></span></h3>
          <p class="text-xs text-on-surface-variant">Pilih bahan &amp; jumlah untuk membuat produk ini</p>
        </div>
        <button onclick="closeRecipeModal()"
          class="text-slate-400 hover:text-on-surface transition-colors cursor-pointer">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="p-6 overflow-y-auto">
        <form id="recipeForm" action="/product/recipe" method="POST">
          @csrf
          <input type="hidden" name="product_id" id="recipeProductId">

          <div id="recipeItems" class="space-y-3 mb-4">
            <p class="text-xs text-slate-500 text-center py-4">Klik "Tambah Bahan" untuk menambahkan bahan</p>
          </div>

          <button type="button" onclick="addRecipeItem()"
            class="w-full py-2 border-2 border-dashed border-outline-variant/30 rounded-lg text-sm font-bold text-on-surface-variant hover:bg-surface-container transition-colors cursor-pointer mb-4">
            + Tambah Bahan
          </button>

          <div class="pt-4 flex gap-3 border-t border-outline-variant/20">
            <button
              class="flex-1 bg-surface-container-high text-on-surface py-3 rounded-lg font-bold text-sm hover:bg-surface-dim transition-all cursor-pointer"
              type="button" onclick="closeRecipeModal()">Cancel</button>
            <button
              class="flex-2 grow bg-primary text-white py-3 px-6 rounded-lg font-bold text-sm shadow-md hover:bg-primary-container transition-all cursor-pointer"
              type="submit">Save Recipe</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    let rawMaterials = [];
    const vatRate = {{ \App\Models\StoreSetting::getVal('vat', auth()->user()->store_id, '11') }};

    document.getElementById('vatRateDisplay').textContent = vatRate;

    function confirmDeleteProduct(productId, productName) {
        Swal.fire({
            title: 'Hapus Produk?',
            text: 'Apakah Anda yakin ingin menghapus produk "' + productName + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.querySelector('form[action="/product/' + productId + '"]');
                if (form) {
                    form.submit();
                } else {
                    Swal.fire('Error', 'Form tidak ditemukan', 'error');
                }
            }
        });
    }

    function calculateSellingPrice() {
        const costPrice = parseFloat(document.getElementById('inputCostPrice').value) || 0;
        const profitPercent = parseFloat(document.getElementById('inputProfitPercent').value) || 0;
        const includeTax = document.getElementById('inputIncludeTax').checked;

        const profitAmount = costPrice * (profitPercent / 100);
        const subtotal = costPrice + profitAmount;
        const taxAmount = includeTax ? (subtotal * vatRate / 100) : 0;
        const rawPrice = subtotal + taxAmount;

        // Round to nearest 100 (ceil)
        const sellingPrice = Math.ceil(rawPrice / 100) * 100;

        document.getElementById('inputSellingPrice').value = sellingPrice;
    }

    function openRecipeModal(productId, productName) {
      document.getElementById('recipeModal').classList.remove('hidden');
      document.getElementById('recipeProductName').textContent = productName;
      document.getElementById('recipeProductId').value = productId;

      fetch('/product/recipe/' + productId)
        .then(res => res.json())
        .then(data => {
          rawMaterials = data.rawMaterials;
          renderRecipeItems(data.recipe?.items || []);
        });
    }

    function closeRecipeModal() {
      document.getElementById('recipeModal').classList.add('hidden');
    }

    function renderRecipeItems(items) {
      const container = document.getElementById('recipeItems');

      if (items.length === 0) {
        container.innerHTML = '<p class="text-xs text-slate-500 text-center py-4">Belum ada bahan. Klik "Tambah Bahan" untuk menambahkan.</p>';
        return;
      }

      container.innerHTML = items.map((item, index) => `
        <div class="flex items-center gap-2 bg-surface-container p-3 rounded-lg">
          <select name="items[${index}][product_id]" class="flex-1 bg-surface-container-low border-none rounded-lg py-2 px-3 text-xs lg:text-sm focus:ring-2 focus:ring-primary/20 outline-none">
            ${rawMaterials.map(rm => `<option value="${rm.id}" ${rm.id === item.product_id ? 'selected' : ''}>${rm.name}</option>`).join('')}
          </select>
          <input type="number" name="items[${index}][quantity]" value="${item.quantity}" min="1" class="w-16 bg-surface-container-low border-none rounded-lg py-2 px-3 text-xs lg:text-sm text-center focus:ring-2 focus:ring-primary/20 outline-none" placeholder="Qty">
          <button type="button" onclick="this.parentElement.remove()" class="p-2 text-error hover:bg-error-container/30 rounded-lg">
            <span class="material-symbols-outlined text-lg">delete</span>
          </button>
        </div>
      `).join('');
    }

    function addRecipeItem() {
      const container = document.getElementById('recipeItems');

      const existingCount = container.querySelectorAll('.flex.items-center').length;
      const index = existingCount;

      const div = document.createElement('div');
      div.className = 'flex items-center gap-2 bg-surface-container p-3 rounded-lg';
      div.innerHTML = `
        <select name="items[${index}][product_id]" class="flex-1 bg-surface-container-low border-none rounded-lg py-2 px-3 text-xs lg:text-sm focus:ring-2 focus:ring-primary/20 outline-none">
          <option value="">Pilih bahan...</option>
          ${rawMaterials.map(rm => `<option value="${rm.id}">${rm.name}</option>`).join('')}
        </select>
        <input type="number" name="items[${index}][quantity]" value="1" min="1" class="w-16 bg-surface-container-low border-none rounded-lg py-2 px-3 text-xs lg:text-sm text-center focus:ring-2 focus:ring-primary/20 outline-none" placeholder="Qty">
        <button type="button" onclick="this.parentElement.remove()" class="p-2 text-error hover:bg-error-container/30 rounded-lg">
          <span class="material-symbols-outlined text-lg">delete</span>
        </button>
      `;

      if (container.querySelector('p')) {
        container.innerHTML = '';
      }
      container.appendChild(div);
    }
  </script>

  <!-- Edit Product Modal -->
  <div id="editProductModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div
      class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-lg bg-surface-container-lowest rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden border border-outline-variant/20">
      <div
        class="px-6 py-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low/30">
        <h3 class="text-lg font-bold text-on-surface">Edit Produk</h3>
        <button onclick="closeEditModal()"
          class="text-slate-400 hover:text-on-surface transition-colors cursor-pointer">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="p-6 overflow-y-auto">
        <form id="editProductForm" action="" method="POST" class="space-y-5">
          @csrf
          <input type="hidden" name="_method" value="PUT">
          <input type="hidden" name="product_id" id="editProductId">

          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Product Name</label>
            <input name="name" id="editProductName" required
              class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              type="text" />
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Barcode</label>
            <input name="barcode" id="editBarcode"
              class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              placeholder="Scan or enter barcode" type="text" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Category</label>
              <select name="category_id" id="editCategoryId"
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Supplier</label>
              <select name="primary_supplier_id" id="editSupplierId"
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                <option value="">Select Supplier</option>
                @foreach($suppliers as $supplier)
                  <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Satuan</label>
            <select name="unit" id="editUnit"
              class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
              <option value="">Pilih Satuan</option>
              @foreach($units as $unit)
                <option value="{{ $unit->name }}">{{ $unit->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Expired Date</label>
            <input name="expired_date" id="editExpiredDate"
              class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              type="date" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">HPP (Cost)</label>
              <div class="relative">
                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-on-surface-variant text-xs">Rp</span>
                <input name="cost_price" id="editCostPrice" required
                  class="w-full bg-surface-container-low border-none rounded-lg py-2 pl-7 pr-2 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                  type="number" oninput="calculateEditSellingPrice()" />
              </div>
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Profit (%)</label>
              <div class="relative">
                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-on-surface-variant text-xs">%</span>
                <input name="profit_percentage" id="editProfitPercent" value="0"
                  class="w-full bg-surface-container-low border-none rounded-lg py-2 pl-7 pr-2 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                  type="number" min="0" max="100" oninput="calculateEditSellingPrice()" />
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3 p-3 bg-surface-container-low rounded-lg">
            <input type="checkbox" name="include_tax" id="editIncludeTax" value="1"
              class="w-4 h-4 text-primary rounded focus:ring-primary/20" onchange="calculateEditSellingPrice()">
            <label for="editIncludeTax" class="text-sm text-on-surface-variant">
              Include Pajak (<span class="editVatRate">11</span>%)
            </label>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Harga Jual (Auto Calculate)</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">Rp</span>
              <input name="selling_price" id="editSellingPrice"
                class="w-full bg-primary/10 border-none rounded-lg py-2.5 pl-8 pr-4 text-sm font-bold text-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                type="number" />
              <span class="absolute right-3 text-xs text-slate-400">Auto</span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Stock</label>
              <input name="stock" id="editStock" required
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                type="number" />
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Threshold</label>
              <input name="threshold" id="editThreshold" required
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                type="number" value="5" />
            </div>
          </div>

          <div class="border-t border-outline-variant/20 pt-4 mt-4">
            <h4 class="text-sm font-bold text-on-surface mb-3">Promo Pricing</h4>
            <div class="grid grid-cols-3 gap-3">
              <div class="space-y-1.5">
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Harga Promo</label>
                <div class="relative">
                  <span class="absolute left-2 top-1/2 -translate-y-1/2 text-on-surface-variant text-xs">Rp</span>
                  <input name="promo_price" id="editPromoPrice"
                    class="w-full bg-surface-container-low border-none rounded-lg py-2 pl-7 pr-2 text-xs focus:ring-2 focus:ring-primary/20 outline-none"
                    type="number" min="0" />
                </div>
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Mulai</label>
                <input name="promo_start" id="editPromoStart"
                  class="w-full bg-surface-container-low border-none rounded-lg py-2 px-2 text-xs focus:ring-2 focus:ring-primary/20 outline-none"
                  type="date" />
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Berakhir</label>
                <input name="promo_end" id="editPromoEnd"
                  class="w-full bg-surface-container-low border-none rounded-lg py-2 px-2 text-xs focus:ring-2 focus:ring-primary/20 outline-none"
                  type="date" />
              </div>
            </div>
          </div>

          <div class="pt-4 flex gap-3 border-t border-outline-variant/20">
            <button type="button" onclick="closeEditModal()"
              class="flex-1 bg-surface-container-high text-on-surface py-3 rounded-lg font-bold text-sm hover:bg-surface-dim transition-all">Batal</button>
            <button type="submit"
              class="flex-1 bg-primary text-white py-3 px-6 rounded-lg font-bold text-sm shadow-md hover:bg-primary-container">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<script>
    function openEditModal(id, name, sellingPrice, costPrice, stock, threshold, promoPrice, promoStart, promoEnd, categoryId, profitPercent = 0, taxIncluded = 0, barcode = '', supplierId = '', unit = '', expiredDate = '') {
      document.getElementById('editProductModal').classList.remove('hidden');
      document.getElementById('editProductForm').action = '/product/' + id;
      document.getElementById('editProductId').value = id;
      document.getElementById('editProductName').value = name;
      document.getElementById('editBarcode').value = barcode;
      document.getElementById('editSellingPrice').value = sellingPrice;
      document.getElementById('editCostPrice').value = costPrice;
      document.getElementById('editProfitPercent').value = profitPercent;
      document.getElementById('editIncludeTax').checked = taxIncluded == 1;
      document.getElementById('editStock').value = stock;
      document.getElementById('editThreshold').value = threshold;
      document.getElementById('editPromoPrice').value = promoPrice && promoPrice !== 'null' ? promoPrice : '';
      document.getElementById('editPromoStart').value = promoStart;
      document.getElementById('editPromoEnd').value = promoEnd;
      document.getElementById('editCategoryId').value = categoryId;
      document.getElementById('editSupplierId').value = supplierId;
      document.getElementById('editUnit').value = unit;
      document.getElementById('editExpiredDate').value = expiredDate;
    }

    function calculateEditSellingPrice() {
        const costPrice = parseFloat(document.getElementById('editCostPrice').value) || 0;
        const profitPercent = parseFloat(document.getElementById('editProfitPercent').value) || 0;
        const includeTax = document.getElementById('editIncludeTax').checked;

        const profitAmount = costPrice * (profitPercent / 100);
        const subtotal = costPrice + profitAmount;
        const taxAmount = includeTax ? (subtotal * vatRate / 100) : 0;
        const rawPrice = subtotal + taxAmount;

        // Round to nearest 100 (ceil)
        const sellingPrice = Math.ceil(rawPrice / 100) * 100;

        document.getElementById('editSellingPrice').value = sellingPrice;
    }
    
    function closeEditModal() {
      document.getElementById('editProductModal').classList.add('hidden');
    }
  </script>

  <!-- Promo Import Modal -->
  <div id="promoImportModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
      onclick="document.getElementById('promoImportModal').classList.add('hidden')"></div>
    <div
      class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-md bg-surface-container-lowest rounded-2xl shadow-2xl p-6 border border-outline-variant/20">
      <h3 class="text-lg font-bold text-on-surface mb-2">Import Promo Excel/CSV</h3>
      <p class="text-xs text-on-surface-variant mb-4">Format: product, promo_price, promo_start, promo_end</p>

      <form action="/product/promo-import" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="file" name="file" accept=".csv,.xls,.xlsx" required class="w-full text-sm" />

        @if(session('promo_import_results'))
          <div class="p-3 rounded-lg {{ session('promo_import_results')['success'] > 0 ? 'bg-green-50' : 'bg-red-50' }}">
            <span
              class="text-sm font-bold {{ session('promo_import_results')['success'] > 0 ? 'text-green-700' : 'text-red-700' }}">
              {{ session('promo_import_results')['success'] }} berhasil, {{ session('promo_import_results')['failed'] }}
              gagal
            </span>
            @if(session('promo_import_results')['success'] > 0)
            <script>setTimeout(() => location.reload(), 1500);</script>
            @endif
          </div>
        @endif

        <div class="flex gap-3">
          <a href="/template-promo.csv" download
            class="flex-1 py-2 text-center bg-surface-container text-sm font-bold rounded-lg hover:bg-surface-container-high">Template</a>
          <button type="submit" class="flex-1 bg-primary text-white py-2 font-bold rounded-lg">Import</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Product Detail Modal -->
  <div id="productDetailModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
      onclick="document.getElementById('productDetailModal').classList.add('hidden')"></div>
    <div
      class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-3xl bg-surface-container-lowest rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden border border-outline-variant/20">
      <div
        class="px-6 py-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low/30">
        <h3 class="text-lg font-bold text-on-surface">Detail Produk</h3>
        <button onclick="document.getElementById('productDetailModal').classList.add('hidden')"
          class="text-slate-400 hover:text-on-surface transition-colors cursor-pointer">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="p-6 overflow-y-auto" id="detailModalContent">
        <div class="flex items-center justify-center py-12">
          <span class="material-symbols-outlined text-4xl text-slate-300 animate-spin">progress_activity</span>
        </div>
      </div>
    </div>
  </div>

  <script>
    function openDetailModal(productId) {
      const modal = document.getElementById('productDetailModal');
      const content = document.getElementById('detailModalContent');
      modal.classList.remove('hidden');
      content.innerHTML = '<div class="flex items-center justify-center py-12"><span class="material-symbols-outlined text-4xl text-slate-300 animate-spin">progress_activity</span></div>';

      fetch('/product/' + productId + '/detail')
        .then(res => res.json())
        .then(data => {
          const p = data.product;
          const stocks = data.stocks;
          const summary = data.summary;
          const history = data.history;

          let stockHtml = '';
          if (stocks.length > 0) {
            const showStoreName = stocks.length > 1;
            stockHtml = stocks.map(s => `
              <div class="flex items-center justify-between py-2 px-3 bg-surface-container-low rounded-lg">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-sm text-slate-400">store</span>
                  ${showStoreName ? '<span class="text-sm font-medium">' + s.store + '</span><span class="text-[10px] text-slate-400 font-mono">' + s.code + '</span>' : '<span class="text-sm font-medium">Stok Tersedia</span>'}
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-sm font-bold ${s.is_low ? 'text-red-600' : 'text-blue-800'}">${s.quantity}</span>
                  ${s.is_low ? '<span class="px-1.5 py-0.5 bg-red-100 text-[9px] font-bold text-red-600 rounded">LOW</span>' : '<span class="px-1.5 py-0.5 bg-blue-50 text-[9px] font-bold text-blue-600 rounded">OK</span>'}
                </div>
              </div>
            `).join('');
          } else {
            stockHtml = '<p class="text-xs text-slate-400 text-center py-4">Belum ada data stok</p>';
          }

          let historyHtml = '';
          if (history.length > 0) {
            historyHtml = history.map(h => {
              let icon = '', color = '', label = '', detail = '';
              if (h.type === 'stock_in') {
                icon = 'move_to_inbox'; color = 'text-green-600'; label = 'Stok Masuk';
                detail = `<span class="text-xs text-slate-500">${h.supplier}</span> &middot; <span class="text-xs text-slate-500">${h.store}</span> &middot; <span class="text-xs text-slate-400">${h.reference}</span>`;
              } else if (h.type === 'sale') {
                icon = 'point_of_sale'; color = 'text-blue-600'; label = 'Penjualan';
                detail = `<span class="text-xs text-slate-500">${h.invoice}</span> &middot; <span class="text-xs text-slate-500">${h.cashier}</span> &middot; <span class="text-xs text-slate-400">${h.customer}</span>`;
              } else if (h.type === 'transfer_out') {
                icon = 'arrow_upward'; color = 'text-amber-600'; label = 'Mutasi Keluar';
                detail = `<span class="text-xs text-slate-500">${h.from}</span> &rarr; <span class="text-xs text-slate-500">${h.to}</span> &middot; <span class="text-xs text-slate-400">${h.reference}</span>`;
              } else if (h.type === 'transfer_in') {
                icon = 'arrow_downward'; color = 'text-purple-600'; label = 'Mutasi Masuk';
                detail = `<span class="text-xs text-slate-500">${h.from}</span> &rarr; <span class="text-xs text-slate-500">${h.to}</span> &middot; <span class="text-xs text-slate-400">${h.reference}</span>`;
              }
              const qtyClass = h.type === 'sale' || h.type === 'transfer_out' ? 'text-red-600' : 'text-green-600';
              const qtyPrefix = h.type === 'sale' || h.type === 'transfer_out' ? '-' : '+';
              return `
                <div class="flex items-center justify-between py-2.5 px-3 hover:bg-surface-container-low/50 rounded-lg transition-colors">
                  <div class="flex items-center gap-3 flex-1 min-w-0">
                    <span class="material-symbols-outlined text-base ${color}">${icon}</span>
                    <div class="min-w-0">
                      <div class="text-xs font-bold">${label}</div>
                      <div class="text-[10px] text-slate-400 truncate">${detail}</div>
                    </div>
                  </div>
                  <div class="text-right ml-3 flex-shrink-0">
                    <div class="text-xs font-bold ${qtyClass}">${qtyPrefix}${h.quantity}</div>
                    <div class="text-[10px] text-slate-400">${h.date}</div>
                  </div>
                </div>
              `;
            }).join('');
          } else {
            historyHtml = '<p class="text-xs text-slate-400 text-center py-4">Belum ada riwayat transaksi</p>';
          }

          content.innerHTML = `
            <div class="space-y-5">
              <!-- Product Info -->
              <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-xl bg-slate-100 flex-shrink-0 overflow-hidden">
                  ${p.image ? '<img class="w-full h-full object-cover" src="/storage/' + p.image + '" />' : '<div class="w-full h-full flex items-center justify-center text-slate-400"><span class="material-symbols-outlined text-2xl">image</span></div>'}
                </div>
                <div class="flex-1">
                  <h4 class="text-base font-bold text-on-surface">${p.name}</h4>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-mono text-slate-400 bg-slate-100 px-2 py-0.5 rounded">${p.sku}</span>
                    ${p.barcode ? '<span class="text-xs font-mono text-primary bg-primary/10 px-2 py-0.5 rounded">' + p.barcode + '</span>' : ''}
                    <span class="text-xs text-slate-500">${p.category}</span>
                  </div>
                  <div class="flex items-center gap-3 mt-2">
                    <span class="text-xs text-slate-500">Supplier: <span class="font-medium ${p.supplier === 'Belum diset' ? 'text-amber-600' : 'text-on-surface'}">${p.supplier}</span></span>
                  </div>
                </div>
              </div>

              <!-- Pricing -->
              <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <div class="bg-surface-container-low rounded-lg p-3 text-center">
                  <div class="text-[10px] text-slate-400 font-bold uppercase">HPP</div>
                  <div class="text-sm font-bold text-on-surface mt-1">Rp${Number(p.cost_price).toLocaleString('id-ID')}</div>
                </div>
                <div class="bg-surface-container-low rounded-lg p-3 text-center">
                  <div class="text-[10px] text-slate-400 font-bold uppercase">Harga Jual</div>
                  <div class="text-sm font-bold text-blue-800 mt-1">Rp${Number(p.selling_price).toLocaleString('id-ID')}</div>
                </div>
                <div class="bg-surface-container-low rounded-lg p-3 text-center">
                  <div class="text-[10px] text-slate-400 font-bold uppercase">Profit</div>
                  <div class="text-sm font-bold text-green-600 mt-1">${p.profit_percentage}%</div>
                </div>
                <div class="bg-surface-container-low rounded-lg p-3 text-center">
                  <div class="text-[10px] text-slate-400 font-bold uppercase">Promo</div>
                  ${p.is_promo_active ? '<div class="text-sm font-bold text-red-600 mt-1">Rp'+Number(p.promo_price).toLocaleString('id-ID')+'</div>' : '<div class="text-xs text-slate-400 mt-1">Tidak aktif</div>'}
                </div>
                <div class="bg-surface-container-low rounded-lg p-3 text-center">
                  <div class="text-[10px] text-slate-400 font-bold uppercase">Expired</div>
                  ${p.earliest_expiry ? (() => {
                    const d = new Date(p.earliest_expiry);
                    const now = new Date();
                    const diff = Math.ceil((d - now) / (1000*60*60*24));
                    const cls = diff <= 0 ? 'text-red-600' : diff <= 30 ? 'text-yellow-600' : 'text-green-600';
                    return '<div class="text-sm font-bold '+cls+' mt-1">'+d.toLocaleDateString('id-ID')+'</div>';
                  })() : '<div class="text-xs text-slate-400 mt-1">-</div>'}
                </div>
              </div>

              <!-- Stock -->
              <div>
                <h5 class="text-sm font-bold text-on-surface mb-2 flex items-center gap-2">
                  <span class="material-symbols-outlined text-base text-slate-400">warehouse</span>
                  Stok Toko
                </h5>
                <div class="space-y-1.5">${stockHtml}</div>
              </div>

              <!-- Summary -->
              <div class="grid grid-cols-4 gap-2">
                <div class="bg-green-50 rounded-lg p-2 text-center">
                  <div class="text-[9px] text-green-600 font-bold uppercase">Stok Masuk</div>
                  <div class="text-sm font-bold text-green-700">${summary.total_stock_in}</div>
                </div>
                <div class="bg-blue-50 rounded-lg p-2 text-center">
                  <div class="text-[9px] text-blue-600 font-bold uppercase">Terjual</div>
                  <div class="text-sm font-bold text-blue-700">${summary.total_sales}</div>
                </div>
                <div class="bg-amber-50 rounded-lg p-2 text-center">
                  <div class="text-[9px] text-amber-600 font-bold uppercase">Mutasi Keluar</div>
                  <div class="text-sm font-bold text-amber-700">${summary.total_transfer_out}</div>
                </div>
                <div class="bg-purple-50 rounded-lg p-2 text-center">
                  <div class="text-[9px] text-purple-600 font-bold uppercase">Mutasi Masuk</div>
                  <div class="text-sm font-bold text-purple-700">${summary.total_transfer_in}</div>
                </div>
              </div>

              <!-- History -->
              <div>
                <h5 class="text-sm font-bold text-on-surface mb-2 flex items-center gap-2">
                  <span class="material-symbols-outlined text-base text-slate-400">history</span>
                  Riwayat Transaksi
                </h5>
                <div class="space-y-0.5 max-h-64 overflow-y-auto">${historyHtml}</div>
              </div>
            </div>
          `;
        })
        .catch(err => {
          content.innerHTML = '<div class="text-center py-8 text-red-500 text-sm">Gagal memuat detail produk</div>';
        });
    }
  </script>

</x-layout>