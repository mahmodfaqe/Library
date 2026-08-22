<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'author', 'year', 'language', 'department_id', 'url', 'cover_url', 'file_path', 'file_size'])]
class Book extends Model
{
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Match a visitor's search against the title and the author.
     */
    public function scopeMatching(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)->orWhere('author', 'like', $like);
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
