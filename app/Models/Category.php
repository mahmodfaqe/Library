<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

#[Fillable(['name', 'icon', 'sort_order', 'drive_folder_id', 'translations'])]
class Category extends Model
{
    protected function casts(): array
    {
        return ['translations' => 'array'];
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * The subject's name in one language, falling back to the Kurdish Sorani
     * original it was imported under.
     */
    public function localName(?string $locale = null): string
    {
        $locale ??= App::getLocale();

        return filled($this->translations[$locale] ?? null)
            ? $this->translations[$locale]
            : $this->name;
    }
}
