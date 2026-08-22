<?php

namespace Tests;

use App\Support\Locale;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The home page cache lives on disk and would otherwise carry over
        // between tests — and between a test run and the developer's own app.
        $this->clearPageCache();
    }

    protected function tearDown(): void
    {
        $this->clearPageCache();

        parent::tearDown();
    }

    /**
     * The home page URL for a locale: the default locale is served at the
     * root, every other locale behind its own prefix.
     */
    protected function homeUrl(string $locale): string
    {
        return $locale === Locale::DEFAULT ? '/' : "/$locale";
    }

    protected function clearPageCache(): void
    {
        foreach (glob(storage_path('framework/pagecache').'/*') ?: [] as $file) {
            @unlink($file);
        }
    }
}
