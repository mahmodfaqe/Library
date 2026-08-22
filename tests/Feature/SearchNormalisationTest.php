<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Support\ArabicText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchNormalisationTest extends TestCase
{
    use RefreshDatabase;

    private function book(string $title, ?string $author = null): Book
    {
        return Book::create([
            'title' => $title,
            'author' => $author,
            'url' => 'https://example.test/'.md5($title),
        ]);
    }

    public function test_an_arabic_spelling_is_found_by_a_kurdish_keyboard(): void
    {
        // The collection carries both spellings: 106 titles use the Arabic
        // kaf and 259 the Arabic yeh, while a Kurdish keyboard types neither.
        $this->book('أسس الكيمياء العضوية');

        // ك -> ک and ي -> ی, as typed on a Kurdish layout
        $this->assertSame(1, Book::matching('کیمیا')->count());
    }

    public function test_a_kurdish_spelling_is_found_by_an_arabic_keyboard(): void
    {
        $this->book('کیمیای ژینگە');

        $this->assertSame(1, Book::matching('كيميا')->count());
    }

    public function test_diacritics_do_not_prevent_a_match(): void
    {
        $this->book('الكِيمِياءُ التحليلية');

        $this->assertSame(1, Book::matching('الكيمياء')->count());
    }

    public function test_teh_marbuta_matches_heh(): void
    {
        $this->book('علوم الحياة');

        $this->assertSame(1, Book::matching('الحياه')->count());
    }

    public function test_latin_search_is_case_insensitive(): void
    {
        $this->book('Molecular Biology of the Cell');

        $this->assertSame(1, Book::matching('BIOLOGY')->count());
        $this->assertSame(1, Book::matching('molecular')->count());
    }

    public function test_the_author_is_searchable_too(): void
    {
        $this->book('Clinical Examination', 'Macleod');

        $this->assertSame(1, Book::matching('macleod')->count());
    }

    public function test_punctuation_between_words_is_ignored(): void
    {
        $this->book('الفحوص-المختبرية');

        $this->assertSame(1, Book::matching('الفحوص المختبرية')->count());
    }

    public function test_a_wildcard_never_widens_the_match(): void
    {
        $this->book('Molecular Biology');
        $this->book('Organic Chemistry');

        // '%' carries no meaning after folding, so a bare one is the same as
        // searching for nothing rather than matching everything by accident.
        $this->assertSame(2, Book::matching('%')->count());

        // And inside a term it stays literal: this must not match "Biology".
        $this->assertSame(0, Book::matching('Bio%logy')->count());
        $this->assertSame(0, Book::matching('_iology')->count());
    }

    public function test_an_unrelated_term_still_finds_nothing(): void
    {
        $this->book('أسس الكيمياء العضوية');

        $this->assertSame(0, Book::matching('فیزیا')->count());
    }

    public function test_the_folded_text_follows_a_rename(): void
    {
        $book = $this->book('Old title');
        $book->update(['title' => 'کیمیای گشتی']);

        $this->assertSame(1, Book::matching('كيميا')->count());
        $this->assertSame(0, Book::matching('old title')->count());
    }

    public function test_arabic_indic_digits_match_ascii(): void
    {
        $this->book('١٩٨٤');

        $this->assertSame(1, Book::matching('1984')->count());
    }

    public function test_folding_is_stable(): void
    {
        $this->assertSame(
            ArabicText::fold('الكيمياء'),
            ArabicText::fold('الکیمیاء'),
        );
    }
}
