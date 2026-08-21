<?php

namespace Tests\Unit;

use App\Support\RichText;
use PHPUnit\Framework\TestCase;

class RichTextTest extends TestCase
{
    public function test_it_escapes_the_translated_text(): void
    {
        $html = RichText::make('5 < 6 & "quoted"', [])->toHtml();

        $this->assertSame('5 &lt; 6 &amp; &quot;quoted&quot;', $html);
    }

    public function test_it_splices_fragments_without_escaping_them(): void
    {
        $html = RichText::make('opened on :date today', ['date' => '<strong>18/10/2025</strong>'])->toHtml();

        $this->assertSame('opened on <strong>18/10/2025</strong> today', $html);
    }

    public function test_markup_in_the_translation_itself_is_never_trusted(): void
    {
        $html = RichText::make('<script>alert(1)</script> :qr', ['qr' => '<a href="#">QR</a>'])->toHtml();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('<a href="#">QR</a>', $html);
    }

    public function test_a_second_fragment_does_not_re_escape_the_first(): void
    {
        $html = RichText::make(':a and :b', ['a' => '<i>one</i>', 'b' => '<i>two</i>'])->toHtml();

        $this->assertSame('<i>one</i> and <i>two</i>', $html);
    }

    public function test_an_unused_placeholder_is_left_alone(): void
    {
        $html = RichText::make('no tokens here', ['qr' => '<a href="#">QR</a>'])->toHtml();

        $this->assertSame('no tokens here', $html);
    }
}
