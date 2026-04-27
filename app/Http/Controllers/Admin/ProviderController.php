<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\View\View;

class ProviderController extends Controller
{
    public function index(): View
    {
        return view('admin.providers.index', [
            'providers' => Provider::query()
                ->with('models')
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
