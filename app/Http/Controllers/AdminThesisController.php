<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CachePage;
use App\Models\Activity;
use App\Models\Department;
use App\Models\Thesis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AdminThesisController extends Controller
{
    /**
     * Everything in the repository, in whatever state it is in.
     *
     * The public list shows only what has been approved; this one exists to
     * work through the rest, so it opens on what is waiting.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $status = in_array($status, Thesis::STATUSES, true) ? $status : null;

        return view('admin.theses.index', [
            'theses' => Thesis::query()
                ->with(['department', 'submitter'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->orderByRaw("case status when 'under_review' then 0 when 'draft' then 1 else 2 end")
                ->orderByDesc('year')
                ->orderByDesc('id')
                ->paginate(30)
                ->withQueryString(),
            'status' => $status,
            'waiting' => Thesis::where('status', Thesis::UNDER_REVIEW)->count(),
            'counts' => Thesis::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.theses.form', [
            'thesis' => new Thesis(['status' => Thesis::DRAFT, 'year' => (int) date('Y')]),
            'departments' => Department::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $thesis = new Thesis($this->validated($request));
        $thesis->submitted_by = Auth::id();
        $this->stamp($thesis);
        $thesis->save();

        Activity::record('thesis.created', $thesis->title);
        CachePage::flush();

        return redirect()->route('admin.theses')->with('status', __('admin.flash.thesis_created'));
    }

    public function edit(Thesis $thesis): View
    {
        return view('admin.theses.form', [
            'thesis' => $thesis,
            'departments' => Department::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Thesis $thesis): RedirectResponse
    {
        $was = $thesis->status;

        $thesis->fill($this->validated($request, $thesis));
        $this->stamp($thesis, $was);
        $thesis->save();

        Activity::record('thesis.updated', $thesis->title);
        CachePage::flush();

        return redirect()->route('admin.theses')->with('status', __('admin.flash.thesis_updated'));
    }

    public function destroy(Thesis $thesis): RedirectResponse
    {
        if ($thesis->hasFile()) {
            Storage::disk('books')->delete($thesis->file_path);
        }

        $title = $thesis->title;
        $thesis->delete();

        Activity::record('thesis.deleted', $title);
        CachePage::flush();

        return redirect()->route('admin.theses')->with('status', __('admin.flash.thesis_deleted'));
    }

    /**
     * Record who approved it, and when.
     *
     * A repository that cannot say who published a thesis is not one anybody
     * should trust, and the question is always asked after the fact.
     */
    private function stamp(Thesis $thesis, ?string $was = null): void
    {
        if ($thesis->status === Thesis::PUBLISHED && $was !== Thesis::PUBLISHED) {
            $thesis->approved_by = Auth::id();
            $thesis->approved_at = now();
        }

        if ($thesis->status !== Thesis::PUBLISHED) {
            $thesis->approved_by = null;
            $thesis->approved_at = null;
        }
    }

    private function validated(Request $request, ?Thesis $thesis = null): array
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:500'],
            'title_en' => ['nullable', 'string', 'max:500'],
            'author' => ['required', 'string', 'max:190'],
            'supervisor' => ['nullable', 'string', 'max:190'],
            'co_supervisor' => ['nullable', 'string', 'max:190'],
            'degree' => ['required', 'in:'.implode(',', Thesis::DEGREES)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'year' => ['required', 'integer', 'min:1970', 'max:'.((int) date('Y') + 1)],
            'defended_on' => ['nullable', 'date'],
            'language' => ['nullable', 'string', 'max:40'],
            'pages' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'abstract' => ['nullable', 'string', 'max:8000'],
            'abstract_en' => ['nullable', 'string', 'max:8000'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'doi' => ['nullable', 'string', 'max:255', 'regex:#^(https?://(dx\.)?doi\.org/)?10\.\d{4,9}/\S+$#i'],
            'status' => ['required', 'in:'.implode(',', Thesis::STATUSES)],
            'embargo_until' => ['nullable', 'date', 'after:today'],
            'license' => ['nullable', 'in:'.implode(',', Thesis::LICENCES)],
            'url' => ['nullable', 'url', 'max:500'],
            'file' => [
                'nullable', 'file', 'mimetypes:application/pdf',
                'max:'.(int) config('library.max_upload_kb'),
            ],
        ]);

        // A thesis nobody can read is not published, whatever the form says.
        $validator->after(function ($validator) use ($request, $thesis) {
            $publishing = $request->input('status') === Thesis::PUBLISHED;
            $reachable = $request->hasFile('file')
                || filled($request->input('url'))
                || $thesis?->hasFile()
                || filled($thesis?->url);

            if ($publishing && ! $reachable) {
                $validator->errors()->add('url', __('admin.theses.needs_a_file'));
            }
        });

        $data = $validator->validate();

        if (filled($data['doi'] ?? null)) {
            $data['doi'] = preg_replace('#^https?://(dx\.)?doi\.org/#i', '', trim($data['doi']));
        }

        if ($file = $request->file('file')) {
            if ($thesis?->hasFile()) {
                Storage::disk('books')->delete($thesis->file_path);
            }

            $data['file_path'] = $file->store('', 'books');
            $data['file_size'] = $file->getSize();
        }

        unset($data['file']);

        return $data;
    }
}
