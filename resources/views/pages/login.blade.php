<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Masuk | POS System</title>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#003f87",
                        "primary-container": "#0056b3",
                        "primary-fixed": "#d7e2ff",
                        "primary-fixed-dim": "#acc7ff",
                        "on-primary": "#ffffff",
                        "on-primary-fixed": "#001a40",
                        "on-primary-container": "#bbd0ff",
                        "secondary": "#4c5e84",
                        "surface": "#f8f9fa",
                        "surface-container": "#edeeef",
                        "surface-container-low": "#f3f4f5",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-high": "#e7e8e9",
                        "on-surface": "#191c1d",
                        "on-surface-variant": "#424752",
                        "outline": "#727784",
                        "outline-variant": "#c2c6d4",
                        "error": "#ba1a1a",
                        "error-container": "#ffdad6",
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "0.75rem",
                        "xl": "1rem",
                        "2xl": "1.5rem",
                        "3xl": "2rem",
                        "full": "9999px"
                    },
                    fontFamily: {
                        "headline": ["Manrope"],
                        "body": ["Inter"],
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        @keyframes pulse-slow {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.7; }
        }

        @keyframes slide-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }
        .animate-slide-up { animation: slide-up 0.6s ease-out; }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .input-focus-ring:focus-within {
            box-shadow: 0 0 0 3px rgba(0, 63, 135, 0.15);
            border-color: #003f87;
        }
    </style>
</head>

