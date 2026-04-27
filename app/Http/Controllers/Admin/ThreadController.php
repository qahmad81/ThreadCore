<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Thread;
use Illuminate\View\View;

class ThreadController extends Controller
{
    public function index(): View
    {
        return view('admin.threads.index', [
            'threads' => Thread::query()->with(['customerAccount', 'familyAgent', 'provider', 'providerModel'])->latest()->limit(100)->get(),
        ]);
    }
}
