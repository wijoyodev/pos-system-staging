<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! app()->runningInConsole() && Schema::hasTable('settings')) {
            try {
                $dbSettings = Cache::rememberForever('global_settings', function () {
                    return Setting::pluck('value', 'key')->toArray();
                });

                if ($key = $dbSettings['aws_key'] ?? null) {
                    Config::set('filesystems.disks.s3.key', $key);
                }
                if ($secret = $dbSettings['aws_secret'] ?? null) {
                    Config::set('filesystems.disks.s3.secret', $secret);
                }
                if ($region = $dbSettings['aws_region'] ?? null) {
                    Config::set('filesystems.disks.s3.region', $region);
                }
                if ($bucket = $dbSettings['aws_bucket'] ?? null) {
                    Config::set('filesystems.disks.s3.bucket', $bucket);
                }

                if ($host = $dbSettings['mail_host'] ?? null) {
                    Config::set('mail.mailers.smtp.host', $host);
                }
                if ($port = $dbSettings['mail_port'] ?? null) {
                    Config::set('mail.mailers.smtp.port', $port);
                }
                if ($username = $dbSettings['mail_username'] ?? null) {
                    Config::set('mail.mailers.smtp.username', $username);
                }
                if ($password = $dbSettings['mail_password'] ?? null) {
                    Config::set('mail.mailers.smtp.password', $password);
                }
                if ($encryption = $dbSettings['mail_encryption'] ?? null) {
                    Config::set('mail.mailers.smtp.encryption', $encryption);
                }
                if ($fromAddress = $dbSettings['mail_from_address'] ?? null) {
                    Config::set('mail.from.address', $fromAddress);
                }
                if ($fromName = $dbSettings['mail_from_name'] ?? null) {
                    Config::set('mail.from.name', $fromName);
                }

                if ($slack = $dbSettings['slack_webhook_url'] ?? null) {
                    Config::set('logging.channels.slack.url', $slack);
                }

                if ($postmark = $dbSettings['postmark_api_key'] ?? null) {
                    Config::set('services.postmark.token', $postmark);
                }
                if ($resend = $dbSettings['resend_api_key'] ?? null) {
                    Config::set('services.resend.key', $resend);
                }
            } catch (\Exception $e) {
                // skip gracefully
            }
        }
    }
}
