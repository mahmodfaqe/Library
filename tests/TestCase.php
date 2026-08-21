<?php

namespace Tests;

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

    protected function clearPageCache(): void
    {
        foreach (glob(storage_path('framework/pagecache').'/*') ?: [] as $file) {
            @unlink($file);
        }
    }
}
