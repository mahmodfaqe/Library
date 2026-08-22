<?php

namespace App\Http\Controllers;

use App\Support\Locale;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * One entry per locale, each listing every translation as an alternate —
     * the sitemap counterpart of the hreflang tags on the page itself.
     */
    public function index(): Response
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

        return response($xml->asXML(), 200, ['Content-Type' => 'application/xml']);
    }
}
