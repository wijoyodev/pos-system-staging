<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full">

        <!-- TopNavBar -->
        <header
            class="top-0 z-40 sticky bg-white/70 backdrop-blur-xl border-b border-slate-200/20 shadow-sm flex justify-between items-center w-full px-4 lg:px-6 py-3">
            <div class="flex items-center gap-4 lg:gap-8">
                <span class="text-base lg:text-xl font-bold text-blue-900 tracking-tight font-manrope">{{ $title }}</span>
            </div>
            <div class="flex items-center gap-2 lg:gap-4">
                <div class="h-6 lg:h-8 w-[1px] bg-slate-200 mx-1 lg:mx-2 hidden sm:block"></div>
                <div class="flex items-center gap-2 lg:gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-[10px] lg:text-xs font-bold text-on-surface font-manrope">{{ auth()->user()->name }}</p>
                        <p class="text-[8px] lg:text-[10px] text-slate-500 font-body uppercase tracking-widest">{{ auth()->user()->role_label }}</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="w-full px-4 lg:px-8 py-6 lg:py-10">
            <div class="mb-6 lg:mb-8">
                <x-report-header title="User Management" module="Administration" submodule="User Management" description="Kelola akses pengguna dan peran sistem.">
                    <x-slot name="actions">
                        <button onclick="openModal('addUserModal')" class="flex items-center gap-2 px-5 py-3 bg-primary text-white font-bold rounded-lg shadow-md hover:bg-primary-container active:scale-95 transition-all text-xs lg:text-sm cursor-pointer">
                            <span class="material-symbols-outlined text-lg">person_add</span>
                            Tambah User
                        </button>
                    </x-slot>
                </x-report-header>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl font-semibold text-sm flex items-center gap-3 animate-fade-in">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl font-semibold text-sm flex items-center gap-3">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl font-semibold text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Filter & Search Bar -->
            <div class="flex flex-col sm:flex-row gap-3 mb-6">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">search</span>
                    <input type="text" id="searchInput" placeholder="Cari nama atau email..."
                        class="w-full pl-10 pr-4 py-3 bg-surface-container-lowest border-none rounded-xl focus:ring-2 focus:ring-primary/10 font-body text-sm shadow-sm"
                        value="{{ request('search') }}"
                        onkeydown="if(event.key==='Enter'){window.location.href='/users?search='+this.value+'&role='+(document.getElementById('roleFilter').value)}" />
                </div>
                <select id="roleFilter"
                    class="px-4 py-3 bg-surface-container-lowest border-none rounded-xl focus:ring-2 focus:ring-primary/10 font-body text-sm shadow-sm min-w-[160px]"
                    onchange="window.location.href='/users?role='+this.value+'&search='+(document.getElementById('searchInput').value)">
                    <option value="">Semua Role</option>
                    <option value="kasir" {{ request('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-3 gap-4 mb-8">
                <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-slate-600 text-lg">group</span>
                        </div>
                        <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Total Users</span>
                    </div>
                    <p class="text-2xl font-extrabold font-manrope text-on-surface">{{ $users->count() }}</p>
                </div>
                <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600 text-lg">admin_panel_settings</span>
                        </div>
                        <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Admin</span>
                    </div>
                    <p class="text-2xl font-extrabold font-manrope text-on-surface">{{ $users->whereIn('role', ['admin', 'super_admin'])->count() }}</p>
                </div>
                <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-emerald-600 text-lg">point_of_sale</span>
                        </div>
                        <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Kasir</span>
                    </div>
                    <p class="text-2xl font-extrabold font-manrope text-on-surface">{{ $users->where('role', 'kasir')->count() }}</p>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/80">
                                <th class="px-6 py-4 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider font-label">User</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider font-label">NIK</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider font-label">Email</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider font-label">Role</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider font-label">Dibuat</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider font-label text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($users as $user)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0
                                            @switch($user->role)
                                                @case('super_admin') bg-purple-100 text-purple-700 @break
                                                @case('admin') bg-blue-100 text-blue-700 @break
                                                @default bg-slate-100 text-slate-600
                                            @endswitch
                                        ">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <span class="font-semibold text-sm text-on-surface">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-mono text-slate-600">{{ $user->nik }}</td>
                                <td class="px-6 py-4 text-sm text-on-surface-variant font-body">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider
                                        @switch($user->role)
                                            @case('super_admin') bg-purple-100 text-purple-700 @break
                                            @case('admin') bg-blue-100 text-blue-700 @break
                                            @default bg-slate-100 text-slate-600
                                        @endswitch
                                    ">
                                        @switch($user->role)
                                            @case('super_admin')
                                                <span class="material-symbols-outlined text-xs mr-1">shield</span>
                                                @break
                                            @case('admin')
                                                <span class="material-symbols-outlined text-xs mr-1">admin_panel_settings</span>
                                                @break
                                            @default
                                                <span class="material-symbols-outlined text-xs mr-1">person</span>
                                        @endswitch
                                        {{ $user->role_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-on-surface-variant font-body">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button onclick="populateEditForm({id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', email: '{{ $user->email }}', role: '{{ $user->role }}', store_id: {{ $user->store_id ?? 'null' }}})"
                                            class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Edit User">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </button>
                                        @if($user->id !== auth()->id())
                                        <button onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                            class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Hapus User">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-slate-400 text-3xl">group_off</span>
                                        </div>
                                        <p class="text-sm text-on-surface-variant font-medium">Tidak ada user ditemukan.</p>
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

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- ADD USER MODAL -->
    <!-- ══════════════════════════════════════════════════════ -->
    <div id="addUserModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('addUserModal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-[calc(100%-2rem)] md:w-full max-w-lg p-0 overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
             id="addUserModalContent">
            <!-- Modal Header -->
            <div class="px-8 pt-8 pb-4">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">person_add</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold font-manrope text-blue-900">Tambah User Baru</h3>
                        <p class="text-sm text-on-surface-variant">Buat akun pengguna baru untuk sistem</p>
                    </div>
                </div>
            </div>
            <!-- Modal Body -->
            <form action="/users" method="POST" class="px-8 pb-8">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 uppercase tracking-wider">Nama Lengkap</label>
                        <input type="text" name="name" required placeholder="Masukkan nama lengkap"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 uppercase tracking-wider">NIK <span class="text-slate-400 normal-case">(otomatis)</span></label>
                        <input type="text" id="addNik" disabled
                            class="w-full bg-surface-container-high rounded-lg border-none py-3 px-4 font-mono text-sm text-slate-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 uppercase tracking-wider">Email</label>
                        <input type="email" name="email" required placeholder="nama@email.com"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 uppercase tracking-wider">Password</label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter" minlength="6"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 uppercase tracking-wider">Role</label>
                        <select name="role" required id="addRole"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body text-sm"
                            onchange="toggleStoreField()">
                            <option value="kasir">Kasir</option>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div id="storeField">
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 uppercase tracking-wider">Toko</label>
                        <select name="store_id"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body text-sm">
                            <option value="">Pilih Toko</option>
                            @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->branch->name }} - {{ $store->name }} ({{ $store->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                    <button type="button" onclick="closeModal('addUserModal')"
                        class="px-6 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Batal</button>
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-bold bg-primary-container text-white rounded-lg shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- EDIT USER MODAL -->
    <!-- ══════════════════════════════════════════════════════ -->
    <div id="editUserModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('editUserModal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-[calc(100%-2rem)] md:w-full max-w-lg p-0 overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
             id="editUserModalContent">
            <div class="px-8 pt-8 pb-4">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-600" style="font-variation-settings: 'FILL' 1;">edit</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold font-manrope text-blue-900">Edit User</h3>
                        <p class="text-sm text-on-surface-variant">Perbarui informasi pengguna</p>
                    </div>
                </div>
            </div>
            <form id="editUserForm" method="POST" class="px-8 pb-8">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 uppercase tracking-wider">Nama Lengkap</label>
                        <input type="text" name="name" id="editName" required
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 uppercase tracking-wider">NIK</label>
                        <input type="text" id="editNik" disabled
                            class="w-full bg-surface-container-high rounded-lg border-none py-3 px-4 font-mono text-sm text-slate-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 uppercase tracking-wider">Email</label>
                        <input type="email" name="email" id="editEmail" required
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 uppercase tracking-wider">Password Baru <span class="text-slate-400 normal-case">(kosongkan jika tidak diubah)</span></label>
                        <input type="password" name="password" placeholder="Minimal 6 karakter" minlength="6"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 uppercase tracking-wider">Role</label>
                        <select name="role" id="editRole" required
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body text-sm"
                            onchange="toggleEditStoreField()">
                            <option value="kasir">Kasir</option>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div id="editStoreField">
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 uppercase tracking-wider">Toko</label>
                        <select name="store_id" id="editStoreId"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body text-sm">
                            <option value="">Pilih Toko</option>
                            @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->branch->name }} - {{ $store->name }} ({{ $store->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                    <button type="button" onclick="closeModal('editUserModal')"
                        class="px-6 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Batal</button>
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-bold bg-primary-container text-white rounded-lg shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- DELETE CONFIRM MODAL -->
    <!-- ══════════════════════════════════════════════════════ -->
    <div id="deleteUserModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('deleteUserModal')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-[calc(100%-2rem)] md:w-full max-w-md p-0 overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
             id="deleteUserModalContent">
            <div class="p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
                    <span class="material-symbols-outlined text-red-600 text-3xl">warning</span>
                </div>
                <h3 class="text-xl font-bold font-manrope text-on-surface mb-2">Hapus User?</h3>
                <p class="text-sm text-on-surface-variant font-body">Anda yakin ingin menghapus user <strong id="deleteUserName"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <form id="deleteUserForm" method="POST" class="px-8 pb-8">
                @csrf
                @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('deleteUserModal')"
                        class="flex-1 px-6 py-3 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                    <button type="submit"
                        class="flex-1 px-6 py-3 text-sm font-bold bg-red-600 text-white rounded-lg hover:bg-red-700 active:scale-95 transition-all">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            const content = document.getElementById(id + 'Content');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            const content = document.getElementById(id + 'Content');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        }

        function openDeleteModal(userId, name) {
            document.getElementById('deleteUserForm').action = '/users/' + userId;
            document.getElementById('deleteUserName').textContent = name;
            openModal('deleteUserModal');
        }

        function toggleStoreField() {
            const role = document.getElementById('addRole').value;
            const storeField = document.getElementById('storeField');
            storeField.style.display = (role === 'super_admin') ? 'none' : 'block';
        }

        function toggleEditStoreField() {
            const role = document.getElementById('editRole').value;
            const storeField = document.getElementById('editStoreField');
            storeField.style.display = (role === 'super_admin') ? 'none' : 'block';
        }

        function populateEditForm(user) {
            document.getElementById('editUserForm').action = '/users/' + user.id;
            document.getElementById('editName').value = user.name;
            document.getElementById('editEmail').value = user.email;
            document.getElementById('editNik').value = user.nik || '';
            document.getElementById('editRole').value = user.role;
            document.getElementById('editStoreId').value = user.store_id || '';
            toggleEditStoreField();
            openModal('editUserModal');
        }

        function generatePreviewNik() {
            const today = new Date();
            const yy = today.getFullYear().toString().slice(-2);
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            document.getElementById('addNik').value = `${yy}${mm}****`;
        }

        // Initialize
        toggleStoreField();
        generatePreviewNik();

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                ['addUserModal', 'editUserModal', 'deleteUserModal'].forEach(id => {
                    const modal = document.getElementById(id);
                    if (!modal.classList.contains('hidden')) {
                        closeModal(id);
                    }
                });
            }
        });
    </script>

    <style>
        #storeField, #editStoreField {
            display: none;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</x-layout>
