<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function __invoke(): View
    {
        $page = SitePage::query()
            ->where('slug', 'landing')
            ->where('is_published', true)
            ->first();

        return view('site.landing', [
            'page' => $page,
        ]);
    }
}
