<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Offline - POS System</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#003f87",
                        "primary-container": "#0056b3",
                        surface: "#f8f9fa",
                        "on-surface": "#191c1d",
                        "on-surface-variant": "#424752",
                    },
                    fontFamily: {
                        headline: ["Manrope"],
                        body: ["Inter"],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-surface min-h-screen flex items-center justify-center p-6">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 rounded-3xl bg-primary/10 flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-primary text-5xl" style="font-variation-settings: 'FILL' 1;">wifi_off</span>
        </div>
        <h1 class="font-headline font-extrabold text-3xl text-on-surface mb-3">Kamu Offline</h1>
        <p class="text-on-surface-variant mb-8 leading-relaxed">
            Tidak ada koneksi internet. Beberapa fitur mungkin tidak tersedia.
            <br />
            <span class="text-sm">Data transaksi akan disinkronisasi saat koneksi pulih.</span>
        </p>
        <button onclick="window.location.reload()" 
            class="px-8 py-4 bg-gradient-to-r from-primary to-primary-container text-white rounded-2xl font-bold text-lg shadow-lg hover:shadow-xl transition-all active:scale-95 flex items-center gap-3 mx-auto">
            <span class="material-symbols-outlined">refresh</span>
            Coba Lagi
        </button>
        <div class="mt-8 flex items-center justify-center gap-2 text-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-base">info</span>
            <span>POS System v2.4.0</span>
        </div>
    </div>

    <script>
        // Auto-reload when connection is restored
        window.addEventListener('online', () => window.location.reload());
    </script>
</body>
</html>
