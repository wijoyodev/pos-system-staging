<!-- Mobile Toggle Button (Floating Arrow) -->
<button id="sidebar-toggle-mobile"
  class="lg:hidden fixed top-1/2 -translate-y-1/2 left-2 z-[60] w-9 h-9 bg-white/90 backdrop-blur-xl shadow-lg rounded-full flex items-center justify-center text-primary border border-white/30 active:scale-90 transition-all"
  onclick="toggleMobileSidebar()">
  <span id="mobile-toggle-icon" class="material-symbols-outlined text-lg font-black">chevron_right</span>
</button>

<script>
function toggleMobileSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  const icon = document.getElementById('mobile-toggle-icon');
  const isOpen = !sidebar.classList.contains('-translate-x-full');
  
  if (isOpen) {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
    icon.textContent = 'chevron_right';
  } else {
    sidebar.classList.remove('-translate-x-full');
    overlay.classList.remove('hidden');
    icon.textContent = 'chevron_left';
  }
}
</script>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="hidden lg:hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[45]"
  onclick="toggleMobileSidebar();"></div>

<!-- Sidebar -->
<aside id="sidebar"
  class="h-screen fixed left-0 top-0 flex flex-col border-r border-slate-200/50 bg-slate-50 dark:bg-slate-900 font-manrope font-semibold text-sm print:hidden z-[50] -translate-x-full lg:translate-x-0 transition-all duration-300">
  
  <!-- Expand/Collapse Toggle Button (Desktop) -->
  <button id="sidebar-toggle"
    class="hidden lg:flex absolute -right-3 top-12 w-7 h-7 bg-white dark:bg-slate-800 shadow-lg rounded-full items-center justify-center text-slate-400 hover:text-primary hover:scale-110 transition-all z-10 border border-slate-100 dark:border-slate-700">
    <span id="toggle-icon" class="material-symbols-outlined text-sm font-black transition-transform duration-300">chevron_left</span>
  </button>

  <!-- Logo Section -->
  <div class="p-6 mb-2">
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary to-primary-container flex items-center justify-center text-white shadow-lg shadow-primary/20 overflow-hidden flex-shrink-0 transition-transform hover:rotate-3">
        @php
          $storeId = auth()->check() ? auth()->user()->store_id : null;
          $storeName = $storeId ? \App\Models\Store::find($storeId)?->name : null;
          $storeCode = $storeId ? \App\Models\Store::find($storeId)?->code : null;
        @endphp
        @if(\App\Models\StoreSetting::getVal('store_logo', $storeId))
          <img src="{{ Storage::url(\App\Models\StoreSetting::getVal('store_logo', $storeId)) }}" class="w-full h-full object-cover">
        @else
          <span class="material-symbols-outlined font-black text-2xl" style="font-variation-settings: 'FILL' 1;">architecture</span>
        @endif
      </div>
      <div class="flex flex-col min-w-0 sidebar-text">
        <span class="text-base font-black text-slate-800 dark:text-white tracking-tight truncate leading-tight">{{ $storeName ?? 'Toko' }}</span>
        <span class="text-[10px] font-black uppercase tracking-widest text-primary/60 dark:text-primary-fixed-dim">{{ $storeCode ?? 'ARKA POS' }}</span>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 overflow-y-auto px-4 py-2 no-scrollbar space-y-1">
    
    <!-- SECTION: OVERVIEW -->
    @if(auth()->user()->hasMinRole('admin'))
    <div class="pt-2 pb-2 px-2 sidebar-section-header">
        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Ikhtisar</p>
    </div>

    {{-- Dashboard --}}
    <a href="/dashboard" 
      class="{{ request()->is('dashboard') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('dashboard') ? 1 : 0 }};">dashboard</span>
      <span class="sidebar-link-text font-bold">Dashboard</span>
    </a>
    @endif

    <!-- SECTION: TRANSACTIONS -->
    @if(auth()->user()->hasMinRole('admin'))
    <div class="pt-6 pb-2 px-2 sidebar-section-header">
        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Transaksi</p>
    </div>
    @endif

    {{-- POS --}}
    @if(!auth()->user()->isSuperAdmin())
    <a href="/" 
      class="{{ request()->is('/') || request()->is('home') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('/') ? 1 : 0 }};">shopping_cart</span>
      <span class="sidebar-link-text font-bold">POS Terminal</span>
    </a>
    @endif

    {{-- Pending Orders --}}
    @if(!auth()->user()->isSuperAdmin())
    <a href="/pending-orders" 
      class="{{ request()->is('pending-orders*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('pending-orders*') ? 1 : 0 }};">pending_actions</span>
      <span class="sidebar-link-text font-bold">Pending Orders</span>
    </a>
    @endif

    {{-- History --}}
    <a href="/history" 
      class="{{ request()->is('history') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('history') ? 1 : 0 }};">history</span>
      <span class="sidebar-link-text font-bold">Riwayat</span>
    </a>

    {{-- Expired Products --}}
    <a href="/stock-in/expired" 
      class="{{ request()->is('stock-in/expired*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('stock-in/expired*') ? 1 : 0 }};">calendar_month</span>
      <span class="sidebar-link-text font-bold">Produk Expired</span>
    </a>

    <!-- SECTION: MANAGEMENT -->
    @if(auth()->user()->hasMinRole('admin'))
    <div class="pt-6 pb-2 px-2 sidebar-section-header">
        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Management</p>
    </div>
    @endif

    {{-- Data Clerek --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/clerek/data" 
      class="{{ request()->is('clerek/data*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('clerek/data*') ? 1 : 0 }};">assessment</span>
      <span class="sidebar-link-text font-bold">Data Clerek</span>
    </a>
    @endif

    {{-- Products --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/product" 
      class="{{ request()->is('product') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('product') ? 1 : 0 }};">inventory_2</span>
      <span class="sidebar-link-text font-bold">Produk</span>
    </a>
    @endif

    {{-- Categories --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/category" 
      class="{{ request()->is('category') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('category') ? 1 : 0 }};">category</span>
      <span class="sidebar-link-text font-bold">Kategori</span>
    </a>
    @endif

    {{-- Price Labels --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/price-label" 
      class="{{ request()->is('price-label*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('price-label*') ? 1 : 0 }};">price_check</span>
      <span class="sidebar-link-text font-bold">Label Harga</span>
    </a>
    @endif

    {{-- Customers --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/customers" 
      class="{{ request()->is('customers*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('customers*') ? 1 : 0 }};">loyalty</span>
      <span class="sidebar-link-text font-bold">Pelanggan</span>
    </a>
    @endif

    {{-- Stock Alerts --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/stock" 
      class="{{ request()->is('stock') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('stock') ? 1 : 0 }};">warning</span>
      <span class="sidebar-link-text font-bold">Stok Menipis</span>
    </a>
    @endif

    {{-- Stock In --}}
    @if(auth()->user()->hasMinRole('admin'))
    @php $isStockIn = request()->is('stock-in*') && !request()->is('stock-in/expired*'); @endphp
    <a href="/stock-in" 
      class="{{ $isStockIn ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ $isStockIn ? 1 : 0 }};">move_to_inbox</span>
      <span class="sidebar-link-text font-bold">Stok Masuk</span>
    </a>
    @endif

    {{-- Stock Transfer (Mutasi Barang) --}}
    @if(auth()->user()->isSuperAdmin() || (auth()->user()->hasMinRole('admin') && \App\Models\StockTransfer::hasActiveTransferForStore(auth()->user()->store_id)))
    <a href="/stock-transfer" 
      class="{{ request()->is('stock-transfer*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('stock-transfer*') ? 1 : 0 }};">swap_horiz</span>
      <span class="sidebar-link-text font-bold">Mutasi Barang</span>
    </a>
    @endif

    {{-- Suppliers --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/suppliers" 
      class="{{ request()->is('suppliers*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('suppliers*') ? 1 : 0 }};">local_shipping</span>
      <span class="sidebar-link-text font-bold">Supplier</span>
    </a>
    @endif

    {{-- Purchase Orders --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/purchase-orders" 
      class="{{ request()->is('purchase-orders*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('purchase-orders*') ? 1 : 0 }};">receipt_long</span>
      <span class="sidebar-link-text font-bold">Purchase Order</span>
    </a>
    @endif

    {{-- End Of Day --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/eod" 
      class="{{ request()->is('eod*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('eod*') ? 1 : 0 }};">event_available</span>
      <span class="sidebar-link-text font-bold">End Of Day</span>
    </a>
    @endif

    {{-- Stock Opname --}}
    @if(auth()->user()->hasMinRole('admin'))
      @php
        $storeId = auth()->user()->store_id;
        $hasSchedule = \App\Models\StockOpnameSession::hasActiveSchedule($storeId);
      @endphp
      @if(auth()->user()->isSuperAdmin() || $hasSchedule)
      <a href="/stock-opname" 
        class="{{ request()->is('stock-opname*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
        <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('stock-opname*') ? 1 : 0 }};">inventory</span>
        <span class="sidebar-link-text font-bold">Stock Opname</span>
      </a>
      @endif
    @endif

    {{-- Promotions --}}
    @if(auth()->user()->isSuperAdmin())
    <a href="/promotions" 
      class="{{ request()->is('promotions*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('promotions*') ? 1 : 0 }};">sell</span>
      <span class="sidebar-link-text font-bold">Promosi</span>
    </a>
    @endif

    <!-- SECTION: REPORTS -->
    @if(auth()->user()->hasMinRole('admin'))
    <div class="pt-6 pb-2 px-2 sidebar-section-header">
        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Laporan & Biaya</p>
    </div>
    @endif

    {{-- Laba Rugi --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/reports/pnl" 
      class="{{ request()->is('reports/pnl*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('reports/pnl*') ? 1 : 0 }};">monitoring</span>
      <span class="sidebar-link-text font-bold">Laba Rugi (P&L)</span>
    </a>
    @endif

    {{-- Pengeluaran --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/expenses" 
      class="{{ request()->is('expenses*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('expenses*') ? 1 : 0 }};">outbox</span>
      <span class="sidebar-link-text font-bold">Pengeluaran</span>
    </a>
    @endif

    <!-- SECTION: HR & ATTENDANCE -->
    @if(auth()->user()->hasMinRole('admin'))
    <div class="pt-6 pb-2 px-2 sidebar-section-header">
        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">SDM & Presensi</p>
    </div>
    @endif

    {{-- Jadwal Shift --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/shift-schedule" 
      class="{{ request()->is('shift-schedule*') || request()->is('shift-assignment*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('shift-schedule*') ? 1 : 0 }};">schedule</span>
      <span class="sidebar-link-text font-bold">Jadwal Shift</span>
    </a>
    @endif

    {{-- Presensi QR --}}
    @if(auth()->user()->isAdmin())
    <a href="/presensi/qr" 
      class="{{ request()->is('presensi/qr*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('presensi/qr*') ? 1 : 0 }};">qr_code</span>
      <span class="sidebar-link-text font-bold">Presensi QR</span>
    </a>
    @endif

    {{-- Super Admin Section --}}
    @if(auth()->user()->isSuperAdmin())
    <div class="pt-6 pb-2 px-2 sidebar-section-header">
        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Super Admin</p>
    </div>
    <a href="/branches" class="{{ request()->is('branches*') ? 'text-primary' : 'text-slate-600' }} flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-200 transition-all group">
      <span class="material-symbols-outlined flex-shrink-0 group-hover:scale-110">business</span>
      <span class="sidebar-link-text font-bold">Cabang & Toko</span>
    </a>
    <a href="/users" class="{{ request()->is('users*') ? 'text-primary' : 'text-slate-600' }} flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-200 transition-all group">
      <span class="material-symbols-outlined flex-shrink-0 group-hover:scale-110">group</span>
      <span class="sidebar-link-text font-bold">Manajemen User</span>
    </a>
    <a href="/void-otp" class="{{ request()->is('void-otp*') ? 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-200' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('void-otp*') ? 1 : 0 }};">vpn_key</span>
      <span class="sidebar-link-text font-bold">Void OTP</span>
    </a>
    <a href="/backup" class="{{ request()->is('backup*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('backup*') ? 1 : 0 }};">database</span>
      <span class="sidebar-link-text font-bold">Backup DB</span>
    </a>
    <a href="/pengaturan-integrasi" class="{{ request()->is('pengaturan-integrasi*') ? 'bg-gradient-to-r from-primary to-primary-container text-white shadow-lg shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800' }} flex items-center gap-3 px-4 py-3 rounded-2xl transition-all sidebar-link group">
      <span class="material-symbols-outlined flex-shrink-0 transition-transform group-hover:scale-110" style="font-variation-settings: 'FILL' {{ request()->is('pengaturan-integrasi*') ? 1 : 0 }};">api</span>
      <span class="sidebar-link-text font-bold">Pengaturan Integrasi</span>
    </a>
    @endif

  </nav>

  <!-- Bottom Section -->
  <div class="p-4 mt-auto space-y-1">
    
    {{-- Store Settings --}}
    @if(auth()->user()->isSuperAdmin())
    <a href="/store-settings" class="{{ request()->is('store-settings') ? 'text-primary' : 'text-slate-500' }} flex items-center gap-3 px-4 py-2 hover:text-primary transition-colors group">
      <span class="material-symbols-outlined text-xl group-hover:rotate-45 transition-transform">storefront</span>
      <span class="sidebar-link-text text-[10px] font-black uppercase tracking-widest">Store Settings</span>
    </a>
    @endif

    {{-- General Settings --}}
    @if(auth()->user()->hasMinRole('admin'))
    <a href="/setting" class="{{ request()->is('setting') ? 'text-primary' : 'text-slate-500' }} flex items-center gap-3 px-4 py-2 hover:text-primary transition-colors group">
      <span class="material-symbols-outlined text-xl group-hover:rotate-90 transition-transform duration-500">settings</span>
      <span class="sidebar-link-text text-[10px] font-black uppercase tracking-widest">General Settings</span>
    </a>
    @endif

    <!-- User Profile Card -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-3 shadow-sm border border-slate-100 dark:border-slate-700 flex items-center gap-3 group relative mb-2 sidebar-user-card">
      <div class="w-10 h-10 rounded-2xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary flex-shrink-0 group-hover:bg-primary group-hover:text-white transition-all duration-300">
        <span class="material-symbols-outlined font-black">person</span>
      </div>
      <div class="flex flex-col min-w-0 sidebar-user-info overflow-hidden">
        <span class="text-xs font-black text-slate-800 dark:text-white truncate">{{ auth()->user()->name }}</span>
        <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ auth()->user()->role_label }}</span>
      </div>
      
      <!-- Logout Inline Button (Hidden when collapsed) -->
      <form action="/logout" method="POST" class="ml-auto flex-shrink-0 logout-form">
        @csrf
        <button type="submit" class="w-8 h-8 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm">
          <span class="material-symbols-outlined text-sm font-black">logout</span>
        </button>
      </form>
    </div>

    <!-- Separate Logout Button for Collapsed Mode -->
    <form action="/logout" method="POST" class="hidden sidebar-collapsed-logout">
        @csrf
        <button type="submit" class="w-full py-3 flex justify-center text-red-500 hover:bg-red-50 rounded-2xl transition-all group">
            <span class="material-symbols-outlined font-black group-hover:scale-110">logout</span>
        </button>
    </form>
  </div>
</aside>
