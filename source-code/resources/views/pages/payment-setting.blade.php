<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full">
        <header class="bg-white/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm">
            <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
                <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900">{{ $title }}</h1>
            </div>
        </header>

        <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
            <div class="max-w-6xl mx-auto">
                <div class="mb-6 lg:mb-10">
                    <x-report-header title="Pengaturan Integrasi" module="Konfigurasi" submodule="API &amp; Layanan"
                        description="Kelola kunci API dan kredensial layanan eksternal — berlaku global" />
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

            <form action="/pengaturan-integrasi" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Midtrans --}}
                <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-indigo-600">payments</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold font-manrope text-blue-900">Midtrans</h3>
                            <p class="text-xs text-on-surface-variant">Payment gateway QRIS</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Server Key</label>
                            <input name="midtrans_server_key"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="text" value="{{ old('midtrans_server_key', $settings['midtrans_server_key'] ?? '') }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Client Key</label>
                            <input name="midtrans_client_key"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="text" value="{{ old('midtrans_client_key', $settings['midtrans_client_key'] ?? '') }}" />
                        </div>
                        <div class="flex items-center justify-between p-4 bg-surface rounded-lg">
                            <div>
                                <h4 class="font-bold text-sm font-manrope">Production Mode</h4>
                                <p class="text-xs text-on-surface-variant font-body">Aktifkan untuk environment production</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="midtrans_is_production" value="1" class="sr-only peer" {{ ($settings['midtrans_is_production'] ?? '0') == '1' ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600">
                                </div>
                            </label>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-xs text-amber-800">
                            <strong>Penting:</strong> Pastikan metode pembayaran <strong>QRIS</strong> sudah diaktifkan di dashboard Midtrans Anda (<em>Settings → Payment Channels</em>). Jika belum, QRIS akan gagal dengan error <em>"Payment channel is not activated"</em>.
                        </div>
                    </div>
                </section>

                {{-- AWS S3 --}}
                <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-orange-600">cloud</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold font-manrope text-blue-900">AWS S3</h3>
                            <p class="text-xs text-on-surface-variant">Cloud storage untuk backup database</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Access Key ID</label>
                            <input name="aws_key"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="text" value="{{ old('aws_key', $settings['aws_key'] ?? '') }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Secret Access Key</label>
                            <input name="aws_secret"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="password" value="{{ old('aws_secret', $settings['aws_secret'] ?? '') }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Region</label>
                            <input name="aws_region"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="text" value="{{ old('aws_region', $settings['aws_region'] ?? 'us-east-1') }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Bucket</label>
                            <input name="aws_bucket"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="text" value="{{ old('aws_bucket', $settings['aws_bucket'] ?? '') }}" />
                        </div>
                    </div>
                </section>

                {{-- Mail / SMTP --}}
                <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-blue-600">mail</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold font-manrope text-blue-900">SMTP / Email</h3>
                            <p class="text-xs text-on-surface-variant">Konfigurasi mail server untuk notifikasi</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Host</label>
                            <input name="mail_host"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="text" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Port</label>
                            <input name="mail_port"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="text" value="{{ old('mail_port', $settings['mail_port'] ?? '') }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Username</label>
                            <input name="mail_username"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="text" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Password</label>
                            <input name="mail_password"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="password" value="{{ old('mail_password', $settings['mail_password'] ?? '') }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Encryption</label>
                            <input name="mail_encryption"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="text" value="{{ old('mail_encryption', $settings['mail_encryption'] ?? '') }}" placeholder="tls / ssl" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">From Address</label>
                            <input name="mail_from_address"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="email" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">From Name</label>
                            <input name="mail_from_name"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="text" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}" />
                        </div>
                    </div>
                </section>

                {{-- Slack --}}
                <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-pink-100 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-pink-600">forum</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold font-manrope text-blue-900">Slack</h3>
                            <p class="text-xs text-on-surface-variant">Webhook URL untuk notifikasi error logging</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Webhook URL</label>
                        <input name="slack_webhook_url"
                            class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                            type="url" value="{{ old('slack_webhook_url', $settings['slack_webhook_url'] ?? '') }}" />
                    </div>
                </section>

                {{-- Postmark & Resend --}}
                <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-green-600">forward_to_inbox</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold font-manrope text-blue-900">Mail Service API</h3>
                            <p class="text-xs text-on-surface-variant">Kunci API untuk Postmark &amp; Resend</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Postmark API Key</label>
                            <input name="postmark_api_key"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="password" value="{{ old('postmark_api_key', $settings['postmark_api_key'] ?? '') }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-on-surface-variant font-label">Resend API Key</label>
                            <input name="resend_api_key"
                                class="w-full bg-surface-container rounded-lg border-none focus:ring-2 focus:ring-primary/10 py-3 px-4 font-body font-mono text-sm"
                                type="password" value="{{ old('resend_api_key', $settings['resend_api_key'] ?? '') }}" />
                        </div>
                    </div>
                </section>

                </div>

                <div class="flex justify-end items-center gap-4 mt-8 py-6 border-t border-slate-200">
                    <button type="submit"
                        class="px-10 py-3 bg-primary-container text-white font-bold font-manrope rounded-lg shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </main>
</x-layout>
