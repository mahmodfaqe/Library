<?php

namespace App\Support;

class Asset
{
    /**
     * A public asset URL carrying the file's own timestamp.
     *
     * Browsers hold on to icons harder than to anything else — Safari caches a
     * favicon per page and keeps serving it across ordinary reloads — so a
     * replaced file needs a new URL to be picked up at all.
     */
    public static function versioned(string $path): string
    {
        $file = public_path($path);

        if (! is_file($file)) {
            return asset($path);
        }

        return asset($path).'?v='.filemtime($file);
    }
}
