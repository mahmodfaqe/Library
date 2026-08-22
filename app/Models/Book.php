<?php

namespace App\Models;

use App\Support\ArabicText;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'author', 'year', 'language', 'department_id', 'category_id', 'drive_file_id', 'url', 'cover_url', 'file_path', 'file_size'])]
class Book extends Model
{
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeInCategory(Builder $query, ?string $categoryId): Builder
    {
        return $categoryId ? $query->where('category_id', $categoryId) : $query;
    }

    public function scopeInLanguage(Builder $query, ?string $language): Builder
    {
        return $language ? $query->where('language', $language) : $query;
    }

    /**
     * Match a visitor's search against the folded title and author, so the
     * spelling of ك/ک and ي/ی on their keyboard does not decide the result.
     *
     * Words the visitor separated with a space have to appear, but not in the
     * order typed and not next to each other: "کیمیا ئەندامی" finds "کیمیای
     * ئەندامی" and "ئەندامی، کیمیای گشتی" alike. Splitting happens before
     * folding, so punctuation inside a word stays inside it — "Mol%Biology" is
     * one term that matches nothing, not two that would match "Molecular
     * Biology", and % keeps its lack of meaning as a LIKE wildcard.
     *
     * The folded copy is also compared against the raw title and author. A row
     * imported before search_text existed, or written by a bulk insert that
     * skipped the model, would otherwise be invisible to search until the next
     * reindex; this way the catalogue still finds it.
     */
    public function scopeMatching(Builder $query, ?string $term): Builder
    {
        $terms = collect(preg_split('/\s+/u', (string) $term, -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->map(fn (string $word) => ArabicText::fold($word))
            ->filter()
            ->values();

        if ($terms->isEmpty()) {
            return $query;
        }

        return $query->where(function (Builder $outer) use ($terms) {
            foreach ($terms as $word) {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $word).'%';

                $outer->where(function (Builder $inner) use ($like) {
                    $inner->where('search_text', 'like', $like)
                        ->orWhere('title', 'like', $like)
                        ->orWhere('author', 'like', $like);
                });
            }
        });
    }

    /**
     * Keep the folded copy in step with whatever the record now says.
     */
    protected static function booted(): void
    {
        static::saving(function (self $book) {
            $book->search_text = ArabicText::fold($book->title.' '.$book->author);
        });
    }

    public function scopeInDepartment(Builder $query, ?string $departmentId): Builder
    {
        return $departmentId ? $query->where('department_id', $departmentId) : $query;
    }

    /**
     * A book is readable if it is either held here or linked elsewhere.
     */
    public function hasFile(): bool
    {
        return filled($this->file_path);
    }

    /**
     * Where a visitor should be sent to read it — the local copy when there
     * is one, otherwise whatever external link the record carries.
     */
    public function readUrl(): ?string
    {
        return $this->hasFile() ? route('books.download', $this) : ($this->url ?: null);
    }

    public function humanFileSize(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        $mb = $this->file_size / 1024 / 1024;

        return $mb >= 1
            ? number_format($mb, 1).' MB'
            : number_format($this->file_size / 1024).' KB';
    }
}
