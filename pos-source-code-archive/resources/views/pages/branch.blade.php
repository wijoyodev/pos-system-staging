<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full bg-slate-50/50" x-data="branchManager()">

        <!-- Modern Header -->
        <header class="bg-white/80 backdrop-blur-xl sticky top-0 z-30 border-b border-slate-200/50 p-4 lg:p-6">
            <div class="w-full flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl lg:text-4xl font-black text-slate-900 tracking-tighter">Kelola <span class="text-primary italic">Cabang</span></h1>
                    <p class="text-slate-400 text-xs font-black uppercase tracking-widest mt-1">Struktur Bisnis & Manajemen Lokasi</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="showAddModal = true" 
                        class="flex items-center gap-2 px-6 py-3 bg-primary text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-xl shadow-primary/20 hover:scale-105 active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-sm font-black">add</span>
                        Tambah Cabang
                    </button>
                </div>
            </div>
        </header>

        <div class="p-4 lg:p-8 w-full space-y-8">
        <!-- Report Header Section -->
        <div class="mb-6 lg:mb-8">
            <x-report-header title="{{ $title ?? 'Page' }}" />
        </div>

            @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-[1.5rem] flex items-center gap-3 animate-in fade-in duration-500">
                <span class="material-symbols-outlined text-emerald-500 font-black">check_circle</span>
                <span class="text-sm font-black uppercase tracking-widest">{{ session('success') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-100 text-red-700 rounded-[1.5rem] flex items-center gap-3 animate-in fade-in duration-500">
                <span class="material-symbols-outlined text-red-500 font-black">error</span>
                <span class="text-sm font-black uppercase tracking-widest">{{ $errors->first() }}</span>
            </div>
            @endif

            <!-- Modal: Tambah Branch -->
            <div x-show="showAddModal" 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-cloak
                 style="display: none;">
                <div @click.away="showAddModal = false" 
                     class="bg-white rounded-[2.5rem] w-[calc(100%-2rem)] md:w-full max-w-lg overflow-hidden shadow-2xl">
                    <div class="bg-primary p-8 text-white relative">
                        <h3 class="text-2xl font-black tracking-tight mb-1">Tambah Branch Baru</h3>
                        <p class="text-blue-100/70 text-xs font-black uppercase tracking-widest">Detail Lokasi Bisnis</p>
                        <button @click="showAddModal = false" class="absolute top-6 right-6 w-10 h-10 bg-white/20 hover:bg-white/30 rounded-2xl flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined font-black">close</span>
                        </button>
                    </div>
                    <form action="/branches" method="POST" class="p-8 space-y-6">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Branch</label>
                                <input type="text" name="name" required placeholder="Contoh: Pusat, Cabang Malang"
                                    class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-primary/20">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Kota</label>
                                    <input type="text" name="city" placeholder="Malang"
                                        class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-primary/20">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Alamat</label>
                                    <input type="text" name="address" placeholder="Jl. Raya No. 1"
                                        class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-primary/20">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-800 transition-all shadow-xl">
                            Simpan Branch
                        </button>
                    </form>
                </div>
            </div>

            <!-- Modal: Tambah Toko ke Branch -->
            <div x-show="showAddStoreModal" 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-cloak
                 style="display: none;">
                <div @click.away="showAddStoreModal = false" 
                     class="bg-white rounded-[2.5rem] w-full max-w-lg overflow-hidden shadow-2xl">
                    <div class="bg-slate-900 p-8 text-white relative">
                        <h3 class="text-2xl font-black tracking-tight mb-1">Tambah Toko Baru</h3>
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Cabang: <span class="text-primary italic" x-text="selectedBranchName"></span></p>
                        <button @click="showAddStoreModal = false" class="absolute top-6 right-6 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-2xl flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined font-black">close</span>
                        </button>
                    </div>
                    <form action="/stores" method="POST" class="p-8 space-y-6">
                        @csrf
                        <input type="hidden" name="branch_id" :value="selectedBranchId">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Toko</label>
                                <input type="text" name="name" required placeholder="Contoh: Toko A, Outlet Utama"
                                    class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-primary/20">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Kode Toko (Unique)</label>
                                <input type="text" name="code" required placeholder="Contoh: TKO-01"
                                    class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-primary/20">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-4 bg-primary text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:brightness-110 transition-all shadow-xl shadow-primary/20">
                            Daftarkan Toko
                        </button>
                    </form>
                </div>
            </div>

            <!-- Branch Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                @forelse($branches as $branch)
                <div class="group bg-white rounded-[2.5rem] overflow-hidden shadow-xl shadow-slate-200/50 border border-slate-100 hover:scale-[1.02] transition-all duration-500">
                    <div class="p-8">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all duration-500">
                                <span class="material-symbols-outlined text-3xl font-black">location_on</span>
                            </div>
                            <div class="flex gap-2">
                                <form action="/branches/{{ $branch->id }}" method="POST" onsubmit="return confirm('Hapus branch? Semua data toko terkait akan terdampak.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-10 h-10 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-xl flex items-center justify-center transition-all">
                                        <span class="material-symbols-outlined text-lg font-black">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <h3 class="text-xl font-black text-slate-800 mb-1">{{ $branch->name }}</h3>
                        <div class="flex items-center gap-2 text-slate-400 text-[10px] font-black uppercase tracking-widest mb-6">
                            <span class="material-symbols-outlined text-[14px]">apartment</span>
                            {{ $branch->city ?? 'Lokasi tidak ditentukan' }}
                            <span class="mx-1">•</span>
                            {{ $branch->stores->count() }} Toko Aktif
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center border-b border-slate-50 pb-2 mb-4">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Daftar Toko</p>
                                <button @click="openAddStore('{{ $branch->id }}', '{{ $branch->name }}')" 
                                    class="text-[9px] font-black text-primary uppercase tracking-widest flex items-center gap-1 hover:underline">
                                    <span class="material-symbols-outlined text-[12px]">add_circle</span> Tambah Toko
                                </button>
                            </div>
                            @forelse($branch->stores as $store)
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl group/store hover:bg-slate-100 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-white flex items-center justify-center shadow-sm">
                                        <span class="material-symbols-outlined text-sm text-slate-400">store</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-700 leading-none mb-1">{{ $store->name }}</p>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">{{ $store->code }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <button @click="toggleStatus('{{ $store->id }}')" 
                                        class="flex items-center gap-1.5 px-3 py-1 rounded-full transition-all group/toggle"
                                        :class="stores['{{ $store->id }}'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="stores['{{ $store->id }}'] === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                                        <span class="text-[8px] font-black uppercase tracking-widest" x-text="stores['{{ $store->id }}']"></span>
                                    </button>
                                    
                                    <form action="/stores/{{ $store->id }}" method="POST" onsubmit="return confirm('Hapus toko ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-slate-300 hover:text-red-500 transition-colors">
                                            <span class="material-symbols-outlined text-sm font-black">close</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-6 border-2 border-dashed border-slate-100 rounded-[2rem]">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Belum ada toko</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-20 bg-white rounded-[3rem] border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-300 mb-6">
                        <span class="material-symbols-outlined text-5xl">location_off</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 mb-1">Belum Ada Cabang</h3>
                    <p class="text-slate-400 text-sm font-bold max-w-xs">Mulai kelola bisnis Anda dengan menambahkan cabang pertama.</p>
                </div>
                @endforelse
            </div>
        </div>
    </main>

    <script>
        function branchManager() {
            return {
                showAddModal: false,
                showAddStoreModal: false,
                selectedBranchId: '',
                selectedBranchName: '',
                stores: {
                    @foreach($branches as $branch)
                        @foreach($branch->stores as $store)
                            '{{ $store->id }}': '{{ $store->status }}',
                        @endforeach
                    @endforeach
                },
                openAddStore(id, name) {
                    this.selectedBranchId = id;
                    this.selectedBranchName = name;
                    this.showAddStoreModal = true;
                },
                async toggleStatus(id) {
                    try {
                        const response = await fetch(`/stores/${id}/toggle`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.stores[id] = data.status;
                            Swal.fire({
                                icon: 'success',
                                title: 'Status Diperbarui',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        }
                    } catch (error) {
                        console.error('Error toggling status:', error);
                    }
                }
            }
        }
    </script>
</x-layout>