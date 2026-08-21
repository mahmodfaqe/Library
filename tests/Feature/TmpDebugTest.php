<?php

namespace Tests\Feature;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TmpDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug(): void
    {
        $w = $this->withSession(['locale' => 'en'])->get('/');
        fwrite(STDERR, "\nWARM=".var_export($w->headers->get('X-Page-Cache'), true)."\n");

        Department::create([
            'sort_order' => 1,
            'icon' => '\u{1F9EC}',
            'drive_url' => 'https://drive.google.com/drive/folders/abc',
            'translations' => ['en' => ['title' => 'Biology', 'desc' => 'd', 'button' => 'b']],
        ]);
        fwrite(STDERR, "COUNT=".Department::count()."\n");

        $h = $this->withSession(['locale' => 'en'])->get('/');
        fwrite(STDERR, "SECOND=".var_export($h->headers->get('X-Page-Cache'), true)." bio=".var_export(str_contains($h->getContent(), 'Biology'), true)."\n");

        $this->clearPageCache();
        fwrite(STDERR, "FILES_AFTER_CLEAR=".count(glob(storage_path('framework/pagecache').'/*') ?: [])."\n");

        $t = $this->withSession(['locale' => 'en'])->get('/');
        fwrite(STDERR, "THIRD=".var_export($t->headers->get('X-Page-Cache'), true)." bio=".var_export(str_contains($t->getContent(), 'Biology'), true)."\n");
        fwrite(STDERR, "COUNT_AFTER=".Department::count()."\n");
        $this->assertTrue(true);
    }
}
