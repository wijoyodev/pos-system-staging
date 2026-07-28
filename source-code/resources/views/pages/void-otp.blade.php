<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col w-full overflow-hidden" x-data="voidOtpPage()">

    <!-- Top Bar -->
    <header class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">{{ $title ?? 'Page' }}</h1>
      </div>
    </header>


        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <div class="mb-6 lg:mb-8">
                <x-report-header title="Void OTP Generator" module="Transaction" submodule="Void OTP" description="Generate kode OTP untuk otorisasi pembatalan transaksi" />
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-4 sm:p-6 lg:p-8 bg-surface overflow-y-auto">
            <div class="w-full space-y-6">
                <!-- Search Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="bg-primary/5 p-4 border-b border-primary/10">
                        <h2 class="text-sm font-bold text-primary flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">search</span>
                            Cari Transaksi
                        </h2>
                        <p class="text-[10px] text-slate-500 mt-0.5">Masukkan nomor struk/invoice yang akan di-void</p>
                    </div>
                    <div class="p-6">
                        <div class="flex gap-3">
                            <div class="flex-1 relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-xl">receipt_long</span>
                                <input type="text" x-model="searchInvoice" @keydown.enter="searchTransaction()"
                                    class="w-full pl-12 pr-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-primary/20 outline-none"
                                    placeholder="Contoh: INV-20260518-001" />
                            </div>
                            <button @click="searchTransaction()" :disabled="searching"
                                class="px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm shadow-md hover:bg-primary-container transition-all disabled:opacity-50 flex items-center gap-2">
                                <span x-show="!searching" class="material-symbols-outlined text-lg">search</span>
                                <span x-show="searching" class="material-symbols-outlined text-lg animate-spin">progress_activity</span>
                                <span x-text="searching ? 'Mencari...' : 'Cari'"></span>
                            </button>
                        </div>

                        <!-- Search Result -->
                        <div x-show="transactionFound" x-transition class="mt-6 p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <div class="flex items-start gap-3 mb-4">
                                <div class="w-10 h-10 bg-primary/10 text-primary rounded-xl flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-xl">receipt</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-bold text-on-surface" x-text="transaction.invoice_id"></h3>
                                    <p class="text-xs text-slate-500" x-text="transaction.created_at"></p>
                                </div>
                                <span class="px-2 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold rounded uppercase" x-text="transaction.payment_method"></span>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div>
                                    <span class="text-slate-400">Kasir</span>
                                    <p class="font-bold text-on-surface" x-text="transaction.cashier_name"></p>
                                </div>
                                <div>
                                    <span class="text-slate-400">Total</span>
                                    <p class="font-bold text-primary" x-text="formatCurrency(transaction.total_amount)"></p>
                                </div>
                                <div>
                                    <span class="text-slate-400">Items</span>
                                    <p class="font-bold text-on-surface" x-text="transaction.items_count + ' produk'"></p>
                                </div>
                                <div>
                                    <span class="text-slate-400">Status</span>
                                    <p class="font-bold text-green-600">Aktif</p>
                                </div>
                            </div>
                        </div>

                        <!-- No Result -->
                        <div x-show="searchNoResult" x-transition class="mt-6 p-6 text-center">
                            <span class="material-symbols-outlined text-4xl text-slate-300">search_off</span>
                            <p class="text-sm text-slate-500 font-bold mt-2" x-text="searchErrorMessage"></p>
                        </div>
                    </div>
                </div>

                <!-- OTP Generation Section -->
                <div x-show="transactionFound" x-transition
                    class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="bg-amber-50 p-4 border-b border-amber-100">
                        <h2 class="text-sm font-bold text-amber-700 flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">vpn_key</span>
                            Generate OTP
                        </h2>
                        <p class="text-[10px] text-amber-600/70 mt-0.5">Pilih admin yang akan mengotorisasi void</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Admin Otorisasi</label>
                            <select x-model="selectedAdminId" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-amber-100">
                                <option value="">-- Pilih Admin --</option>
                                <template x-for="admin in admins" :key="admin.id">
                                    <option :value="admin.id" x-text="admin.name + ' (' + admin.role + ')'"></option>
                                </template>
                            </select>
                        </div>

                        <button @click="generateOtp()" :disabled="generatingOtp || !selectedAdminId"
                            class="w-full py-4 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-amber-200 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <span x-show="!generatingOtp" class="material-symbols-outlined text-lg">lock_open</span>
                            <span x-show="generatingOtp" class="material-symbols-outlined text-lg animate-spin">progress_activity</span>
                            <span x-text="generatingOtp ? 'Generating...' : 'Generate OTP'"></span>
                        </button>

                        <!-- OTP Result -->
                        <div x-show="otpGenerated" x-transition class="p-6 bg-gradient-to-br from-primary to-primary-container rounded-xl text-center">
                            <p class="text-white/70 text-xs font-bold uppercase tracking-widest mb-2">Kode OTP Void</p>
                            <p class="text-5xl font-mono font-black text-white tracking-widest mb-3" x-text="otpCode"></p>
                            <div class="flex items-center justify-center gap-4 text-white/80 text-xs">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">person</span>
                                    <span x-text="otpAdminName"></span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">schedule</span>
                                    <span x-text="'Exp: ' + otpExpiresAt"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function voidOtpPage() {
            return {
                searchInvoice: '',
                searching: false,
                transactionFound: false,
                searchNoResult: false,
                searchErrorMessage: '',
                transaction: {},
                admins: [],
                selectedAdminId: '',
                generatingOtp: false,
                otpGenerated: false,
                otpCode: '',
                otpAdminName: '',
                otpExpiresAt: '',

                async searchTransaction() {
                    if (!this.searchInvoice.trim()) {
                        Swal.fire({ icon: 'warning', title: 'Input Kosong', text: 'Masukkan nomor struk/invoice.' });
                        return;
                    }

                    this.searching = true;
                    this.transactionFound = false;
                    this.searchNoResult = false;
                    this.otpGenerated = false;
                    this.selectedAdminId = '';

                    try {
                        let response = await fetch('/void-otp/search', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ invoice_id: this.searchInvoice.trim() })
                        });

                        let data = await response.json();
                        if (data.success) {
                            this.transaction = data.history;
                            this.admins = data.admins;
                            this.transactionFound = true;
                        } else {
                            this.searchNoResult = true;
                            this.searchErrorMessage = data.message || 'Transaksi tidak ditemukan.';
                        }
                    } catch (e) {
                        this.searchNoResult = true;
                        this.searchErrorMessage = 'Terjadi kesalahan sistem.';
                    } finally {
                        this.searching = false;
                    }
                },

                async generateOtp() {
                    if (!this.selectedAdminId) {
                        Swal.fire({ icon: 'warning', title: 'Pilih Admin', text: 'Pilih admin yang akan mengotorisasi void.' });
                        return;
                    }

                    this.generatingOtp = true;
                    try {
                        let response = await fetch('/void-otp/generate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                history_id: this.transaction.id,
                                admin_id: this.selectedAdminId
                            })
                        });

                        let data = await response.json();
                        if (data.success) {
                            this.otpCode = data.otp;
                            this.otpAdminName = data.admin_name;
                            this.otpExpiresAt = data.expires_at;
                            this.otpGenerated = true;

                            Swal.fire({
                                icon: 'success',
                                title: 'OTP Berhasil Digenerate',
                                html: `
                                    <div class="text-left">
                                        <p class="mb-2">Transaksi: <strong>${data.invoice_id}</strong></p>
                                        <p class="mb-2">Admin: <strong>${data.admin_name}</strong></p>
                                        <p class="text-4xl font-mono font-bold text-primary tracking-widest my-4 text-center">${data.otp}</p>
                                        <p class="text-xs text-slate-500">Berlaku sampai: <strong>${data.expires_at}</strong></p>
                                        <p class="text-xs text-amber-600 mt-2">Berikan kode OTP ini ke kasir/admin untuk melakukan void.</p>
                                    </div>
                                `,
                                confirmButtonColor: '#003f87',
                                confirmButtonText: 'Mengerti'
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                        }
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.' });
                    } finally {
                        this.generatingOtp = false;
                    }
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
                }
            }
        }
    </script>
</x-layout>
