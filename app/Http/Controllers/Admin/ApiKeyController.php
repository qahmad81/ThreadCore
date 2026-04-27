<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(): View
    {
        return view('admin.api-keys.index', [
            'keys' => ApiKey::query()->with('customerAccount')->latest()->limit(200)->get(),
        ]);
    }
}
