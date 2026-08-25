<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Support\Locale;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * One entry per locale, each listing every translation as an alternate —
     * the sitemap counterpart of the hreflang tags on the page itself.
     *
     * Books are included one by one. A sitemap naming only the home page and
     * the catalogue leaves a search engine no way to reach any individual
     * book, which is most of what the library holds.
     */
    public function index(): Response
    {
        // Several megabytes of XML per crawl, from a catalogue that changes a
        // few times a week. Built once and kept until an admin write clears
        // the cache (see CachePage::flush()).
        $xml = Cache::remember('sitemap.'.$this->host(), now()->addHours(12), fn () => $this->build());

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

    private function build(): string
    {
        $xml = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            .'xmlns:xhtml="http://www.w3.org/1999/xhtml"/>'
        );

        // The home page and the catalogue, each in every language.
        $pages = [
            ['url' => fn (string $l) => Locale::url($l), 'priority' => '1.0'],
            ['url' => fn (string $l) => Locale::booksUrl($l), 'priority' => '0.9'],
        ];

        foreach ($pages as $page) {
            foreach (Locale::SUPPORTED as $locale) {
                $url = $xml->addChild('url');
                $url->addChild('loc', htmlspecialchars(($page['url'])($locale)));
                $url->addChild('changefreq', 'weekly');
                $url->addChild('priority', $locale === Locale::DEFAULT ? $page['priority'] : '0.8');

                foreach (Locale::SUPPORTED as $alternate) {
                    $link = $url->addChild('xhtml:link', null, 'http://www.w3.org/1999/xhtml');
                    $link->addAttribute('rel', 'alternate');
                    $link->addAttribute('hreflang', Locale::languageTag($alternate));
                    $link->addAttribute('href', ($page['url'])($alternate));
                }
            }
        }

        // Every book, in every language. Without these the catalogue is one
        // page to a search engine and the thousand books inside it cannot be
        // found at all.
        Book::query()
            ->select(['id', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($books) use ($xml) {
                foreach ($books as $book) {
                    foreach (Locale::SUPPORTED as $locale) {
                        $url = $xml->addChild('url');
                        $url->addChild('loc', htmlspecialchars(Locale::bookUrl($book->id, $locale)));
                        $url->addChild('lastmod', $book->updated_at?->toDateString() ?? '');
                        $url->addChild('changefreq', 'monthly');
                        $url->addChild('priority', $locale === Locale::DEFAULT ? '0.7' : '0.6');

                        foreach (Locale::SUPPORTED as $alternate) {
                            $link = $url->addChild('xhtml:link', null, 'http://www.w3.org/1999/xhtml');
                            $link->addAttribute('rel', 'alternate');
                            $link->addAttribute('hreflang', Locale::languageTag($alternate));
                            $link->addAttribute('href', Locale::bookUrl($book->id, $alternate));
                        }
                    }
                }
            });

        return (string) $xml->asXML();
    }
}