<body class="bg-surface font-body text-on-surface min-h-screen flex overflow-hidden">

    <!-- Left Panel - Branding -->
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative overflow-hidden bg-gradient-to-br from-primary via-primary-container to-primary">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-20 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-pulse-slow"></div>
            <div class="absolute bottom-32 right-32 w-96 h-96 bg-primary-fixed/20 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 2s;"></div>
            <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-white/5 rounded-full blur-2xl animate-float"></div>
        </div>

        <!-- Grid Pattern Overlay -->
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, rgba(255,255,255,0.3) 1px, transparent 1px); background-size: 40px 40px;"></div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col justify-between p-12 xl:p-16 w-full">
            <!-- Logo -->
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                    <span class="material-symbols-outlined text-white text-3xl" style="font-variation-settings: 'FILL' 1;">point_of_sale</span>
                </div>
                <div>
                    <h1 class="font-headline font-extrabold text-2xl text-white tracking-tight">JOY POS</h1>
                    <p class="text-white/70 text-sm font-medium">Point Of Sales System</p>
                </div>
            </div>

            <!-- Center Message -->
            <div class="space-y-6 max-w-lg">
                <h2 class="font-headline font-extrabold text-4xl xl:text-5xl text-white leading-tight">
                    Kelola Transaksi<br />
                    <span class="text-primary-fixed">Dengan Mudah</span>
                </h2>
                <p class="text-white/80 text-lg leading-relaxed">
                    Sistem point of sale modern untuk mengelola penjualan, inventaris, dan laporan bisnis Anda secara real-time.
                </p>

                <!-- Feature Pills -->
                <div class="flex flex-wrap gap-3 pt-4">
                    <div class="flex items-center gap-2 px-4 py-2 bg-white/15 backdrop-blur-sm rounded-full text-white text-sm font-semibold">
                        <span class="material-symbols-outlined text-lg">shopping_cart</span>
                        POS Terminal
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 bg-white/15 backdrop-blur-sm rounded-full text-white text-sm font-semibold">
                        <span class="material-symbols-outlined text-lg">inventory_2</span>
                        Stok Real-time
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 bg-white/15 backdrop-blur-sm rounded-full text-white text-sm font-semibold">
                        <span class="material-symbols-outlined text-lg">monitoring</span>
                        Laporan
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between text-white/60 text-sm">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">verified_user</span>
                    <span class="font-medium">Secure & Encrypted</span>
                </div>
                <span>v2.4.0</span>
            </div>
        </div>
    </div>

    <!-- Right Panel - Login Form -->
    <div class="w-full lg:w-1/2 xl:w-2/5 flex items-center justify-center p-6 lg:p-12 relative">
        <!-- Background Decoration -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-fixed/30 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-primary/10 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>

        <!-- Presensi Button -->
        <a href="/presensi-page" 
            class="absolute top-6 right-6 flex items-center gap-2 px-4 py-2.5 bg-primary/10 text-primary rounded-xl hover:bg-primary/20 transition-all text-sm font-bold group">
            <span class="material-symbols-outlined text-lg group-hover:rotate-12 transition-transform">qr_code</span>
            Presensi
        </a>

        <!-- Login Card -->
        <div class="relative z-10 w-full max-w-md animate-slide-up">
            <!-- Mobile Logo -->
            <div class="lg:hidden flex items-center gap-3 mb-8">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-primary-container flex items-center justify-center shadow-lg">
                    <span class="material-symbols-outlined text-white text-2xl" style="font-variation-settings: 'FILL' 1;">point_of_sale</span>
                </div>
                <div>
                    <h1 class="font-headline font-extrabold text-xl text-primary">POS System</h1>
                    <p class="text-on-surface-variant text-xs">Arka POS</p>
                </div>
            </div>

            <!-- Header -->
            <div class="mb-8">
                <h2 class="font-headline font-extrabold text-3xl text-on-surface mb-2">Selamat Datang</h2>
                <p class="text-on-surface-variant text-base">Masukkan NIK dan password Anda untuk masuk</p>
            </div>

            <!-- Form -->
            <form action="/login" method="POST" class="space-y-5">
                @csrf

                @if($errors->any())
                <div class="p-4 bg-error/10 border border-error/20 text-error rounded-xl flex items-center gap-3">
                    <span class="material-symbols-outlined flex-shrink-0">error</span>
                    <span class="text-sm font-medium">{{ $errors->first() }}</span>
                </div>
                @endif

                <!-- NIK Input -->
                <div>
                    <label class="block text-sm font-bold text-on-surface mb-2" for="nik">
                        NIK (Nomor Induk Karyawan)
                    </label>
                    <div class="relative input-focus-ring rounded-xl transition-all">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-outline text-xl">badge</span>
                        </div>
                        <input
                            class="w-full pl-12 pr-4 py-3.5 bg-surface-container-lowest border border-outline-variant/50 rounded-xl focus:outline-none transition-all placeholder:text-outline/60 text-on-surface font-medium"
                            id="nik" 
                            name="nik" 
                            placeholder="Contoh: 26050001" 
                            required 
                            type="text" 
                            autocomplete="username" />
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-bold text-on-surface" for="password">
                            Password
                        </label>
                    </div>
                    <div class="relative input-focus-ring rounded-xl transition-all">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-outline text-xl">lock</span>
                        </div>
                        <input
                            class="w-full pl-12 pr-12 py-3.5 bg-surface-container-lowest border border-outline-variant/50 rounded-xl focus:outline-none transition-all placeholder:text-outline/60 text-on-surface font-medium"
                            id="password" 
                            name="password" 
                            placeholder="Masukkan password" 
                            required 
                            type="password" 
                            autocomplete="current-password" />
                        <button
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-outline hover:text-on-surface-variant transition-colors"
                            type="button"
                            onclick="togglePassword()">
                            <span class="material-symbols-outlined text-xl" id="toggle-icon">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input
                        class="h-5 w-5 text-primary border-outline-variant rounded focus:ring-primary/20 bg-surface-container-low cursor-pointer"
                        id="remember" 
                        name="remember" 
                        type="checkbox" />
                    <label class="ml-3 text-sm text-on-surface-variant cursor-pointer select-none font-medium"
                        for="remember">
                        Ingat perangkat ini
                    </label>
                </div>

                <!-- Submit Button -->
                <button
                    class="w-full py-4 px-6 bg-gradient-to-r from-primary to-primary-container text-on-primary font-headline font-bold rounded-xl shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2 text-lg"
                    type="submit">
                    <span class="material-symbols-outlined text-xl">login</span>
                    Masuk
                </button>
            </form>

            <!-- Footer Info -->
            <div class="mt-8 pt-6 border-t border-outline-variant/30">
                <p class="text-xs text-on-surface-variant text-center leading-relaxed">
                    Platform hanya untuk karyawan.<br />
                    Semua aktivitas login akan dicatat dalam sistem.
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggle-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = 'visibility';
            }
        }
    </script>
</body>

</html>
