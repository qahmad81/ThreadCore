<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DocsController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(auth()->user()->customerAccount, 403);

        return view('customer.docs');
    }
}
