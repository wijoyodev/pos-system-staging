<!DOCTYPE html>

<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>{{ $title }}</title>
    <meta name="theme-color" content="#003f87" />
    <meta name="description" content="Arka POS - Point of Sale Application" />

    <!-- PWA Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-title" content="Arka POS" />
    <meta name="mobile-web-app-capable" content="yes" />
    <link rel="manifest" href="/manifest.json" />
    <link rel="icon" type="image/png" sizes="192x192" href="/pwa/icon-192.png" />
    <link rel="apple-touch-icon" href="/pwa/icon-192.png" />

    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Manrope:wght@500;700;800&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- HTML5 QR Code Scanner -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface": "#f8f9fa",
                        "on-error-container": "#93000a",
                        "secondary-fixed": "#d7e2ff",
                        "surface-dim": "#d9dadb",
                        "tertiary-fixed": "#ffdbcc",
                        "primary-fixed-dim": "#acc7ff",
                        "surface-container-highest": "#e1e3e4",
                        "secondary": "#4c5e84",
                        "primary": "#003f87",
                        "surface-container": "#edeeef",
                        "surface-bright": "#f8f9fa",
                        "surface-variant": "#e1e3e4",
                        "inverse-surface": "#2e3132",
                        "surface-container-lowest": "#ffffff",
                        "secondary-container": "#bfd2fd",
                        "tertiary-container": "#983c00",
                        "surface-container-low": "#f3f4f5",
                        "on-primary-fixed": "#001a40",
                        "on-secondary-fixed-variant": "#34476a",
                        "on-primary": "#ffffff",
                        "background": "#f8f9fa",
                        "on-secondary-fixed": "#041b3c",
                        "surface-tint": "#115cb9",
                        "on-secondary-container": "#475a7f",
                        "error-container": "#ffdad6",
                        "on-surface-variant": "#424752",
                        "tertiary-fixed-dim": "#ffb694",
                        "on-primary-fixed-variant": "#004491",
                        "secondary-fixed-dim": "#b3c7f1",
                        "on-primary-container": "#bbd0ff",
                        "primary-container": "#0056b3",
                        "error": "#ba1a1a",
                        "on-background": "#191c1d",
                        "outline": "#727784",
                        "tertiary": "#722b00",
                        "outline-variant": "#c2c6d4",
                        "on-secondary": "#ffffff",
                        "inverse-on-surface": "#f0f1f2",
                        "on-surface": "#191c1d",
                        "surface-container-high": "#e7e8e9",
                        "primary-fixed": "#d7e2ff",
                        "on-tertiary-fixed-variant": "#7b2f00",
                        "on-tertiary-container": "#ffc2a7",
                        "on-error": "#ffffff",
                        "inverse-primary": "#acc7ff",
                        "on-tertiary": "#ffffff",
                        "on-tertiary-fixed": "#351000"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.5rem",
                        "lg": "0.5rem",
                        "xl": "1.5rem",
                        "full": "9999px"
                    },
                    "fontFamily": {
                        "headline": ["Manrope"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Sidebar expand/collapse styles */
        #sidebar {
            width: 16rem;
            transition: width 0.3s ease;
        }

        #sidebar.sidebar-collapsed {
            width: 4rem;
        }

        @media (max-width: 1023px) {
            #sidebar {
                width: 16rem !important;
            }
        }

        @media (min-width: 1024px) {

            #sidebar.sidebar-collapsed .sidebar-text,
            #sidebar.sidebar-collapsed .sidebar-link-text,
            #sidebar.sidebar-collapsed .sidebar-user-info,
            #sidebar.sidebar-collapsed .sidebar-section-header,
            #sidebar.sidebar-collapsed .logout-form,
            #sidebar.sidebar-collapsed .sidebar-user-card {
                display: none !important;
            }

            #sidebar.sidebar-collapsed .sidebar-collapsed-logout {
                display: block !important;
            }

            #sidebar.sidebar-collapsed .p-6 {
                padding: 1.25rem 0.75rem !important;
                display: flex;
                justify-content: center;
            }

            #sidebar.sidebar-collapsed .px-4 {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            #sidebar.sidebar-collapsed .sidebar-link {
                justify-content: center !important;
                padding: 0.85rem 0 !important;
                margin: 0.25rem 0.5rem !important;
            }

            #sidebar.sidebar-collapsed .sidebar-link span.material-symbols-outlined {
                font-size: 1.4rem !important;
                margin: 0 !important;
            }

            /* Push main content when sidebar is fixed */
            main {
                margin-left: 0;
                transition: margin-left 0.3s ease;
            }

            @media (min-width: 1024px) {
                main {
                    margin-left: 16rem;
                }

                body.sidebar-collapsed main {
                    margin-left: 4rem;
                }
            }

            #sidebar.sidebar-collapsed #toggle-icon {
                transform: rotate(180deg);
            }

            #sidebar:not(.sidebar-collapsed) #toggle-icon {
                transform: rotate(0deg);
            }

            /* Hover effects for collapsed menu */
            #sidebar.sidebar-collapsed .sidebar-link:hover {
                background: rgba(0, 63, 135, 0.1);
                transform: scale(1.05);
            }
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    class="bg-surface text-on-surface font-body antialiased selection:bg-primary-fixed selection:text-on-primary-fixed">
    <div class="flex h-screen">
        <x-side-bar></x-side-bar>
        {{ $slot }}
    </div>

    <!-- PWA Install Button (Floating) -->
    <button id="pwa-install-btn" onclick="installPWA()"
        class="fixed bottom-6 right-6 z-50 hidden items-center gap-2 px-5 py-3 bg-primary text-white rounded-2xl shadow-xl hover:shadow-2xl transition-all active:scale-95 font-semibold"
        style="display: none;">
        <span class="material-symbols-outlined">download</span>
        <span>Install App</span>
    </button>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebar-toggle');

            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('sidebar-collapsed');
                document.body.classList.add('sidebar-collapsed');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('sidebar-collapsed');
                    document.body.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('sidebar-collapsed'));
                });
            }

            // Global SweetAlert for session flash messages
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('success')),
                    confirmButtonColor: '#3085d6',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: @json(session('error')),
                    confirmButtonColor: '#3085d6',
                    timer: 4000,
                    timerProgressBar: true
                });
            @endif

            @if(session('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: @json(session('warning')),
                    confirmButtonColor: '#3085d6',
                    timer: 4000,
                    timerProgressBar: true
                });
            @endif

            @if(session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: @json(session('info')),
                    confirmButtonColor: '#3085d6',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Clerek Modal (Global) -->
    @if(auth()->user() && !auth()->user()->isSuperAdmin())
        <div id="clerekModal" x-data="clerekAction()" x-show="showClerekModal" x-cloak
            @open-clerek-modal.window="openModal()" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showClerekModal = false"></div>
            <div
                class="relative w-full max-w-sm bg-surface-container-lowest rounded-[2.5rem] shadow-2xl overflow-hidden border border-white/20 flex flex-col">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-white/50 backdrop-blur">
                    <div>
                        <h3 class="text-xl font-black text-primary font-manrope tracking-tight">Tutup Shift</h3>
                        <p class="text-[10px] text-slate-500 mt-1 uppercase font-bold tracking-widest">Akhiri sesi transaksi
                            Anda</p>
                    </div>
                    <button @click="showClerekModal = false"
                        class="w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center transition-all active:scale-90">
                        <span class="material-symbols-outlined text-slate-400">close</span>
                    </button>
                </div>
                <div class="p-8 space-y-6 text-center">
                    <div class="w-20 h-20 bg-primary/5 rounded-full flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-primary text-4xl font-black">point_of_sale</span>
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-on-surface">Konfirmasi Tutup Shift?</h4>
                        <p class="text-xs text-slate-400 mt-2 font-medium">Data transaksi Anda akan dikunci dan diserahkan
                            ke Admin untuk verifikasi uang tunai.</p>
                    </div>
                    <button @click="submitClerek()" :disabled="submitting"
                        class="w-full py-5 bg-gradient-to-br from-primary to-primary-container text-white rounded-2xl font-black text-sm shadow-xl shadow-primary/20 hover:scale-[0.98] transition-transform active:opacity-90 flex items-center justify-center gap-3 disabled:opacity-50">
                        <span x-show="!submitting">Ya, Tutup Shift</span>
                        <span x-show="submitting" class="material-symbols-outlined animate-spin">autorenew</span>
                        <span x-show="!submitting" class="material-symbols-outlined text-sm">check_circle</span>
                    </button>
                    <button @click="showClerekModal = false"
                        class="text-xs font-black text-slate-400 uppercase tracking-widest hover:text-primary transition-colors">
                        Batalkan
                    </button>
                </div>
            </div>
        </div>

        <script>
            function clerekAction() {
                return {
                    showClerekModal: false,
                    loading: false, submitting: false,
                    summary: { totalSales: 0, cashSales: 0, qrisSales: 0, debitSales: 0, creditSales: 0 },
                    shift: 'pagi', notes: '',
                    async openModal() {
                        this.showClerekModal = true;
                        this.loading = true;
                        try {
                            let res = await fetch('/clerek/summary');
                            this.summary = await res.json();
                            if (this.summary.isClosed) {
                                Swal.fire({ icon: 'info', title: 'Shift Sudah Ditutup', text: 'Terminal ini sudah menyelesaikan clerek hari ini.', confirmButtonColor: '#003f87' });
                                this.showClerekModal = false; return;
                            }
                            this.loading = false;
                        } catch (e) { this.loading = false; Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal mengambil data ringkasan' }); }
                    },
                    formatCurrency(amount) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount); },
                    async submitClerek() {
                        this.submitting = true;
                        try {
                            let res = await fetch('/clerek/submit', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ shift: this.shift, notes: this.notes })
                            });
                            let data = await res.json();
                            if (data.success) {
                                Swal.fire({ icon: 'success', title: 'Shift Ditutup', text: 'Berhasil menutup shift terminal.', confirmButtonColor: '#003f87' }).then(() => { window.location.reload(); });
                            } else { Swal.fire({ icon: 'error', title: 'Gagal', text: data.message }); }
                        } catch (e) { Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem' }); }
                        finally { this.submitting = false; }
                    }
                };
            }
        </script>
    @endif

    <!-- PWA: Service Worker & Install -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .catch((err) => console.error('[PWA] SW registration failed', err));
            });
        }

        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const btn = document.getElementById('pwa-install-btn');
            if (btn) btn.style.display = 'flex';
        });

        function installPWA() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(() => {
                    deferredPrompt = null;
                    const btn = document.getElementById('pwa-install-btn');
                    if (btn) btn.style.display = 'none';
                });
            }
        }
    </script>

    @stack('scripts')
</body>

</html>