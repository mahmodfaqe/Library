<?php

namespace App\Http\Controllers;

use App\Models\Thesis;
use App\Support\BookLanguage;
use App\Support\Locale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OaiController extends Controller
{
    /**
     * The Open Archives Initiative Protocol for Metadata Harvesting.
     *
     * This is what turns a website with theses on it into a repository. BASE,
     * CORE, OpenAIRE and the national aggregators do not read web pages; they
     * ask an endpoint like this one, in this exact shape, and copy what it
     * answers into their own indexes. Without it the university's work is
     * findable only by whoever already knows the address.
     *
     * The protocol is old and unforgiving: the verbs, the argument checking
     * and the error codes are all specified, and a harvester that meets
     * something unexpected gives up rather than guesses.
     *
     * @see https://www.openarchives.org/OAI/openarchivesprotocol.html
     */
    private const PAGE = 100;

    /**
     * The namespaces and schemas the protocol fixes. They are not addresses
     * this application fetches — nothing here reads them — but the exact
     * strings a harvester checks the answer against, so they are named once
     * and never typed twice.
     */
    private const NS_OAI = 'http://www.openarchives.org/OAI/2.0/';

    private const NS_XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    private const NS_DC = 'http://purl.org/dc/elements/1.1/';

    private const NS_OAI_DC = 'http://www.openarchives.org/OAI/2.0/oai_dc/';

    private const XSD_OAI = 'http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd';

    private const XSD_OAI_DC = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';

    public function __invoke(Request $request): Response
    {
        $verb = (string) $request->query('verb', '');

        return match ($verb) {
            'Identify' => $this->identify($request),
            'ListMetadataFormats' => $this->listMetadataFormats($request),
            'ListSets' => $this->listSets($request),
            'ListIdentifiers' => $this->listRecords($request, headersOnly: true),
            'ListRecords' => $this->listRecords($request, headersOnly: false),
            'GetRecord' => $this->getRecord($request),
            default => $this->error($request, 'badVerb', 'The verb is missing or not one this repository knows.'),
        };
    }

    // ── The verbs ───────────────────────────────────────────────────────

    private function identify(Request $request): Response
    {
        // The earliest record there is: a harvester uses it to know how far
        // back it may ask for.
        $earliest = Thesis::published()->min('created_at');

        return $this->respond($request, '<Identify>'
            .$this->tag('repositoryName', __('messages.site_title', [], 'en'))
            .$this->tag('baseURL', route('oai'))
            .$this->tag('protocolVersion', '2.0')
            .$this->tag('adminEmail', config('library.contact_email') ?: 'library@uor.edu.krd')
            .$this->tag('earliestDatestamp', $earliest ? $this->stamp($earliest) : '2020-01-01T00:00:00Z')
            // Nothing is ever deleted from the record: a withdrawn thesis
            // keeps its identifier and stops being listed.
            .$this->tag('deletedRecord', 'no')
            .$this->tag('granularity', 'YYYY-MM-DDThh:mm:ssZ')
            .'</Identify>');
    }

    private function listMetadataFormats(Request $request): Response
    {
        // Dublin Core only. It is the one every harvester must understand,
        // and offering a richer format that none of them asks for helps
        // nobody.
        return $this->respond($request, '<ListMetadataFormats><metadataFormat>'
            .$this->tag('metadataPrefix', 'oai_dc')
            .$this->tag('schema', self::XSD_OAI_DC)
            .$this->tag('metadataNamespace', self::NS_OAI_DC)
            .'</metadataFormat></ListMetadataFormats>');
    }

    private function listSets(Request $request): Response
    {
        $sets = '';

        foreach (Thesis::DEGREES as $degree) {
            $sets .= '<set>'
                .$this->tag('setSpec', 'degree:'.$degree)
                .$this->tag('setName', __('theses.degrees.'.$degree, [], 'en'))
                .'</set>';
        }

        return $this->respond($request, '<ListSets>'.$sets.'</ListSets>');
    }

    private function listRecords(Request $request, bool $headersOnly): Response
    {
        $prefix = $request->query('metadataPrefix', 'oai_dc');
        $token = $request->query('resumptionToken');

        // A resumption token carries the whole query, because the protocol
        // forbids the harvester from sending the other arguments with it.
        if ($token) {
            $state = json_decode(base64_decode((string) $token), true);

            if (! is_array($state)) {
                return $this->error($request, 'badResumptionToken', 'That token is not one this repository issued.');
            }

            $prefix = $state['prefix'] ?? 'oai_dc';
            $offset = (int) ($state['offset'] ?? 0);
            $set = $state['set'] ?? null;
            $from = $state['from'] ?? null;
            $until = $state['until'] ?? null;
        } else {
            $offset = 0;
            $set = $request->query('set');
            $from = $request->query('from');
            $until = $request->query('until');
        }

        if ($prefix !== 'oai_dc') {
            return $this->error($request, 'cannotDisseminateFormat', 'This repository offers oai_dc only.');
        }

        $query = Thesis::published()->with('department')->orderBy('id');

        if ($set) {
            if (! str_starts_with((string) $set, 'degree:')) {
                return $this->error($request, 'noRecordsMatch', 'There is no such set.');
            }

            $query->where('degree', substr((string) $set, 7));
        }

        if ($from) {
            $query->where('updated_at', '>=', $from);
        }

        if ($until) {
            $query->where('updated_at', '<=', $until);
        }

        $total = (clone $query)->count();
        $theses = $query->skip($offset)->take(self::PAGE)->get();

        if ($theses->isEmpty()) {
            return $this->error($request, 'noRecordsMatch', 'Nothing here matches that request.');
        }

        $body = '';

        foreach ($theses as $thesis) {
            $body .= $headersOnly
                ? $this->header($thesis)
                : '<record>'.$this->header($thesis).'<metadata>'.$this->dublinCore($thesis).'</metadata></record>';
        }

        $next = $offset + $theses->count();

        if ($next < $total) {
            $body .= '<resumptionToken completeListSize="'.$total.'" cursor="'.$offset.'">'
                .base64_encode((string) json_encode(compact('prefix', 'set', 'from', 'until') + ['offset' => $next]))
                .'</resumptionToken>';
        }

        $element = $headersOnly ? 'ListIdentifiers' : 'ListRecords';

        return $this->respond($request, "<{$element}>{$body}</{$element}>");
    }

    private function getRecord(Request $request): Response
    {
        if ($request->query('metadataPrefix', 'oai_dc') !== 'oai_dc') {
            return $this->error($request, 'cannotDisseminateFormat', 'This repository offers oai_dc only.');
        }

        $id = (string) $request->query('identifier', '');
        $thesis = Thesis::published()->find($this->idFromIdentifier($id));

        if (! $thesis) {
            return $this->error($request, 'idDoesNotExist', 'There is no published record with that identifier.');
        }

        return $this->respond(
            $request,
            '<GetRecord><record>'.$this->header($thesis)
            .'<metadata>'.$this->dublinCore($thesis).'</metadata>'
            .'</record></GetRecord>'
        );
    }

    // ── The record ──────────────────────────────────────────────────────

    /**
     * The identifier a harvester stores and comes back with. Built from the
     * host so that two installations never claim the same record.
     */
    private function identifier(Thesis $thesis): string
    {
        return 'oai:'.parse_url(config('app.url'), PHP_URL_HOST).':thesis/'.$thesis->id;
    }

    private function idFromIdentifier(string $identifier): int
    {
        return (int) (explode('/', $identifier)[1] ?? 0);
    }

    private function header(Thesis $thesis): string
    {
        return '<header>'
            .$this->tag('identifier', $this->identifier($thesis))
            .$this->tag('datestamp', $this->stamp($thesis->updated_at))
            .$this->tag('setSpec', 'degree:'.$thesis->degree)
            .'</header>';
    }

    /**
     * The thesis as Dublin Core, which is the only vocabulary every harvester
     * in the world agrees on.
     */
    private function dublinCore(Thesis $thesis): string
    {
        $fields = [
            ['title', $thesis->title],
            ['title', $thesis->title_en],
            ['creator', $thesis->author],
            ['contributor', $thesis->supervisor],
            ['contributor', $thesis->co_supervisor],
            ['subject', $thesis->department?->translation('en', 'title')],
            ['description', $thesis->abstract_en ?: $thesis->abstract],
            ['publisher', __('messages.university_name', [], 'en')],
            ['date', (string) $thesis->year],
            ['type', 'Text'],
            ['type', __('theses.degrees.'.$thesis->degree, [], 'en').' thesis'],
            ['format', 'application/pdf'],
            ['identifier', Locale::thesisUrl($thesis->id)],
            ['identifier', $thesis->doiUrl()],
            ['language', BookLanguage::locale($thesis->language) ?? $thesis->language],
            ['rights', $thesis->license ? __('theses.licences.'.$thesis->license, [], 'en') : null],
            // An embargo is part of the record: a harvester that knows the
            // file is withheld will not keep asking for it.
            ['rights', $thesis->isUnderEmbargo()
                ? 'Embargoed until '.$thesis->embargo_until->toDateString()
                : null],
        ];

        foreach ($thesis->keywordList() as $keyword) {
            $fields[] = ['subject', $keyword];
        }

        $body = '';

        foreach ($fields as [$name, $value]) {
            if (filled($value)) {
                $body .= $this->tag('dc:'.$name, (string) $value);
            }
        }

        return '<oai_dc:dc xmlns:oai_dc="'.self::NS_OAI_DC.'"'
            .' xmlns:dc="'.self::NS_DC.'"'
            .' xmlns:xsi="'.self::NS_XSI.'"'
            .' xsi:schemaLocation="'.self::NS_OAI_DC.' '.self::XSD_OAI_DC.'">'
            .$body
            .'</oai_dc:dc>';
    }

    // ── The envelope every answer comes in ──────────────────────────────

    private function respond(Request $request, string $body): Response
    {
        return $this->xml($this->envelope($request, $body));
    }

    private function error(Request $request, string $code, string $message): Response
    {
        // The protocol says the request element carries no attributes when
        // the error is a bad verb or a bad argument.
        $bare = in_array($code, ['badVerb', 'badArgument'], true);

        return $this->xml($this->envelope(
            $request,
            '<error code="'.$code.'">'.$this->escape($message).'</error>',
            $bare
        ));
    }

    private function envelope(Request $request, string $body, bool $bareRequest = false): string
    {
        $attributes = '';

        if (! $bareRequest) {
            foreach (['verb', 'identifier', 'metadataPrefix', 'set', 'from', 'until', 'resumptionToken'] as $name) {
                $value = $request->query($name);

                if (is_string($value) && $value !== '') {
                    $attributes .= ' '.$name.'="'.$this->escape($value).'"';
                }
            }
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<OAI-PMH xmlns="'.self::NS_OAI.'"'
            .' xmlns:xsi="'.self::NS_XSI.'"'
            .' xsi:schemaLocation="'.self::NS_OAI.' '.self::XSD_OAI.'">'
            .$this->tag('responseDate', $this->stamp(now()))
            .'<request'.$attributes.'>'.$this->escape(route('oai')).'</request>'
            .$body
            .'</OAI-PMH>';
    }

    private function xml(string $body): Response
    {
        return response($body, 200, ['Content-Type' => 'text/xml; charset=utf-8']);
    }

    private function tag(string $name, string $value): string
    {
        return '<'.$name.'>'.$this->escape($value).'</'.$name.'>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function stamp(\DateTimeInterface $moment): string
    {
        return Carbon::instance($moment)->utc()->format('Y-m-d\TH:i:s\Z');
    }
}
