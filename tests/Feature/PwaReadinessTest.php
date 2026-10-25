<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_authenticated_pages_include_pwa_install_support(): void
    {
        $guestPage = $this->get('/login');

        $guestPage->assertOk()
            ->assertSee('manifest.webmanifest')
            ->assertSee('id="pwaInstallButton"', false)
            ->assertSee('serviceWorker.register', false);

        $user = User::factory()->create();

        $authenticatedPage = $this->actingAs($user)->get('/profile');

        $authenticatedPage->assertOk()
            ->assertSee('manifest.webmanifest')
            ->assertSee('id="pwaInstallButton"', false)
            ->assertSee('serviceWorker.register', false);
    }

    public function test_all_shared_layouts_include_the_manifest_and_pwa_controls(): void
    {
        foreach (['admin', 'guest', 'auth-split', 'app'] as $layout) {
            $contents = file_get_contents(resource_path("views/layouts/{$layout}.blade.php"));

            $this->assertIsString($contents);
            $this->assertStringContainsString('manifest.webmanifest', $contents, "The {$layout} layout needs the manifest.");
            $this->assertStringContainsString("@include('partials.pwa')", $contents, "The {$layout} layout needs the PWA controls.");
        }
    }

    public function test_manifest_has_valid_install_icons(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);
        $iconsBySize = collect($manifest['icons'])
            ->filter(fn (array $icon) => str_ends_with($icon['src'], '.png'))
            ->keyBy('sizes');

        foreach (['192x192', '512x512'] as $size) {
            $this->assertArrayHasKey($size, $iconsBySize);

            $iconPath = public_path(ltrim($iconsBySize[$size]['src'], './'));
            $imageSize = getimagesize($iconPath);

            $this->assertIsArray($imageSize);
            $this->assertSame($size, $imageSize[0].'x'.$imageSize[1]);
        }

        $this->assertSame('standalone', $manifest['display']);
    }
}
