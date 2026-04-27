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
}
