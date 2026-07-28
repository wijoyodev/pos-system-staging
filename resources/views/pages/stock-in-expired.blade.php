<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>

  <main class="flex-1 flex flex-col min-h-screen relative w-full">
    <!-- Top Bar -->
    <header
      class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-[31] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Produk Expired</h1>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-lg text-slate-400">search</span>
          <input type="text" id="searchInput" placeholder="Cari nama, PLU, atau barcode..."
            class="w-full pl-10 pr-4 py-2 bg-slate-100/50 border-none rounded-xl focus:ring-2 focus:ring-primary/10 transition-all text-sm outline-none">
        </div>
      </div>
    </header>

    <!-- Canvas -->
    <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
      @if($errors->any())
      <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
        <p class="text-sm font-bold text-red-700">{{ $errors->first() }}</p>
      </div>
      @endif

      <!-- Filter Tabs -->
      <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ url()->current() }}?filter=all"
          class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $currentFilter === 'all' ? 'bg-primary text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
          Semua ({{ $products->count() }})
        </a>
        <a href="{{ url()->current() }}?filter=expired"
          class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $currentFilter === 'expired' ? 'bg-red-500 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
          Expired
        </a>
        <a href="{{ url()->current() }}?filter=near"
          class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $currentFilter === 'near' ? 'bg-yellow-500 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
          Hampir Expired (30 hari)
        </a>
        <a href="{{ url()->current() }}?filter=no_data"
          class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $currentFilter === 'no_data' ? 'bg-slate-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
          Tanpa Expiry
        </a>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-700">
                <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Produk</th>
                <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kategori</th>
                <th class="text-center px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Stok</th>
                <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Batch Expired</th>
                <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Produk Expired</th>
                <th class="text-center px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                <th class="text-center px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
              @forelse($products as $product)
              <tr data-barcode="{{ $product->barcode }}" class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-4 py-3">
                  <div class="font-bold text-slate-800 dark:text-white">{{ $product->name }}</div>
                  <div class="text-[10px] text-slate-400 font-medium">{{ $product->sku }}</div>
                </td>
                <td class="px-4 py-3">
                  <span class="text-xs text-slate-500">{{ $product->category->name ?? '-' }}</span>
                  @if($product->unit)
                  <span class="text-[10px] text-slate-400"> · {{ $product->unit }}</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-black text-xs">{{ $product->store_stock }}</span>
                </td>
                <td class="px-4 py-3">
                  @php
                    $batchItems = $product->stockInItems;
                  @endphp
                  @if($batchItems->count())
                  <div class="space-y-1">
                    @foreach($batchItems as $item)
                    <div class="flex items-center gap-2 text-xs">
                      <span class="text-slate-600 dark:text-slate-300">{{ $item->expired_date->format('d M Y') }}</span>
                      @if($item->isExpired())
                      <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-700 text-[9px] font-black">EXP</span>
                      @elseif($item->isNearExpiry())
                      <span class="px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700 text-[9px] font-black">{{ (int) $item->expired_date->diffInDays(now()) }} hr</span>
                      @else
                      <span class="px-1.5 py-0.5 rounded bg-green-100 text-green-700 text-[9px] font-black">OK</span>
                      @endif
                    </div>
                    @endforeach
                  </div>
                  @else
                  <span class="text-xs text-slate-400 italic">Tidak ada batch</span>
                  @endif
                </td>
                <td class="px-4 py-3">
                  @php
                    $productExpiry = $product->expired_date;
                  @endphp
                  <span class="text-xs {{ $productExpiry ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400 italic' }}">
                    {{ $productExpiry ? $productExpiry->format('d M Y') : 'Tidak diset' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  @php
                    $earliest = $product->getEarliestExpiryDate();
                    $isExpired = $product->isExpired();
                    $isNear = $product->isNearExpiry();
                  @endphp
                  @if(!$earliest)
                  <span class="inline-flex px-2 py-1 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-black">NO DATA</span>
                  @elseif($isExpired)
                  <span class="inline-flex px-2 py-1 rounded-lg bg-red-100 text-red-700 text-[10px] font-black">EXPIRED</span>
                  @elseif($isNear)
                  <span class="inline-flex px-2 py-1 rounded-lg bg-yellow-100 text-yellow-700 text-[10px] font-black">{{ (int) \Carbon\Carbon::parse($earliest)->diffInDays(now()) }} HARI</span>
                  @else
                  <span class="inline-flex px-2 py-1 rounded-lg bg-green-100 text-green-700 text-[10px] font-black">OK</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-center">
                  @if(auth()->user()->hasMinRole('admin'))
                  <button onclick="openEditExpired({{ $product->id }}, '{{ $product->expired_date?->format('Y-m-d') ?? '' }}')"
                    class="px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all text-[10px] font-black cursor-pointer">
                    EDIT
                  </button>
                  @else
                  <span class="text-[10px] text-slate-400">-</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="px-4 py-8 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <span class="material-symbols-outlined text-3xl text-slate-300">calendar_month</span>
                    <p class="text-sm text-slate-400 font-medium">Tidak ada produk ditemukan</p>
                    <p class="text-xs text-slate-300">Semua produk dalam kondisi baik</p>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Edit Expired Modal -->
  <div id="editExpiredModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('editExpiredModal').classList.add('hidden')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-black text-slate-800 dark:text-white">Edit Tanggal Expired</h2>
          <button onclick="document.getElementById('editExpiredModal').classList.add('hidden')" class="p-1 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer">
            <span class="material-symbols-outlined text-lg">close</span>
          </button>
        </div>
        <form method="POST" action="/stock-in/expired/update" class="space-y-4">
          @csrf
          <input type="hidden" name="product_id" id="editProductId">
          <div>
            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Tanggal Kedaluwarsa Produk</label>
            <input type="date" name="expired_date" id="editExpiredDate"
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
            <p class="text-[10px] text-slate-400 mt-1">Atur tanggal kedaluwarsa default untuk produk ini (digunakan jika batch tidak memiliki tanggal).</p>
          </div>
          <div class="flex gap-2 justify-end">
            <button type="button" onclick="document.getElementById('editExpiredModal').classList.add('hidden')"
              class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200 transition-all cursor-pointer">
              Batal
            </button>
            <button type="submit"
              class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container transition-all cursor-pointer">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('searchInput')?.addEventListener('input', function () {
      const q = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll('tbody tr');
      rows.forEach(row => {
        const name = row.querySelector('td:first-child div:first-child')?.textContent?.toLowerCase() || '';
        const sku = row.querySelector('td:first-child div:last-child')?.textContent?.toLowerCase() || '';
        const barcode = row.getAttribute('data-barcode')?.toLowerCase() || '';
        row.style.display = !q || name.includes(q) || sku.includes(q) || barcode.includes(q) ? '' : 'none';
      });
    });

    function openEditExpired(productId, expiredDate) {
      document.getElementById('editProductId').value = productId;
      document.getElementById('editExpiredDate').value = expiredDate;
      document.getElementById('editExpiredModal').classList.remove('hidden');
    }
  </script>
</x-layout>