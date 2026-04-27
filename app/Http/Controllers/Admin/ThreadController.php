<?php

namespace App\Http\Controllers\Admin;

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
        return view('admin.threads.index', [
            'threads' => Thread::query()->with(['customerAccount', 'familyAgent', 'provider', 'providerModel'])->latest()->limit(100)->get(),
        ]);
    }

    public function show(Thread $thread): View
    {
        $thread->load([
            'customerAccount',
            'apiKey',
            'familyAgent',
            'provider',
            'providerModel',
            'messages' => fn ($query) => $query->orderBy('created_at'),
        ]);

        return view('admin.threads.show', [
            'thread' => $thread,
        ]);
    }

    public function export(Thread $thread): StreamedResponse
    {
        $thread->load([
            'customerAccount',
            'apiKey',
            'familyAgent',
            'provider',
            'providerModel',
            'messages' => fn ($query) => $query->orderBy('created_at'),
        ]);

        return response()->streamDownload(
            function () use ($thread) {
                echo $this->exporter->render($thread);
            },
            $this->exporter->filename($thread),
            ['Content-Type' => 'text/markdown; charset=UTF-8'],
        );
    }
}
