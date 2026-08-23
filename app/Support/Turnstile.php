<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifikasi token Cloudflare Turnstile di sisi server.
 *
 * Token yang dikirim peramban tidak bisa dipercaya begitu saja — ia harus
 * ditukarkan ke Cloudflare. Tanpa langkah ini, siapa pun bisa mengirim token
 * palsu dan captcha-nya tidak ada gunanya.
 */
class Turnstile
{
    private const ENDPOINT = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Captcha hanya aktif bila kedua kunci terpasang. Tanpa ini, setiap
     * lingkungan pengembangan dan seluruh rangkaian tes akan terkunci.
     */
    public static function enabled(): bool
    {
        return filled(config('services.turnstile.site_key'))
            && filled(config('services.turnstile.secret_key'));
    }

    public static function siteKey(): ?string
    {
        return config('services.turnstile.site_key');
    }

    /**
     * @param  string|null  $token  Nilai cf-turnstile-response dari peramban.
     * @param  string|null  $ip     IP pengirim, dipakai Cloudflare sebagai sinyal tambahan.
     */
    public static function verify(?string $token, ?string $ip = null): bool
    {
        if (! self::enabled()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post(self::ENDPOINT, array_filter([
                    'secret' => config('services.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $ip,
                ]));
        } catch (\Throwable $e) {
            // Cloudflare tidak terjangkau. Sengaja diloloskan: mengunci login
            // saat layanan pihak ketiga tumbang jauh lebih merugikan daripada
            // membuka celah sesaat, sementara pemeriksaan kata sandi tetap jalan.
            Log::warning('Turnstile tidak terjangkau, verifikasi dilewati.', [
                'error' => $e->getMessage(),
            ]);

            return true;
        }

        // Cloudflare menjawab, tapi menolak token -> tolak.
        return $response->successful() && $response->json('success') === true;
    }
}
