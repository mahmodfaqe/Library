<?php

namespace App\Models;

use App\Support\ArabicText;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title', 'title_en', 'author', 'supervisor', 'co_supervisor', 'degree',
    'department_id', 'year', 'defended_on', 'language', 'pages',
    'abstract', 'abstract_en', 'keywords', 'doi',
    'url', 'drive_file_id', 'file_path', 'file_size',
    'status', 'embargo_until', 'license',
])]
class Thesis extends Model
{
    /**
     * The degrees the college awards. Kept in the order a reader expects to
     * see them, which is also the order they are earned in.
     */
    public const DEGREES = ['bachelor', 'master', 'phd'];

    /**
     * A thesis is written, then examined, then published. Only the last of
     * those is a thing the public has any business seeing.
     */
    public const DRAFT = 'draft';

    public const UNDER_REVIEW = 'under_review';

    public const PUBLISHED = 'published';

    public const WITHDRAWN = 'withdrawn';

    public const STATUSES = [self::DRAFT, self::UNDER_REVIEW, self::PUBLISHED, self::WITHDRAWN];

    /**
     * The licences a student may place their work under. All rights reserved
     * is the default, because it is what applies when nobody has chosen.
     */
    public const LICENCES = ['all-rights-reserved', 'cc-by', 'cc-by-sa', 'cc-by-nc', 'cc-by-nc-nd', 'cc0'];

    /**
     * How well this answers the search that found it. Not a column.
     */
    public float $relevance = 0;

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'pages' => 'integer',
            'defended_on' => 'date',
            'embargo_until' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── What the public may see ─────────────────────────────────────────

    /**
     * Only what has been examined and approved. A draft is somebody's work in
     * progress and a withdrawn thesis has been taken back for a reason.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::PUBLISHED);
    }

    public function isPublished(): bool
    {
        return $this->status === self::PUBLISHED;
    }

    /**
     * An embargo withholds the file, never the record.
     *
     * A thesis awaiting a journal's decision or a patent filing still exists,
     * is still citable, and its author still gets the credit — what waits is
     * only the reading of it. Hiding the record instead would make the work
     * disappear for the year or two it matters most.
     */
    public function isUnderEmbargo(): bool
    {
        return $this->embargo_until !== null && $this->embargo_until->isFuture();
    }

    public function isReadable(): bool
    {
        return $this->isPublished() && ! $this->isUnderEmbargo() && $this->readUrl() !== null;
    }

    public function hasFile(): bool
    {
        return filled($this->file_path);
    }

    public function readUrl(): ?string
    {
        if ($this->hasFile()) {
            return route('theses.download', $this);
        }

        return $this->url ?: null;
    }

    public function doiUrl(): ?string
    {
        return $this->doi ? 'https://doi.org/'.$this->doi : null;
    }

    /**
     * The address this thesis is cited by: its DOI where the university has
     * issued one, and otherwise its page here, which is permanent.
     */
    public function permanentUrl(?string $locale = null): string
    {
        return $this->doiUrl() ?: Locale::thesisUrl($this->id, $locale);
    }

    // ── Reading it in the reader's language ─────────────────────────────

    /**
     * The title as this reader should see it: English on the English pages
     * where an English title exists, and otherwise as it was written.
     */
    public function localTitle(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'en' && filled($this->title_en)) {
            return $this->title_en;
        }

        return $this->title;
    }

    public function localAbstract(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'en' && filled($this->abstract_en)) {
            return $this->abstract_en;
        }

        return $this->abstract ?: $this->abstract_en;
    }

    /**
     * @return list<string>
     */
    public function keywordList(): array
    {
        if (! $this->keywords) {
            return [];
        }

        $parts = preg_split('/[,،;؛]+/u', $this->keywords) ?: [];

        return array_values(array_filter(array_map('trim', $parts), fn ($word) => $word !== ''));
    }

    public function humanFileSize(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        return number_format($this->file_size / 1048576, 1).' MB';
    }

    // ── Searching ───────────────────────────────────────────────────────

    public function scopeMatching(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $folded = ArabicText::fold($term);

        return $query->where(function (Builder $inner) use ($folded) {
            foreach (preg_split('/\s+/u', trim($folded)) ?: [] as $word) {
                if ($word === '') {
                    continue;
                }

                $inner->where('search_text', 'like', '%'.self::escape($word).'%');
            }
        });
    }

    public function scopeOfDegree(Builder $query, ?string $degree): Builder
    {
        return in_array($degree, self::DEGREES, true) ? $query->where('degree', $degree) : $query;
    }

    public function scopeInDepartment(Builder $query, ?string $department): Builder
    {
        return $department ? $query->where('department_id', $department) : $query;
    }

    public function scopeOfYear(Builder $query, ?string $year): Builder
    {
        return $year ? $query->where('year', (int) $year) : $query;
    }

    /**
     * The characters LIKE treats as wildcards carry no meaning in a search a
     * reader typed.
     */
    private static function escape(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    protected static function booted(): void
    {
        static::saving(function (self $thesis) {
            $thesis->search_text = ArabicText::fold(implode(' ', array_filter([
                $thesis->title,
                $thesis->title_en,
                $thesis->author,
                $thesis->supervisor,
                $thesis->co_supervisor,
                $thesis->keywords,
            ])));
        });
    }
}
