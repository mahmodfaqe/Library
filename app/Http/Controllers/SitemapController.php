<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Support\Locale;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * The library is listed as one sitemap per language, gathered under an
     * index at /sitemap.xml.
     *
     * Every book has a page in every language, and each entry names all the
     * translations as alternates — the sitemap counterpart of the hreflang
     * tags on the page itself. That is eight entries and some seventy
     * alternate links per book, so a single file would be megabytes of XML
     * held in one cache row and built in one pass of memory. Split by
     * language, each file stays around a megabyte however far the collection
     * grows.
     */
    public function index(): Response
    {
        $xml = Cache::remember(
            'sitemap.index.'.$this->host(),
            now()->addHours(12),
            fn () => $this->buildIndex()
        );

        return $this->respond($xml);
    }

    /**
     * One language's pages: the home page, the catalogue, and every book.
     */
    public function locale(string $locale): Response
    {
        abort_unless(Locale::supports($locale), 404);

        $xml = Cache::remember(
            'sitemap.'.$locale.'.'.$this->host(),
            now()->addHours(12),
            fn () => $this->build($locale)
        );

        return $this->respond($xml);
    }

    private function respond(string $xml): Response
    {
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * The sitemap is full of absolute URLs, so a copy built for one host must
     * not be served under another.
     */
    private function host(): string
    {
        return substr(md5(url('/')), 0, 8);
    }

    private function buildIndex(): string
    {
        $xml = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'
            .'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>'
        );

        // The catalogue's own clock: the day the newest change was made.
        $changed = Book::max('updated_at');
        $lastmod = $changed ? substr((string) $changed, 0, 10) : now()->toDateString();

        foreach (Locale::SUPPORTED as $locale) {
            $entry = $xml->addChild('sitemap');
            $entry->addChild('loc', htmlspecialchars(url('sitemap-'.$locale.'.xml')));
            $entry->addChild('lastmod', $lastmod);
        }

        return (string) $xml->asXML();
    }

    private function build(string $locale): string
    {
        $xml = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            .'xmlns:xhtml="http://www.w3.org/1999/xhtml"/>'
        );

        $default = $locale === Locale::DEFAULT;

        // The home page and the catalogue.
        $pages = [
            ['url' => fn (string $l) => Locale::url($l), 'priority' => $default ? '1.0' : '0.8'],
            ['url' => fn (string $l) => Locale::booksUrl($l), 'priority' => $default ? '0.9' : '0.8'],
        ];

        foreach ($pages as $page) {
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars(($page['url'])($locale)));
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', $page['priority']);

            $this->addAlternates($url, $page['url']);
        }

        // Every book. Without these the catalogue is one page to a search
        // engine and the thousand books inside it cannot be found at all.
        Book::query()
            ->select(['id', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($books) use ($xml, $locale, $default) {
                foreach ($books as $book) {
                    $url = $xml->addChild('url');
                    $url->addChild('loc', htmlspecialchars(Locale::bookUrl($book->id, $locale)));
                    $url->addChild('lastmod', $book->updated_at?->toDateString() ?? '');
                    $url->addChild('changefreq', 'monthly');
                    $url->addChild('priority', $default ? '0.7' : '0.6');

                    $this->addAlternates($url, fn (string $l) => Locale::bookUrl($book->id, $l));
                }
            });

        return (string) $xml->asXML();
    }

    /**
     * Name every translation of one page, so a search engine can offer a
     * reader the language they read in rather than the one it crawled.
     */
    private function addAlternates(\SimpleXMLElement $url, callable $address): void
    {
        foreach (Locale::SUPPORTED as $alternate) {
            $this->addAlternate($url, Locale::languageTag($alternate), $address($alternate));
        }

        // And where to send a reader whose language is none of them, matching
        // the tags in the page's own head.
        $this->addAlternate($url, 'x-default', $address(Locale::DEFAULT));
    }

    private function addAlternate(\SimpleXMLElement $url, string $hreflang, string $href): void
    {
        $link = $url->addChild('xhtml:link', null, 'http://www.w3.org/1999/xhtml');
        $link->addAttribute('rel', 'alternate');
        $link->addAttribute('hreflang', $hreflang);
        $link->addAttribute('href', $href);
    }
}
