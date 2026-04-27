<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use Illuminate\View\View;

class SitePageController extends Controller
{
    public function __invoke(string $slug): View
    {
        $page = SitePage::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('site.page', [
            'page' => $page,
            'publishedPages' => SitePage::query()
                ->where('is_published', true)
                ->where('slug', '!=', 'landing')
                ->orderBy('title')
                ->get(),
        ]);
    }
}
