<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full" x-data="clerekAdmin()">

        <header class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
            <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
                <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Shift Settlement</h1>
            </div>
        </header>

        <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
            <!-- Report Header Section -->
            <div class="mb-6 lg:mb-8">
                <x-report-header title="Shift Settlement" module="Finance" submodule="End of Shift" description="Complete end-of-shift reconciliation and cash count." />
            </div>

            <!-- Search & Date Filters -->
            <div class="w-full flex flex-col sm:flex-row items-center gap-2 bg-white p-2 rounded-3xl shadow-sm border border-slate-100 mb-6">
                <div class="relative w-full sm:w-44">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">calendar_month</span>
                    <input type="date" x-model="date" @change="searchNIK()"
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border-none focus:ring-2 focus:ring-primary/10 rounded-xl text-xs font-bold text-slate-600">
                </div>
                <div class="relative w-full sm:w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input type="text" x-model="nik" @keyup.enter="searchNIK()"
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border-none focus:ring-2 focus:ring-primary/10 rounded-xl text-sm font-bold"
                        placeholder="Masukkan NIK Kasir...">
                </div>
                <button @click="searchNIK()" 
                    class="w-full sm:w-auto px-6 py-2 bg-primary text-white rounded-2xl font-black text-xs hover:bg-primary-container transition-colors shadow-lg shadow-primary/20">
                    VERIFIKASI
                </button>
            </div>

        @if(!$nik)
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-24 text-center">

                <div class="w-24 h-24 rounded-full bg-primary/5 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-primary text-5xl">badge</span>
                </div>
                <h3 class="text-xl font-black text-on-surface mb-2">Input NIK Kasir</h3>
                <p class="text-slate-400 max-w-xs font-medium">Masukkan NIK kasir untuk memproses verifikasi uang tunai.</p>
            </div>
        @elseif(!$targetUser)
            <!-- User Not Found -->
            <div class="flex flex-col items-center justify-center py-24 text-center text-red-500">
                <span class="material-symbols-outlined text-6xl mb-4">person_off</span>
                <h3 class="text-xl font-black mb-1">Kasir Tidak Ditemukan</h3>
                <p class="text-slate-400 font-medium">Kasir dengan NIK <span class="font-bold">"{{ $nik }}"</span> tidak terdaftar di toko ini.</p>
                <button @click="nik = ''; window.location.href='/clerek/data'" class="mt-6 text-primary font-black text-sm uppercase tracking-widest border-b-2 border-primary">Kembali Cari</button>
            </div>
        @else
            <!-- Content Grid -->
            <div class="grid grid-cols-12 gap-6">
                <!-- Left Column -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <!-- User Profile Card -->
                    <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-bl-[4rem] -mr-8 -mt-8"></div>
                        <div class="flex items-center gap-4 mb-6 relative z-10">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-primary-container flex items-center justify-center text-white shadow-lg shadow-primary/20">
                                <span class="material-symbols-outlined text-3xl font-black">person</span>
                            </div>
                            <div>
                                <h3 class="font-black text-lg text-on-surface tracking-tight">{{ $targetUser->name }}</h3>
                                <p class="text-[10px] font-black text-primary uppercase tracking-widest">{{ $targetUser->nik }}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4 relative z-10">
                            <div class="flex justify-between py-2 border-b border-slate-50">
                                <span class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Store Branch</span>
                                <span class="font-black text-sm text-slate-700">{{ $targetUser->store->name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Status Hari Ini</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest {{ $pendingClerek ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $pendingClerek ? 'Menunggu Verifikasi' : 'Sudah Clerek' }}
                                </span>
                            </div>
                        </div>
                    </section>

                    @if($pendingClerek)
                    <!-- Sales Summary (Visible ONLY after count is confirmed) -->
                    <div x-show="countConfirmed" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                        <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                            <h3 class="font-black text-xs text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-sm">analytics</span>
                                Sales Summary (System)
                            </h3>
                            <div class="space-y-3">
                                @foreach([
                                    ['Tunai (Cash)', $pendingClerek->cash_sales, 'payments'],
                                    ['QRIS', $pendingClerek->qris_sales, 'qr_code_2'],
                                    ['Debit/Kredit', $pendingClerek->debit_sales + $pendingClerek->credit_sales, 'credit_card']
                                ] as $item)
                                <div class="p-3 bg-slate-50 rounded-2xl flex justify-between items-center group">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-primary text-xl opacity-50 group-hover:opacity-100">{{ $item[2] }}</span>
                                        <span class="text-xs font-bold text-slate-600">{{ $item[0] }}</span>
                                    </div>
                                    <span class="font-black text-on-surface text-sm">Rp{{ number_format($item[1], 0, ',', '.') }}</span>
                                </div>
                                @endforeach
                                
                                <div class="pt-6 mt-4 border-t-2 border-slate-50 flex justify-between items-end px-1">
                                    <div>
                                        <p class="text-[10px] font-black text-primary uppercase tracking-widest mb-1">Expected Cash</p>
                                        <span class="text-3xl font-black text-primary">Rp{{ number_format($pendingClerek->expected_cash, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Blind State Placeholder -->
                    <div x-show="!countConfirmed" class="bg-primary/5 rounded-3xl p-8 border-2 border-dashed border-primary/20 text-center">
                        <span class="material-symbols-outlined text-primary/30 text-5xl mb-4">visibility_off</span>
                        <p class="text-xs font-black text-primary/60 uppercase tracking-widest">Selesaikan hitung uang tunai untuk melihat ringkasan sistem</p>
                    </div>
                    @endif
                </div>

                @if($pendingClerek)
                <!-- Middle Column -->
                <div class="col-span-12 lg:col-span-5">
                    <section class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100 h-full flex flex-col relative">
                        <!-- Locked Overlay -->
                        <div x-show="countConfirmed" class="absolute inset-0 bg-white/10 backdrop-blur-[1px] z-10 rounded-[2.5rem] flex items-center justify-center">
                            <div class="bg-white px-6 py-3 rounded-full shadow-xl border border-slate-100 flex items-center gap-2">
                                <span class="material-symbols-outlined text-green-500">lock</span>
                                <span class="text-xs font-black text-slate-700 uppercase tracking-widest">Jumlah Sudah Terkunci</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mb-8">
                            <h3 class="text-xl font-black text-primary font-manrope tracking-tight flex items-center gap-2">
                                <span class="material-symbols-outlined font-black">calculate</span>
                                Input Uang Tunai
                            </h3>
                            <span class="bg-slate-50 px-3 py-1 rounded-full text-[10px] font-black text-slate-400 uppercase tracking-widest">Denominations</span>
                        </div>
                        
                        <div class="space-y-3 flex-1 overflow-y-auto pr-2 custom-scrollbar">
                            <template x-for="(denom, index) in denominations" :key="index">
                                <div class="grid grid-cols-12 items-center gap-4 group p-1 rounded-xl transition-all">
                                    <div class="col-span-4 text-xs font-black text-slate-400 group-hover:text-primary transition-colors" x-text="formatCurrency(denom.value)"></div>
                                    <div class="col-span-3">
                                        <input type="number" x-model.number="denom.qty" @input="calculateActual()" :disabled="countConfirmed"
                                            class="w-full bg-slate-50 border-none focus:ring-2 focus:ring-primary/10 rounded-xl text-center py-2 text-xs font-bold disabled:opacity-50"
                                            placeholder="0">
                                    </div>
                                    <div class="col-span-5 text-right font-black text-on-surface text-xs" x-text="formatCurrency(denom.value * (denom.qty || 0))"></div>
                                </div>
                            </template>
                            
                            <div class="grid grid-cols-12 items-center gap-4 pt-3 border-t border-slate-100">
                                <div class="col-span-4 text-xs font-black text-slate-400 uppercase tracking-widest">Koin / Lainnya</div>
                                <div class="col-span-3">
                                    <input type="number" x-model.number="otherCash" @input="calculateActual()" :disabled="countConfirmed"
                                        class="w-full bg-slate-50 border-none focus:ring-2 focus:ring-primary/10 rounded-xl text-center py-2 text-xs font-bold disabled:opacity-50"
                                        placeholder="0">
                                </div>
                                <div class="col-span-5 text-right font-black text-on-surface text-xs" x-text="formatCurrency(otherCash)"></div>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t-2 border-primary/5">
                            <div class="bg-primary/5 p-6 rounded-[2rem] border border-primary/10">
                                <label class="block text-[10px] font-black text-primary uppercase tracking-widest mb-2">Total Physical Cash (Actual)</label>
                                <div class="relative">
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 text-2xl font-black text-primary">Rp</span>
                                    <input type="number" x-model.number="actualCash" @input="calculateDifference()" :disabled="countConfirmed"
                                        @keyup.enter="confirmCount()"
                                        class="w-full bg-transparent border-none focus:ring-0 rounded-none py-2 pl-12 text-4xl font-black text-primary placeholder:text-primary/10 disabled:opacity-80"
                                        placeholder="0">
                                </div>
                            </div>
                        </div>

                        <button x-show="!countConfirmed" @click="confirmCount()" :disabled="actualCash <= 0"
                            class="mt-4 w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-black transition-colors flex items-center justify-center gap-2 shadow-lg">
                            <span class="material-symbols-outlined text-sm">lock</span>
                            Kunci & Lihat Hasil
                        </button>
                    </section>
                </div>

                <!-- Right Column -->
                <div class="col-span-12 lg:col-span-3 space-y-6">
                    <!-- Reconciliation Result (Visible ONLY after count is confirmed) -->
                    <div x-show="countConfirmed" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="space-y-6">
                        <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                            <h3 class="font-black text-xs text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-sm">balance</span>
                                Reconciliation
                            </h3>
                            <div class="space-y-6">
                                <div class="bg-slate-50 p-4 rounded-2xl">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Actual (Counted)</p>
                                    <p class="text-xl font-black text-on-surface" x-text="formatCurrency(actualCash)"></p>
                                </div>
                                <div class="p-5 rounded-3xl text-center shadow-lg border-2"
                                    :class="difference === 0 ? 'bg-green-50 border-green-200' : (difference < 0 ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200')">
                                    <p class="text-[10px] font-black uppercase tracking-tighter mb-1" :class="difference === 0 ? 'text-green-600' : (difference < 0 ? 'text-red-600' : 'text-yellow-600')">Difference (Selisih)</p>
                                    <p class="text-2xl font-black" :class="difference === 0 ? 'text-green-700' : (difference < 0 ? 'text-red-700' : 'text-yellow-700')"
                                        x-text="(difference > 0 ? '+' : '') + formatCurrency(difference)"></p>
                                    <span class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full" 
                                        :class="difference === 0 ? 'bg-green-200 text-green-800' : (difference < 0 ? 'bg-red-200 text-red-800' : 'bg-yellow-200 text-yellow-800')"
                                        x-text="difference === 0 ? 'BALANCED' : (difference < 0 ? 'SHORTAGE' : 'SURPLUS')"></span>
                                </div>
                            </div>
                        </section>

                        <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Notes & Observation</label>
                            <textarea x-model="notes" rows="3"
                                class="w-full bg-slate-50 border-none focus:ring-2 focus:ring-primary/10 rounded-2xl text-xs font-bold p-4"
                                placeholder="Explain any discrepancies..."></textarea>
                        </section>

                        <button @click="processClerek({{ $pendingClerek->id }})" :disabled="submitting"
                            class="w-full py-5 bg-gradient-to-br from-primary to-primary-container text-white rounded-[1.5rem] font-black text-sm shadow-xl shadow-primary/20 hover:scale-[0.98] transition-transform active:opacity-90 flex items-center justify-center gap-3 disabled:opacity-50">
                            <span x-show="!submitting">FINALISASI CLEREK</span>
                            <span x-show="submitting" class="material-symbols-outlined animate-spin text-xl font-black">autorenew</span>
                            <span x-show="!submitting" class="material-symbols-outlined text-xl font-black">check_circle</span>
                        </button>
                    </div>

                    <!-- Guidance Card -->
                    <div x-show="!countConfirmed" class="bg-white rounded-3xl p-6 border border-slate-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                <span class="material-symbols-outlined text-sm">info</span>
                            </div>
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Petunjuk Admin</h4>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex gap-2 items-start">
                                <span class="w-4 h-4 rounded-full bg-primary/10 text-primary text-[10px] font-black flex items-center justify-center flex-shrink-0">1</span>
                                <p class="text-[11px] font-bold text-slate-500 leading-tight">Hitung fisik uang tunai yang diserahkan kasir.</p>
                            </li>
                            <li class="flex gap-2 items-start">
                                <span class="w-4 h-4 rounded-full bg-primary/10 text-primary text-[10px] font-black flex items-center justify-center flex-shrink-0">2</span>
                                <p class="text-[11px] font-bold text-slate-500 leading-tight">Masukkan jumlah pecahan pada kolom input.</p>
                            </li>
                            <li class="flex gap-2 items-start">
                                <span class="w-4 h-4 rounded-full bg-primary/10 text-primary text-[10px] font-black flex items-center justify-center flex-shrink-0">3</span>
                                <p class="text-[11px] font-bold text-slate-500 leading-tight">Tekan Enter atau klik "Kunci" untuk membandingkan dengan sistem.</p>
                            </li>
                        </ul>
                    </div>
                </div>
                @else
                <!-- History Table -->
                <div class="col-span-12">
                    <section class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
                        <div class="flex justify-between items-center mb-8">
                            <h3 class="font-black text-xl text-on-surface flex items-center gap-3 font-manrope">
                                <span class="material-symbols-outlined text-primary bg-primary/5 p-2 rounded-xl">history</span>
                                Riwayat Clerek Kasir
                                <span class="text-xs font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-full" x-text="formatDateDisplay(date)"></span>
                            </h3>
                        </div>
                        <div class="overflow-x-auto rounded-2xl border border-slate-50">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                        <th class="py-5 px-6">ID Settlement</th>
                                        <th class="py-5 px-6">Shift</th>
                                        <th class="py-5 px-6 text-right">Total Jual</th>
                                        <th class="py-5 px-6 text-right">Diharapkan</th>
                                        <th class="py-5 px-6 text-right">Diterima</th>
                                        <th class="py-5 px-6 text-right">Selisih</th>
                                        <th class="py-5 px-6 text-center">Petugas Verif</th>
                                        <th class="py-5 px-6 text-center">Status</th>
                                        <th class="py-5 px-6 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @forelse($history as $item)
                                    <tr class="group hover:bg-slate-50/80 transition-all">
                                        <td class="py-5 px-6">
                                            <p class="text-xs font-black text-primary uppercase tracking-tighter">#CLRK-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</p>
                                            <p class="text-[9px] font-bold text-slate-400">{{ $item->closing_date->format('d M Y, H:i') }}</p>
                                        </td>
                                        <td class="py-5 px-6">
                                            <span class="px-3 py-1 bg-primary/5 text-primary rounded-full text-[9px] font-black uppercase tracking-widest">{{ $item->shift }}</span>
                                        </td>
                                        <td class="py-5 px-6 text-xs font-black text-right text-slate-700">Rp{{ number_format($item->total_sales, 0, ',', '.') }}</td>
                                        <td class="py-5 px-6 text-xs font-bold text-right text-slate-400">Rp{{ number_format($item->expected_cash, 0, ',', '.') }}</td>
                                        <td class="py-5 px-6 text-xs font-black text-right text-primary">Rp{{ number_format($item->actual_cash, 0, ',', '.') }}</td>
                                        <td class="py-5 px-6 text-xs font-black text-right">
                                            <span class="{{ $item->difference == 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $item->difference > 0 ? '+' : '' }}{{ number_format($item->difference, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="py-5 px-6 text-center">
                                            <p class="text-xs font-black text-slate-600">{{ $item->approver->name ?? '-' }}</p>
                                        </td>
                                        <td class="py-5 px-6 text-center">
                                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-green-100 text-green-700">COMPLETED</span>
                                        </td>
                                        <td class="py-5 px-6 text-center">
                                            <a href="/clerek/{{ $item->id }}/print" target="_blank" class="p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-all inline-flex items-center" title="Print Receipt">
                                                <span class="material-symbols-outlined text-lg">print</span>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="py-16 text-center">
                                            <div class="flex flex-col items-center">
                                                <span class="material-symbols-outlined text-4xl text-slate-200 mb-2">inbox</span>
                                                <p class="text-xs font-bold text-slate-400">Belum ada data clerek pada tanggal ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                @endif
            </div>
        @endif
        </div>
    </main>

    <script>
        function clerekAdmin() {
            return {
                nik: '{{ $nik }}',
                date: '{{ $date }}',
                loading: false,
                submitting: false,
                countConfirmed: false,
                actualCash: 0,
                otherCash: 0,
                difference: 0,
                notes: '',
                denominations: [
                    { value: 100000, qty: 0 },
                    { value: 50000, qty: 0 },
                    { value: 20000, qty: 0 },
                    { value: 10000, qty: 0 },
                    { value: 5000, qty: 0 },
                    { value: 2000, qty: 0 },
                    { value: 1000, qty: 0 },
                    { value: 500, qty: 0 },
                    { value: 200, qty: 0 },
                    { value: 100, qty: 0 },
                ],

                searchNIK() {
                    if (!this.nik && !this.date) return;
                    window.location.href = '/clerek/data?nik=' + (this.nik || '') + '&date=' + (this.date || '');
                },

                calculateActual() {
                    let total = this.denominations.reduce((sum, d) => sum + (d.value * (d.qty || 0)), 0);
                    this.actualCash = total + (this.otherCash || 0);
                },

                confirmCount() {
                    if (this.actualCash <= 0) return;
                    this.calculateDifference();
                    this.countConfirmed = true;
                    Swal.fire({
                        icon: 'success',
                        title: 'Jumlah Dikunci',
                        text: 'Ringkasan sistem sekarang ditampilkan.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },

                calculateDifference() {
                    const expected = {{ $pendingClerek->expected_cash ?? 0 }};
                    this.difference = (this.actualCash || 0) - expected;
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
                },

                formatDateDisplay(dateStr) {
                    if (!dateStr) return '';
                    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                },

                async processClerek(id) {
                    this.submitting = true;
                    try {
                        let res = await fetch('/clerek/process', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({
                                closing_id: id,
                                actual_cash: this.actualCash,
                                notes: this.notes
                            })
                        });
                        let data = await res.json();
                        if (data.success) {
                            Swal.fire({ 
                                icon: 'success', 
                                title: 'Berhasil', 
                                text: data.message,
                                showCancelButton: true,
                                confirmButtonText: 'Print Struk',
                                cancelButtonText: 'Tutup',
                                confirmButtonColor: '#000',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.open('/clerek/' + id + '/print', '_blank');
                                }
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                        }
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem' });
                    } finally {
                        this.submitting = false;
                    }
                }
            }
        }
    </script>
</x-layout>
