<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Thesis;
use App\Support\Citation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ThesisController extends Controller
{
    /**
     * The repository of work written at the university.
     *
     * Everything here is filtered through published(): a thesis that has not
     * been examined and approved is somebody's draft, and one that has been
     * withdrawn was withdrawn for a reason.
     */
    public function index(Request $request): View
    {
        $term = $this->textQuery($request, 'q');
        $degree = $this->textQuery($request, 'degree');
        $department = $this->textQuery($request, 'department');
        $year = $this->textQuery($request, 'year');

        $theses = Thesis::query()
            ->published()
            ->with('department')
            ->matching($term)
            ->ofDegree($degree)
            ->inDepartment($department)
            ->ofYear($year)
            ->orderByDesc('year')
            ->orderBy('title')
            ->paginate(24)
            ->withQueryString();

        return view('theses.index', [
            'theses' => $theses,
            'term' => $term,
            'degree' => $degree,
            'department' => $department,
            'year' => $year,
            'departments' => Department::orderBy('sort_order')->get(),
            // Only the years that have something in them: an empty year in a
            // filter is a dead end a reader has to discover by trying it.
            'years' => Thesis::published()->distinct()->orderByDesc('year')->pluck('year'),
            'degrees' => Thesis::published()
                ->selectRaw('degree, count(*) as total')
                ->groupBy('degree')
                ->pluck('total', 'degree'),
        ]);
    }

    public function show(Thesis $thesis): View
    {
        abort_unless($thesis->isPublished(), 404);

        return view('theses.show', [
            'thesis' => $thesis->load('department'),
            'related' => Thesis::published()
                ->with('department')
                ->when(
                    $thesis->department_id,
                    fn ($query) => $query->where('department_id', $thesis->department_id),
                    fn ($query) => $query->where('degree', $thesis->degree)
                )
                ->whereKeyNot($thesis->getKey())
                ->orderByDesc('year')
                ->limit(4)
                ->get(),
        ]);
    }

    /**
     * The thesis as a file a reference manager can swallow.
     *
     * The record is public even under embargo, so this is too: what an
     * embargo withholds is the reading of the work, not the fact of it.
     */
    public function cite(Thesis $thesis, string $format): Response
    {
        abort_unless($thesis->isPublished(), 404);
        abort_unless(in_array($format, Citation::FORMATS, true), 404);

        return response(Citation::writeThesis($thesis, $format), 200, [
            'Content-Type' => Citation::contentType($format).'; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.Citation::thesisFilename($thesis, $format).'"',
        ]);
    }

    public function download(Thesis $thesis): StreamedResponse
    {
        abort_unless($thesis->isPublished(), 404);

        // An embargo is the whole point of having one: until it lifts, the
        // file is not served, however the address was arrived at.
        abort_if($thesis->isUnderEmbargo(), 403, __('theses.embargoed'));

        abort_unless($thesis->hasFile() && Storage::disk('books')->exists($thesis->file_path), 404);

        $thesis->incrementQuietly('downloads');

        return Storage::disk('books')->response(
            $thesis->file_path,
            Str::of($thesis->title)->limit(80, '')->slug().'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * A query parameter as a plain string, or null. Arrays and nested input
     * arrive from crafted URLs and would otherwise reach the query builder.
     */
    private function textQuery(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
