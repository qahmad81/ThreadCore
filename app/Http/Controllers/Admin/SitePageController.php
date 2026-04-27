<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SitePageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', ['pages' => SitePage::query()->orderBy('slug')->get()]);
    }

    public function create(): View
    {
        return view('admin.pages.form', ['page' => new SitePage()]);
    }

    public function store(Request $request): RedirectResponse
    {
        SitePage::query()->create($this->validated($request));

        return redirect()->route('admin.pages.index')->with('status', 'Page created.');
    }

    public function edit(SitePage $page): View
    {
        return view('admin.pages.form', ['page' => $page]);
    }

    public function update(Request $request, SitePage $page): RedirectResponse
    {
        $page->update($this->validated($request));

        return redirect()->route('admin.pages.index')->with('status', 'Page updated.');
    }

    public function destroy(SitePage $page): RedirectResponse
    {
        if ($page->slug === 'landing') {
            return back()->withErrors(['page' => 'The landing page cannot be deleted.']);
        }

        $page->delete();

        return redirect()->route('admin.pages.index')->with('status', 'Page deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:160'],
            'headline' => ['required', 'string', 'max:220'],
            'summary' => ['nullable', 'string'],
            'blocks_text' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $blocks = collect(preg_split('/\R{2,}/', trim($data['blocks_text'] ?? '')))
            ->filter()
            ->map(function (string $block): array {
                [$label, $title, $body] = array_pad(array_map('trim', explode('|', $block, 3)), 3, '');

                return compact('label', 'title', 'body');
            })
            ->values()
            ->all();

        return [
            'slug' => str($data['slug'])->slug()->toString(),
            'title' => $data['title'],
            'headline' => $data['headline'],
            'summary' => $data['summary'] ?? null,
            'blocks' => $blocks,
            'is_published' => $request->boolean('is_published'),
        ];
    }
}
