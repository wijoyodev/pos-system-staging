<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full">

        <!-- TopNavBar Shell -->
        <header
            class="top-0 z-40 sticky bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border-b border-slate-200/20 dark:border-slate-800/20 shadow-sm dark:shadow-none flex justify-between items-center w-full px-4 lg:px-6 py-3">
            <div class="flex items-center gap-4 lg:gap-8">
                <span class="text-base lg:text-xl font-bold text-blue-900 dark:text-blue-400 tracking-tight font-manrope">{{ $title }}</span>
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
        @php
            $profileDesc = auth()->user()->isAdmin() ? 'Kelola profil Anda dan pengaturan kasir di toko Anda.' : 'Kelola informasi profil dan keamanan akun Anda.';
        @endphp
        <div class="mb-6 lg:mb-8">
            <x-report-header title="User Settings" module="Settings" submodule="Profile" :description="$profileDesc" />
        </div>

            @if(session('success'))
                <div class="mb-8 p-4 bg-primary-container text-on-primary-container rounded-lg font-semibold tracking-wide">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-8 p-4 bg-error-container text-on-error-container rounded-lg font-semibold tracking-wide">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="/setting" method="POST" class="grid grid-cols-12 gap-8">
                @csrf

                <!-- Section 1: Profile Info -->
                <section class="col-span-12 lg:col-span-8 bg-surface-container-lowest rounded-xl p-8 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">person</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold font-manrope text-blue-900">Profile Information</h3>
                            <p class="text-sm text-on-surface-variant">Perbarui nama dan email akun Anda</p>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Nama Lengkap</label>
                                <input name="name"
                                    class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body"
                                    type="text" value="{{ old('name', auth()->user()->name) }}" required />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Email</label>
                                <input name="email"
                                    class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body"
                                    type="email" value="{{ old('email', auth()->user()->email) }}" required />
                            </div>
                        </div>
                        <div class="flex items-center gap-4 pt-2">
                            <div class="w-16 h-16 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">badge</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">NIK</p>
                                <p class="text-lg font-mono font-bold text-primary">{{ auth()->user()->nik }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 pt-2">
                            <div class="w-16 h-16 rounded-xl bg-secondary-container/20 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">shield_person</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Role</p>
                                <p class="text-lg font-bold text-on-surface">{{ auth()->user()->role_label }}</p>
                            </div>
                        </div>
                        @if(auth()->user()->store)
                        <div class="flex items-center gap-4 pt-2">
                            <div class="w-16 h-16 rounded-xl bg-tertiary/10 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-tertiary text-2xl" style="font-variation-settings: 'FILL' 1;">storefront</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Toko</p>
                                <p class="text-lg font-bold text-on-surface">{{ auth()->user()->store->branch->name }} - {{ auth()->user()->store->name }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </section>

                <!-- Section 2: Quick Info -->
                <section class="col-span-12 lg:col-span-4 space-y-8">
                    <div class="bg-surface-container-lowest rounded-xl p-8 shadow-sm">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600">info</span>
                            </div>
                            <h3 class="font-bold font-manrope text-blue-900">Akun Info</h3>
                        </div>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Terbuat</span>
                                <span class="font-bold text-on-surface">{{ auth()->user()->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Terakhir Login</span>
                                <span class="font-bold text-on-surface">{{ auth()->user()->updated_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 3: Security / Password Change -->
                <section class="col-span-12 bg-surface-container-lowest rounded-xl p-8 shadow-sm">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-lg bg-error-container/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-error">lock_reset</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold font-manrope text-blue-900">Keamanan</h3>
                            <p class="text-sm text-on-surface-variant">Ubah password akun Anda</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-6 max-w-2xl">
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Password Saat Ini</label>
                            <input type="password" name="password_current"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body"
                                placeholder="••••••••">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Password Baru</label>
                            <input type="password" name="password_new"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body"
                                placeholder="Minimal 6 karakter">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirm"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body"
                                placeholder="Ulangi password baru">
                        </div>
                    </div>
                </section>

                <!-- Save/Action Bar -->
                <div class="col-span-12 flex justify-end items-center gap-4 mt-4 py-6 border-t border-slate-200">
                    <button type="reset"
                        class="px-8 py-3 bg-transparent text-primary font-bold font-manrope rounded-lg hover:bg-slate-100 transition-all">
                        Reset Forms
                    </button>
                    <button type="submit"
                        class="px-10 py-3 bg-primary-container text-white font-bold font-manrope rounded-lg shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                        Save Changes
                    </button>
                </div>
            </form>

            <!-- Cashier Management (Admin only) -->
            @if(auth()->user()->isAdmin() && $cashiers->count() > 0)
            <div class="mt-12">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">group</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold font-manrope text-blue-900">Kelola Kasir</h2>
                        <p class="text-sm text-on-surface-variant">Edit profil & reset password kasir di toko Anda</p>
                    </div>
                </div>

                <div class="space-y-6">
                    @foreach($cashiers as $cashier)
                    <form action="/setting/cashier/{{ $cashier->id }}" method="POST" class="bg-surface-container-lowest rounded-xl p-6 lg:p-8 shadow-sm">
                        @csrf
                        <div class="flex items-start gap-4 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-blue-700 text-xl">person</span>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold font-manrope text-blue-900">{{ $cashier->name }}</h3>
                                <p class="text-xs text-slate-500 font-mono">NIK: {{ $cashier->nik }} · Terdaftar {{ $cashier->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-6">
                            <div>
                                <label class="block text-xs font-semibold mb-2 text-on-surface-variant font-label uppercase tracking-wider">Nama</label>
                                <input name="name" value="{{ old('name', $cashier->name) }}"
                                    class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-2.5 px-4 font-body text-sm" required />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-2 text-on-surface-variant font-label uppercase tracking-wider">Email</label>
                                <input name="email" type="email" value="{{ old('email', $cashier->email) }}"
                                    class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-2.5 px-4 font-body text-sm" required />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-2 text-on-surface-variant font-label uppercase tracking-wider">Reset Password <span class="normal-case font-normal text-slate-400">(opsional)</span></label>
                                <input type="password" name="password"
                                    class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-2.5 px-4 font-body text-sm"
                                    placeholder="Kosongkan jika tidak diubah" />
                            </div>
                        </div>
                        <div class="flex justify-end mt-6 pt-4 border-t border-slate-100">
                            <button type="submit"
                                class="px-6 py-2.5 bg-primary-container text-white text-sm font-bold font-manrope rounded-lg shadow-md hover:scale-[1.02] active:scale-95 transition-all">
                                Update Kasir
                            </button>
                        </div>
                    </form>
                    @endforeach
                </div>
            </div>
            @elseif(auth()->user()->isAdmin())
            <div class="mt-12 bg-surface-container-lowest rounded-xl p-8 text-center">
                <span class="material-symbols-outlined text-6xl text-slate-300 mb-4 block">person_search</span>
                <h3 class="text-lg font-bold text-on-surface mb-2">Belum ada kasir</h3>
                <p class="text-sm text-slate-500">Hubungi Super Admin untuk menambahkan kasir ke toko Anda.</p>
            </div>
            @endif
        </div>
    </main>
</x-layout>