<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Thread;
use App\Services\Gateway\ThreadMarkdownExporter;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ThreadController extends Controller
{
    public function __construct(
        private readonly ThreadMarkdownExporter $exporter,
    ) {
    }

    public function index(): View
    {
        $account = auth()->user()->customerAccount;
        abort_unless($account, 403);

        return view('customer.threads.index', [
            'account' => $account,
            'threads' => Thread::query()
                ->where('customer_account_id', $account->id)
                ->with(['familyAgent', 'provider', 'providerModel'])
                ->latest()
                ->paginate(50)
                ->withQueryString(),
        ]);
    }

    public function show(string $publicId): View
    {
        $account = auth()->user()->customerAccount;
        abort_unless($account, 403);

        $thread = Thread::query()
            ->where('public_id', $publicId)
            ->where('customer_account_id', $account->id)
            ->with([
                'apiKey',
                'familyAgent',
                'provider',
                'providerModel',
                'messages' => fn ($query) => $query->orderBy('created_at'),
            ])
            ->firstOrFail();

        return view('customer.threads.show', [
            'thread' => $thread,
        ]);
    }

    public function export(string $publicId): StreamedResponse
    {
        $account = auth()->user()->customerAccount;
        abort_unless($account, 403);

        $thread = Thread::query()
            ->where('public_id', $publicId)
            ->where('customer_account_id', $account->id)
            ->with([
                'apiKey',
                'familyAgent',
                'provider',
                'providerModel',
                'messages' => fn ($query) => $query->orderBy('created_at'),
            ])
            ->firstOrFail();

        return response()->streamDownload(
            function () use ($thread) {
                echo $this->exporter->render($thread);
            },
            $this->exporter->filename($thread),
            ['Content-Type' => 'text/markdown; charset=UTF-8'],
        );
    }
}
