<?php

namespace Tests\Feature;

use App\Handlers\LfmConfigHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LfmSecurityTest extends TestCase
{
    use RefreshDatabase;
    public function test_lfm_package_routes_are_disabled(): void
    {
        $this->assertFalse(config('lfm.use_package_routes'));

        // Default package routes such as /filemanager/upload or /filemanager/delete should not exist
        $response = $this->post('/filemanager/upload');
        $response->assertNotFound();

        $response = $this->get('/filemanager/jsonitems');
        $response->assertNotFound();

        // All registered unisharp routes must be under /cms/fire-filemanager
        $routes = app('router')->getRoutes();
        foreach ($routes as $route) {
            if (str_starts_with($route->getName() ?? '', 'unisharp.lfm.')) {
                $this->assertStringStartsWith('cms/fire-filemanager', $route->uri());
            }
        }
    }

    public function test_cms_fire_filemanager_routes_are_protected_by_session(): void
    {
        // Without session('id'), /cms/fire-filemanager should redirect to /cms/login
        $response = $this->get('/cms/fire-filemanager');
        $response->assertRedirect('/cms/login');
    }

    public function test_lfm_file_naming_hardening_is_enabled(): void
    {
        $this->assertTrue(config('lfm.rename_file'));
        $this->assertTrue(config('lfm.alphanumeric_filename'));
    }

    public function test_lfm_disallowed_extensions_contains_blacklist(): void
    {
        $disallowed = config('lfm.disallowed_extensions');

        $expected = [
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
            'pht', 'phps', 'html', 'htm', 'shtml', 'svg', 'htaccess',
            'sh', 'bash', 'cgi', 'pl', 'py', 'exe',
        ];

        $this->assertIsArray($disallowed);
        foreach ($expected as $ext) {
            $this->assertContains($ext, $disallowed, "Extension {$ext} must be in disallowed_extensions");
        }
    }

    public function test_private_folder_handler_resolves_safely(): void
    {
        $handlerClass = config('lfm.private_folder_name');
        $this->assertEquals(LfmConfigHandler::class, $handlerClass);

        $handler = app($handlerClass);

        // When no session/auth is present, fall back to 'shared'
        $this->assertEquals('shared', $handler->userField());

        // When session id is set, return session id
        session(['id' => 'admin-user-123']);
        $this->assertEquals('admin-user-123', $handler->userField());
    }
}
