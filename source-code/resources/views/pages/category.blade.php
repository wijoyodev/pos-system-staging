<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full">
        <header class="bg-white/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm">
            <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
                <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900">Kategori & Satuan</h1>
            </div>
        </header>

        <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
            <div class="max-w-6xl mx-auto">

                @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6 flex items-start gap-3">
                    <span class="material-symbols-outlined text-green-500 flex-shrink-0 mt-0.5">check_circle</span>
                    <p class="text-sm font-bold text-green-700">{{ session('success') }}</p>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-green-400 hover:text-green-600">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Categories Card --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-indigo-600 text-lg">category</span>
                                </div>
                                <h3 class="text-sm font-extrabold text-slate-800">Kategori</h3>
                            </div>
                            <span class="text-xs font-bold text-slate-400">{{ $categories->count() }} total</span>
                        </div>

                        @if(auth()->user()->isSuperAdmin())
                        <form action="/category" method="POST" class="px-5 py-3 border-b border-slate-50 flex gap-2">
                            @csrf
                            <input name="name" required placeholder="Nama kategori..." type="text"
                                class="flex-1 px-3 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/10 text-sm outline-none @error('name') ring-2 ring-red-300 @enderror" />
                            <button type="submit"
                                class="px-4 py-2 bg-primary text-white font-bold rounded-lg text-sm hover:bg-primary/90 transition-all active:scale-95">
                                Tambah
                            </button>
                        </form>
                        @error('name')
                        <p class="px-5 pb-2 text-xs font-bold text-red-500">{{ $message }}</p>
                        @enderror
                        @endif

                        <div class="divide-y divide-slate-50 max-h-96 overflow-y-auto no-scrollbar">
                            @forelse($categories as $category)
                            <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
                                        <span class="material-symbols-outlined text-slate-500 text-sm">category</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-700">{{ $category->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $category->products_count }} produk</p>
                                    </div>
                                </div>
                                @if(auth()->user()->isSuperAdmin())
                                <form action="/category/{{ $category->id }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-all">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </form>
                                @endif
                            </div>
                            @empty
                            <div class="px-5 py-8 text-center text-sm text-slate-400">Belum ada kategori</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Units Card --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-teal-50 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-teal-600 text-lg">straighten</span>
                                </div>
                                <h3 class="text-sm font-extrabold text-slate-800">Satuan</h3>
                            </div>
                            <span id="unit-count" class="text-xs font-bold text-slate-400">0 total</span>
                        </div>

                        @if(auth()->user()->isSuperAdmin())
                        <form id="unit-form" class="px-5 py-3 border-b border-slate-50 flex gap-2">
                            @csrf
                            <input id="unit-input" name="name" required placeholder="Nama satuan..." type="text"
                                class="flex-1 px-3 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/10 text-sm outline-none" />
                            <button type="submit"
                                class="px-4 py-2 bg-primary text-white font-bold rounded-lg text-sm hover:bg-primary/90 transition-all active:scale-95">
                                Tambah
                            </button>
                        </form>
                        @endif

                        <div id="unit-list" class="divide-y divide-slate-50 max-h-96 overflow-y-auto no-scrollbar">
                            <div class="px-5 py-8 text-center text-sm text-slate-400">Memuat...</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        loadUnits();

        const form = document.getElementById('unit-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const input = document.getElementById('unit-input');
                const name = input.value.trim();
                if (!name) return;

                fetch('/units', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value },
                    body: JSON.stringify({ name })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        loadUnits();
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan' });
                    }
                })
                .catch(() => {
                    // success redirect from server
                    input.value = '';
                    loadUnits();
                });
            });
        }
    });

    function loadUnits() {
        fetch('/units')
            .then(r => r.json())
            .then(units => {
                const list = document.getElementById('unit-list');
                const count = document.getElementById('unit-count');
                count.textContent = units.length + ' total';

                if (units.length === 0) {
                    list.innerHTML = '<div class="px-5 py-8 text-center text-sm text-slate-400">Belum ada satuan</div>';
                    return;
                }

                list.innerHTML = units.map(u => `
                    <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-slate-500 text-sm">straighten</span>
                            </div>
                            <p class="text-sm font-bold text-slate-700">${u.name}</p>
                        </div>
                        ${'{{ auth()->user()->isSuperAdmin() ? '' : '' }}'
                            .trim() ?
                        `<form onsubmit="event.preventDefault(); deleteUnit(${u.id})">
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-all">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </form>` : ''}
                    </div>
                `).join('');
            });
    }

    function deleteUnit(id) {
        if (!confirm('Hapus satuan ini?')) return;
        fetch('/units/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value }
        })
        .then(() => {
            loadUnits();
            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Satuan berhasil dihapus', timer: 1500, showConfirmButton: false });
        });
    }
    </script>
</x-layout>
