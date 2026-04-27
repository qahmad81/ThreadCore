<?php

namespace Tests\Feature;

use App\Models\SitePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_landing_page(): void
    {
        $this->seed();
        $admin = User::query()->where('is_admin', true)->firstOrFail();
        $page = SitePage::query()->where('slug', 'landing')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.pages.update', $page), [
            'slug' => 'landing',
            'title' => 'Updated Landing',
            'headline' => 'Updated landing headline',
            'summary' => 'Updated public copy',
            'blocks_text' => "Core | Updated block | Visible from CMS",
            'is_published' => '1',
        ])->assertRedirect(route('admin.pages.index'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Updated landing headline')
            ->assertSee('Visible from CMS');
    }

    public function test_published_cms_page_is_available_on_public_slug(): void
    {
        SitePage::query()->create([
            'slug' => 'about',
            'title' => 'About',
            'headline' => 'About ThreadCore',
            'summary' => 'A public CMS page.',
            'blocks' => [
                ['label' => 'CMS', 'title' => 'Visible page', 'body' => 'Rendered from site_pages.'],
            ],
            'is_published' => true,
        ]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('About ThreadCore')
            ->assertSee('Rendered from site_pages.');
    }

    public function test_unpublished_cms_page_returns_not_found(): void
    {
        SitePage::query()->create([
            'slug' => 'draft-page',
            'title' => 'Draft',
            'headline' => 'Draft page',
            'is_published' => false,
        ]);

        $this->get('/draft-page')->assertNotFound();
    }

    public function test_reserved_cms_slug_cannot_be_saved(): void
    {
        $this->seed();
        $admin = User::query()->where('is_admin', true)->firstOrFail();

        $this->actingAs($admin)->post(route('admin.pages.store'), [
            'slug' => 'customer',
            'title' => 'Customer',
            'headline' => 'Reserved page',
            'summary' => 'Should fail',
            'is_published' => '1',
        ])->assertSessionHasErrors('slug');
    }

    public function test_catch_all_does_not_intercept_core_routes(): void
    {
        $this->seed();

        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/customer')->assertRedirect(route('login'));
        $this->get('/'.config('threadcore.admin.path'))->assertRedirect(route('login'));
    }
}
