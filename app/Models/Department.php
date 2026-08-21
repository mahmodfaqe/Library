<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['sort_order', 'icon', 'drive_url', 'translations'])]
class Department extends Model
{
    protected function casts(): array
    {
        return [
            'translations' => 'array',
        ];
    }

    /**
     * Get a translated field for the given locale, falling back to Kurdish (Sorani).
     */
    public function translation(string $lang, string $field): string
    {
        $translations = $this->translations ?? [];

        if (isset($translations[$lang][$field])) {
            return $translations[$lang][$field];
        }

        if (isset($translations['ku-sorani'][$field])) {
            return $translations['ku-sorani'][$field];
        }

        return '';
    }
}
