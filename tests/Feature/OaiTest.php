<?php

namespace Tests\Feature;

use App\Models\Thesis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OaiTest extends TestCase
{
    use RefreshDatabase;

    private function thesis(array $attributes = []): Thesis
    {
        return Thesis::create(array_merge([
            'title' => 'The effect of solar radiation on plants',
            'author' => 'Sarwar Ahmed Hama',
            'supervisor' => 'Dr. Karzan Omar',
            'degree' => 'master',
            'year' => 2024,
            'status' => Thesis::PUBLISHED,
            'url' => 'https://drive.test/'.uniqid(),
        ], $attributes));
    }

    /**
     * A harvester gives up on malformed XML rather than guessing, so every
     * answer this endpoint gives has to parse.
     */
    private function xml(string $url): \SimpleXMLElement
    {
        $response = $this->get($url)->assertOk();

        $this->assertStringContainsString('text/xml', $response->headers->get('Content-Type'));

        $xml = simplexml_load_string($response->getContent());

        $this->assertNotFalse($xml, "The answer to {$url} is not XML.");

        return $xml;
    }

    public function test_it_identifies_itself(): void
    {
        $xml = $this->xml('/oai?verb=Identify');

        $this->assertSame('2.0', (string) $xml->Identify->protocolVersion);
        $this->assertSame(route('oai'), (string) $xml->Identify->baseURL);
        $this->assertNotEmpty((string) $xml->Identify->repositoryName);
        $this->assertNotEmpty((string) $xml->Identify->earliestDatestamp);
    }

    public function test_it_offers_dublin_core(): void
    {
        // The one vocabulary every harvester in the world must understand.
        $xml = $this->xml('/oai?verb=ListMetadataFormats');

        $this->assertSame('oai_dc', (string) $xml->ListMetadataFormats->metadataFormat->metadataPrefix);
    }

    public function test_a_verb_it_does_not_know_is_a_bad_verb(): void
    {
        // The protocol names its errors, and a harvester reads the code.
        $xml = $this->xml('/oai?verb=Sing');

        $this->assertSame('badVerb', (string) $xml->error['code']);
    }

    public function test_no_verb_at_all_is_a_bad_verb(): void
    {
        $xml = $this->xml('/oai');

        $this->assertSame('badVerb', (string) $xml->error['code']);
    }

    public function test_it_lists_the_records(): void
    {
        $this->thesis();

        $xml = $this->xml('/oai?verb=ListRecords&metadataPrefix=oai_dc');
        $dc = $xml->ListRecords->record->metadata->children('http://www.openarchives.org/OAI/2.0/oai_dc/')->dc
            ->children('http://purl.org/dc/elements/1.1/');

        $this->assertSame('The effect of solar radiation on plants', (string) $dc->title[0]);
        $this->assertSame('Sarwar Ahmed Hama', (string) $dc->creator);
        $this->assertSame('Dr. Karzan Omar', (string) $dc->contributor);
        $this->assertSame('2024', (string) $dc->date);
    }

    public function test_it_lists_identifiers_without_the_metadata(): void
    {
        $this->thesis();

        $xml = $this->xml('/oai?verb=ListIdentifiers&metadataPrefix=oai_dc');

        $this->assertStringContainsString(':thesis/', (string) $xml->ListIdentifiers->header->identifier);
        $this->assertCount(0, $xml->ListIdentifiers->header->xpath('metadata') ?: []);
    }

    public function test_it_returns_one_record_by_its_identifier(): void
    {
        $thesis = $this->thesis();
        $id = (string) $this->xml('/oai?verb=ListIdentifiers&metadataPrefix=oai_dc')
            ->ListIdentifiers->header->identifier;

        $xml = $this->xml('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier='.urlencode($id));

        $this->assertSame($id, (string) $xml->GetRecord->record->header->identifier);
        $this->assertStringContainsString((string) $thesis->id, $id);
    }

    public function test_an_identifier_that_does_not_exist_says_so(): void
    {
        $xml = $this->xml('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai:example.com:thesis/9999');

        $this->assertSame('idDoesNotExist', (string) $xml->error['code']);
    }

    public function test_it_offers_no_format_it_cannot_write(): void
    {
        $this->thesis();

        $xml = $this->xml('/oai?verb=ListRecords&metadataPrefix=marc21');

        $this->assertSame('cannotDisseminateFormat', (string) $xml->error['code']);
    }

    public function test_an_unpublished_thesis_is_not_harvested(): void
    {
        // What is not public on the site is not public to an aggregator that
        // will copy it into a hundred indexes.
        $this->thesis(['status' => Thesis::DRAFT]);

        $xml = $this->xml('/oai?verb=ListRecords&metadataPrefix=oai_dc');

        $this->assertSame('noRecordsMatch', (string) $xml->error['code']);
    }

    public function test_records_are_grouped_into_sets_by_degree(): void
    {
        $this->thesis(['degree' => 'master']);
        $this->thesis(['title' => 'A doctorate', 'author' => 'Someone Else', 'degree' => 'phd']);

        $sets = $this->xml('/oai?verb=ListSets')->ListSets->set;
        $this->assertCount(count(Thesis::DEGREES), $sets);

        $xml = $this->xml('/oai?verb=ListRecords&metadataPrefix=oai_dc&set=degree:phd');
        $this->assertCount(1, $xml->ListRecords->record);
    }

    public function test_a_harvester_can_ask_only_for_what_changed(): void
    {
        // This is how a harvester keeps an index up to date without asking
        // for the whole repository every night.
        $old = $this->thesis();
        $old->updated_at = now()->subYear();
        $old->saveQuietly();

        $this->thesis(['title' => 'Something new', 'author' => 'Recent Person']);

        $xml = $this->xml('/oai?verb=ListRecords&metadataPrefix=oai_dc&from='.now()->subWeek()->toDateString());

        $this->assertCount(1, $xml->ListRecords->record);
    }

    public function test_a_long_list_is_handed_over_a_page_at_a_time(): void
    {
        // A hundred and one records is two requests, and the second one is
        // reached with the token the first hands back.
        foreach (range(1, 101) as $n) {
            $this->thesis(['title' => "Thesis number {$n}", 'author' => "Author {$n}"]);
        }

        $first = $this->xml('/oai?verb=ListRecords&metadataPrefix=oai_dc');
        $this->assertCount(100, $first->ListRecords->record);

        $token = (string) $first->ListRecords->resumptionToken;
        $this->assertNotEmpty($token);
        $this->assertSame('101', (string) $first->ListRecords->resumptionToken['completeListSize']);

        $second = $this->xml('/oai?verb=ListRecords&resumptionToken='.urlencode($token));
        $this->assertCount(1, $second->ListRecords->record);
        // The last page hands back no token: that is how a harvester knows it
        // has everything.
        $this->assertEmpty((string) $second->ListRecords->resumptionToken);
    }

    public function test_a_token_it_did_not_issue_is_refused(): void
    {
        $xml = $this->xml('/oai?verb=ListRecords&resumptionToken=not-a-real-token');

        $this->assertSame('badResumptionToken', (string) $xml->error['code']);
    }

    public function test_an_embargo_is_part_of_the_record(): void
    {
        // A harvester that knows the file is withheld will not keep asking.
        $this->thesis(['embargo_until' => now()->addYear()]);

        $xml = $this->xml('/oai?verb=ListRecords&metadataPrefix=oai_dc');
        $dc = $xml->ListRecords->record->metadata->children('http://www.openarchives.org/OAI/2.0/oai_dc/')->dc
            ->children('http://purl.org/dc/elements/1.1/');

        $rights = array_map('strval', iterator_to_array($dc->rights, false));

        $this->assertNotEmpty(array_filter($rights, fn ($r) => str_contains($r, 'Embargoed')));
    }

    public function test_the_answer_survives_a_title_full_of_xml(): void
    {
        $this->thesis(['title' => 'Ampersands & <angle brackets> in a title']);

        $xml = $this->xml('/oai?verb=ListRecords&metadataPrefix=oai_dc');
        $dc = $xml->ListRecords->record->metadata->children('http://www.openarchives.org/OAI/2.0/oai_dc/')->dc
            ->children('http://purl.org/dc/elements/1.1/');

        $this->assertSame('Ampersands & <angle brackets> in a title', (string) $dc->title[0]);
    }
}
