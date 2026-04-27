<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GatewayRequestLog;
use Illuminate\View\View;

class UsageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.usage.index', [
            'logs' => GatewayRequestLog::query()->with(['thread'])->latest()->limit(100)->get(),
            'requests' => GatewayRequestLog::query()->count(),
            'tokens' => GatewayRequestLog::query()->sum('input_tokens') + GatewayRequestLog::query()->sum('output_tokens'),
        ]);
    }
}
