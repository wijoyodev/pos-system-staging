<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full bg-slate-50/50">

        <!-- TopNavBar Integration -->
        <header
            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl shadow-sm top-0 sticky z-30 font-manrope antialiased tracking-tight">
            <div class="flex justify-between items-center w-full px-4 lg:px-8 py-3 lg:py-4">
                <div class="flex items-center flex-1">
                    <div class="relative w-full max-w-md">
                        <h1 class="text-xl lg:text-2xl font-black tracking-tighter text-slate-900 dark:text-blue-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary font-black">outbox</span>
                            {{ $title }}
                        </h1>
                    </div>
                </div>
            </div>
        </header>

        <div class="p-4 lg:p-8 space-y-8 w-full">
            <x-report-header title="Operational Expenses" module="Finance" submodule="Expenses" description="Track and manage your store overheads with precision." />

            <!-- Input Form: Premium Card -->
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
                <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-primary rounded-full"></span>
                    Catat Pengeluaran Baru
                </h3>
                <form action="/expenses" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        @if(auth()->user()->isSuperAdmin())
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Toko</label>
                            <select name="store_id" required class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-primary/20">
                                <option value="">-- Pilih Toko --</option>
                                @foreach($stores as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kategori</label>
                            <select name="category" required class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-primary/20">
                                <option value="Gaji">Gaji / Salary</option>
                                <option value="Sewa">Sewa / Rent</option>
                                <option value="Listrik">Listrik</option>
                                <option value="Air">Air</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Perlengkapan">Perlengkapan / Supplies</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nominal (Rp)</label>
                            <input name="amount" required type="number" min="0" placeholder="0"
                                class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-primary/20">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tanggal</label>
                            <input name="expense_date" required type="date" value="{{ date('Y-m-d') }}"
                                class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-primary/20">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-end">
                        <div class="lg:col-span-3 space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Deskripsi / Keterangan</label>
                            <input name="description" placeholder="Berikan catatan singkat..."
                                class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div>
                            <button type="submit"
                                class="w-full py-3.5 bg-primary text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/20">
                                Simpan Pengeluaran
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- List Section -->
            <div class="space-y-4">
                <div class="flex justify-between items-center px-4">
                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Riwayat Pengeluaran</h3>
                    @if(auth()->user()->isSuperAdmin())
                    <form method="GET" class="flex items-center gap-2">
                        <select name="store_id" onchange="this.form.submit()" class="bg-white border-none rounded-xl py-1.5 px-4 text-xs font-bold shadow-sm">
                            <option value="">Semua Toko</option>
                            @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </form>
                    @endif
                </div>

                <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-xl shadow-slate-200/50 border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-8 py-6 text-[10px] font-black uppercase text-slate-400 tracking-widest">Tanggal</th>
                                    @if(auth()->user()->isSuperAdmin())
                                    <th class="px-8 py-6 text-[10px] font-black uppercase text-slate-400 tracking-widest">Toko</th>
                                    @endif
                                    <th class="px-8 py-6 text-[10px] font-black uppercase text-slate-400 tracking-widest">Kategori</th>
                                    <th class="px-8 py-6 text-[10px] font-black uppercase text-slate-400 tracking-widest">Keterangan</th>
                                    <th class="px-8 py-6 text-[10px] font-black uppercase text-slate-400 tracking-widest text-right">Nominal</th>
                                    <th class="px-8 py-6 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($expenses as $expense)
                                <tr class="group hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-6 text-sm font-bold text-slate-600">{{ $expense->expense_date->format('d M Y') }}</td>
                                    @if(auth()->user()->isSuperAdmin())
                                    <td class="px-8 py-6">
                                        <span class="text-xs font-black text-primary bg-primary/5 px-3 py-1 rounded-full uppercase tracking-tighter">{{ $expense->store->name ?? 'Global' }}</span>
                                    </td>
                                    @endif
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                            <span class="text-sm font-bold text-slate-800">{{ $expense->category }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-sm text-slate-400 italic max-w-xs truncate">{{ $expense->description ?? '-' }}</td>
                                    <td class="px-8 py-6 text-base font-black text-slate-900 text-right tracking-tight">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                    <td class="px-8 py-6 text-center">
                                        <form action="/expenses/{{ $expense->id }}" method="POST" onsubmit="return confirm('Hapus catatan ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-10 h-10 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-xl flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 scale-90 group-hover:scale-100">
                                                <span class="material-symbols-outlined text-lg font-black">delete</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mt-6 px-4">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    </main>
</x-layout>
