<?php

namespace Tests\Feature;

use App\Livewire\LoginComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Captcha Turnstile pada login CMS.
 *
 * Titik beratnya pada verifikasi sisi server: token yang datang dari peramban
 * tidak boleh dipercaya tanpa ditukarkan ke Cloudflare.
 */
class LoginTurnstileTest extends TestCase
{
    use RefreshDatabase;

    private const SITE_KEY = 'site-key-uji';
    private const SECRET_KEY = 'secret-key-uji';

    private function aktifkanTurnstile(): void
    {
        config([
            'services.turnstile.site_key' => self::SITE_KEY,
            'services.turnstile.secret_key' => self::SECRET_KEY,
        ]);
    }

    private function matikanTurnstile(): void
    {
        config([
            'services.turnstile.site_key' => null,
            'services.turnstile.secret_key' => null,
        ]);
    }

    private function buatPengguna(string $password = 'rahasia123'): void
    {
        DB::table('users')->insert([
            'name' => 'Admin Uji',
            'email' => 'admin@uji.test',
            'password' => Hash::make($password),
            'role_id' => 1,
        ]);
    }

    private function jawabanCloudflare(bool $sukses): void
    {
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => $sukses], 200),
        ]);
    }

    // ── Tampilan ──────────────────────────────────────────────────────────

    public function test_widget_muncul_saat_kunci_terpasang(): void
    {
        $this->aktifkanTurnstile();

        $this->get('/cms/login')
            ->assertOk()
            ->assertSee('cf-turnstile', false)
            ->assertSee('data-sitekey="'.self::SITE_KEY.'"', false)
            ->assertSee('challenges.cloudflare.com/turnstile/v0/api.js', false);
    }

    /** Tanpa kunci, captcha tidak dipasang — supaya lingkungan lokal tetap bisa masuk. */
    public function test_widget_tidak_muncul_saat_kunci_kosong(): void
    {
        $this->matikanTurnstile();

        $this->get('/cms/login')
            ->assertOk()
            ->assertDontSee('cf-turnstile', false)
            ->assertDontSee('challenges.cloudflare.com', false);
    }

    /** Kunci rahasia tidak boleh pernah sampai ke peramban. */
    public function test_kunci_rahasia_tidak_ikut_terkirim_ke_halaman(): void
    {
        $this->aktifkanTurnstile();

        $this->get('/cms/login')->assertDontSee(self::SECRET_KEY, false);
    }

    // ── Verifikasi ────────────────────────────────────────────────────────

    public function test_login_ditolak_tanpa_token(): void
    {
        $this->aktifkanTurnstile();
        $this->buatPengguna();
        Http::fake();

        Livewire::test(LoginComponent::class)
            ->set('email', 'admin@uji.test')
            ->set('password', 'rahasia123')
            ->set('turnstileToken', '')
            ->call('login')
            ->assertNoRedirect();

        $this->assertNull(session('id'), 'Sesi tidak boleh terbentuk tanpa captcha.');
        Http::assertNothingSent();
    }

    public function test_login_ditolak_saat_cloudflare_menolak_token(): void
    {
        $this->aktifkanTurnstile();
        $this->buatPengguna();
        $this->jawabanCloudflare(false);

        Livewire::test(LoginComponent::class)
            ->set('email', 'admin@uji.test')
            ->set('password', 'rahasia123')
            ->set('turnstileToken', 'token-palsu')
            ->call('login')
            ->assertNoRedirect();

        $this->assertNull(session('id'), 'Token yang ditolak Cloudflare tidak boleh meloloskan login.');
    }

    public function test_login_berhasil_saat_token_sah(): void
    {
        $this->aktifkanTurnstile();
        $this->buatPengguna();
        $this->jawabanCloudflare(true);

        Livewire::test(LoginComponent::class)
            ->set('email', 'admin@uji.test')
            ->set('password', 'rahasia123')
            ->set('turnstileToken', 'token-sah')
            ->call('login')
            ->assertRedirect('/cms/dashboard');

        $this->assertSame(1, session('id'));
    }

    /** Kata sandi salah tetap ditolak walau captchanya lolos. */
    public function test_captcha_lolos_tidak_menggantikan_kata_sandi(): void
    {
        $this->aktifkanTurnstile();
        $this->buatPengguna();
        $this->jawabanCloudflare(true);

        Livewire::test(LoginComponent::class)
            ->set('email', 'admin@uji.test')
            ->set('password', 'salah')
            ->set('turnstileToken', 'token-sah')
            ->call('login')
            ->assertNoRedirect();

        $this->assertNull(session('id'));
    }

    public function test_token_dan_kunci_rahasia_dikirim_ke_cloudflare(): void
    {
        $this->aktifkanTurnstile();
        $this->buatPengguna();
        $this->jawabanCloudflare(true);

        Livewire::test(LoginComponent::class)
            ->set('email', 'admin@uji.test')
            ->set('password', 'rahasia123')
            ->set('turnstileToken', 'token-sah')
            ->call('login');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'siteverify')
                && $request['secret'] === self::SECRET_KEY
                && $request['response'] === 'token-sah';
        });
    }

    /** Token sekali pakai: percobaan gagal harus meminta widget menerbitkan yang baru. */
    public function test_percobaan_gagal_meminta_token_baru(): void
    {
        $this->aktifkanTurnstile();
        $this->buatPengguna();
        $this->jawabanCloudflare(false);

        Livewire::test(LoginComponent::class)
            ->set('email', 'admin@uji.test')
            ->set('password', 'rahasia123')
            ->set('turnstileToken', 'token-basi')
            ->call('login')
            ->assertDispatched('turnstile-reset')
            ->assertSet('turnstileToken', '');
    }

    /**
     * Cloudflare tak terjangkau sengaja diloloskan — mengunci login saat layanan
     * pihak ketiga tumbang lebih merugikan daripada celah sesaat, dan pemeriksaan
     * kata sandi tetap berjalan.
     */
    public function test_cloudflare_tak_terjangkau_tidak_mengunci_login(): void
    {
        $this->aktifkanTurnstile();
        $this->buatPengguna();
        Http::fake(fn () => throw new ConnectionException('timeout'));

        Livewire::test(LoginComponent::class)
            ->set('email', 'admin@uji.test')
            ->set('password', 'rahasia123')
            ->set('turnstileToken', 'token-apa-saja')
            ->call('login')
            ->assertRedirect('/cms/dashboard');
    }

    // ── Saat captcha dimatikan ────────────────────────────────────────────

    public function test_tanpa_kunci_login_tetap_berjalan_tanpa_token(): void
    {
        $this->matikanTurnstile();
        $this->buatPengguna();
        Http::fake();

        Livewire::test(LoginComponent::class)
            ->set('email', 'admin@uji.test')
            ->set('password', 'rahasia123')
            ->call('login')
            ->assertRedirect('/cms/dashboard');

        Http::assertNothingSent();
    }
}
