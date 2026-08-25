<?php

namespace Tests\Feature;

use App\Models\Thesis;
use App\Support\Zenodo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ZenodoDepositTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'library.zenodo.sandbox_token' => 'sandbox-token',
            'library.zenodo.token' => 'live-token',
        ]);

        Storage::fake('books');
    }

    private function thesis(array $attributes = []): Thesis
    {
        Storage::disk('books')->put('thesis.pdf', '%PDF-1.4 pretend');

        return Thesis::create(array_merge([
            'title' => 'کاریگەری تیشکی خۆر',
            'title_en' => 'The effect of solar radiation',
            'author' => 'Sarwar Ahmed Hama',
            'supervisor' => 'Dr. Karzan Omar',
            'degree' => 'master',
            'year' => 2024,
            'defended_on' => '2024-06-15',
            'language' => 'کوردی',
            'keywords' => 'Solar radiation, Plant growth',
            'abstract_en' => 'A study of solar radiation.',
            'license' => 'cc-by',
            'status' => Thesis::PUBLISHED,
            'file_path' => 'thesis.pdf',
        ], $attributes));
    }

    /**
     * The four calls a deposit makes, in the order it makes them.
     */
    private function zenodoAnswers(): void
    {
        Http::fake([
            '*/deposit/depositions' => Http::response([
                'id' => 555,
                'links' => ['bucket' => 'https://sandbox.zenodo.org/api/files/abc-bucket'],
            ]),
            '*/files/abc-bucket/*' => Http::response(['key' => 'thesis.pdf'], 201),
            '*/deposit/depositions/555/actions/publish' => Http::response([
                'doi' => '10.5281/zenodo.555',
                'links' => ['record_html' => 'https://sandbox.zenodo.org/records/555'],
            ]),
            '*/deposit/depositions/555' => Http::response(['id' => 555]),
        ]);
    }

    // ── Nothing happens by accident ─────────────────────────────────────

    public function test_it_sends_nothing_without_being_told_twice(): void
    {
        Http::fake();
        $this->thesis();

        $this->artisan('theses:deposit')
            ->expectsOutputToContain('Nothing was sent')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_the_rehearsal_says_what_would_happen(): void
    {
        Http::fake();
        $this->thesis();

        $this->artisan('theses:deposit')
            ->expectsOutputToContain('would deposit')
            ->assertSuccessful();
    }

    public function test_it_goes_to_the_sandbox_unless_told_otherwise(): void
    {
        // Every first attempt belongs somewhere nothing is permanent.
        $this->zenodoAnswers();
        $this->thesis();

        $this->artisan('theses:deposit --confirm')->assertSuccessful();

        Http::assertSent(fn (Request $request) => str_contains($request->url(), 'sandbox.zenodo.org'));
    }

    public function test_without_a_token_it_does_nothing(): void
    {
        Http::fake();
        config(['library.zenodo.sandbox_token' => '']);
        $this->thesis();

        $this->artisan('theses:deposit --confirm')->assertFailed();

        Http::assertNothingSent();
    }

    // ── What it refuses to deposit ──────────────────────────────────────

    public function test_it_will_not_deposit_a_thesis_with_no_licence(): void
    {
        // Putting a student's work on a public archive for good, under a
        // licence nobody recorded them agreeing to, is not a technical
        // mistake but a rights one — and it cannot be undone.
        $this->zenodoAnswers();
        $this->thesis(['license' => null]);

        $this->artisan('theses:deposit --confirm')
            ->expectsOutputToContain('no licence is recorded')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_it_will_not_deposit_an_all_rights_reserved_thesis(): void
    {
        $this->zenodoAnswers();
        $this->thesis(['license' => 'all-rights-reserved']);

        $this->artisan('theses:deposit --confirm')->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_it_will_not_deposit_an_unpublished_thesis(): void
    {
        $this->zenodoAnswers();
        $this->thesis(['status' => Thesis::DRAFT]);

        $this->artisan('theses:deposit --confirm')
            ->expectsOutputToContain('nothing to deposit')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_it_never_deposits_the_same_thesis_twice(): void
    {
        // A published record cannot be deleted, so a second deposit would
        // leave two permanent records of one piece of work, for good.
        $this->zenodoAnswers();
        $thesis = $this->thesis(['zenodo_id' => 111, 'zenodo_url' => 'https://zenodo.org/records/111']);

        $this->artisan("theses:deposit --thesis={$thesis->id} --confirm")
            ->expectsOutputToContain('already deposited')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_a_thesis_with_no_file_is_skipped(): void
    {
        $this->zenodoAnswers();
        $this->thesis(['file_path' => null, 'url' => null]);

        $this->artisan('theses:deposit --confirm')
            ->expectsOutputToContain('no file')
            ->assertSuccessful();
    }

    // ── A deposit that goes through ─────────────────────────────────────

    public function test_it_deposits_and_keeps_the_doi(): void
    {
        $this->zenodoAnswers();
        $thesis = $this->thesis();

        $this->artisan('theses:deposit --confirm')
            ->expectsOutputToContain('10.5281/zenodo.555')
            ->assertSuccessful();

        $fresh = $thesis->fresh();
        $this->assertSame('10.5281/zenodo.555', $fresh->doi);
        $this->assertSame(555, (int) $fresh->zenodo_id);
        $this->assertSame('https://sandbox.zenodo.org/records/555', $fresh->zenodo_url);
        $this->assertNotNull($fresh->deposited_at);

        // And the DOI is what the thesis is cited by from then on.
        $this->assertSame('https://doi.org/10.5281/zenodo.555', $fresh->permanentUrl());
    }

    public function test_the_deposit_is_recorded_in_the_audit_trail(): void
    {
        $this->zenodoAnswers();
        $this->thesis();

        $this->artisan('theses:deposit --confirm');

        $this->assertDatabaseHas('activity_log', ['action' => 'thesis.deposited']);
    }

    public function test_the_token_is_never_put_in_the_url(): void
    {
        // A query string is written to every log and proxy it passes through.
        $this->zenodoAnswers();
        $this->thesis();

        $this->artisan('theses:deposit --confirm');

        Http::assertSent(function (Request $request) {
            $this->assertStringNotContainsString('sandbox-token', $request->url());

            return true;
        });
    }

    public function test_the_file_is_sent_as_raw_bytes(): void
    {
        // Zenodo's bucket API refuses application/pdf outright. The fakes
        // here would have accepted anything; the sandbox would not, and did
        // not — this is the shape it actually wants.
        $this->zenodoAnswers();
        $this->thesis();

        $this->artisan('theses:deposit --confirm')->assertSuccessful();

        Http::assertSent(function (Request $request) {
            if (! str_contains($request->url(), 'abc-bucket')) {
                return false;
            }

            $this->assertSame(
                'application/octet-stream',
                $request->header('Content-Type')[0] ?? null
            );

            return true;
        });
    }

    public function test_a_draft_that_fails_is_thrown_away(): void
    {
        // A deposit is four calls. A failure in any of the last three leaves
        // an unpublished draft holding a copy of the file, in the account,
        // for good.
        Http::fake([
            '*/deposit/depositions' => Http::response([
                'id' => 555,
                'links' => ['bucket' => 'https://sandbox.zenodo.org/api/files/abc-bucket'],
            ]),
            '*/files/abc-bucket/*' => Http::response(['message' => 'nope'], 415),
            '*/deposit/depositions/555' => Http::response([], 204),
        ]);

        $this->thesis();

        $this->artisan('theses:deposit --confirm')
            ->expectsOutputToContain('failed')
            ->assertSuccessful();

        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE'
            && str_contains($request->url(), 'depositions/555'));
    }

    public function test_a_failure_leaves_the_thesis_alone(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Bad request'], 400)]);
        $thesis = $this->thesis();

        $this->artisan('theses:deposit --confirm')
            ->expectsOutputToContain('failed')
            ->assertSuccessful();

        $this->assertNull($thesis->fresh()->doi);
        $this->assertNull($thesis->fresh()->zenodo_id);
    }

    // ── What Zenodo is told ─────────────────────────────────────────────

    public function test_it_describes_the_thesis_as_a_thesis(): void
    {
        $metadata = Zenodo::metadata($this->thesis());

        $this->assertSame('publication', $metadata['upload_type']);
        $this->assertSame('thesis', $metadata['publication_type']);
        $this->assertSame('The effect of solar radiation', $metadata['title']);
        $this->assertSame('Sarwar Ahmed Hama', $metadata['creators'][0]['name']);
        $this->assertSame('Dr. Karzan Omar', $metadata['thesis_supervisors'][0]['name']);
        $this->assertStringContainsString('Raparin', $metadata['thesis_university']);
        $this->assertSame('2024-06-15', $metadata['publication_date']);
        $this->assertSame(['Solar radiation', 'Plant growth'], $metadata['keywords']);
    }

    public function test_a_kurdish_thesis_is_labelled_as_kurdish(): void
    {
        // Zenodo wants ISO 639-3, and the catalogue stores a word.
        $this->assertSame('ckb', Zenodo::metadata($this->thesis())['language']);
        $this->assertSame('eng', Zenodo::metadata($this->thesis(['language' => 'English']))['language']);
    }

    public function test_the_licence_is_translated_into_zenodos_own(): void
    {
        $this->assertSame('cc-by-4.0', Zenodo::metadata($this->thesis())['license']);
        $this->assertSame('cc-zero', Zenodo::metadata($this->thesis(['license' => 'cc0']))['license']);
    }

    public function test_an_embargo_is_carried_across(): void
    {
        // It means the same thing there as here: the record is public and the
        // file waits.
        $metadata = Zenodo::metadata($this->thesis(['embargo_until' => now()->addYear()]));

        $this->assertSame('embargoed', $metadata['access_right']);
        $this->assertSame(now()->addYear()->toDateString(), $metadata['embargo_date']);
    }

    public function test_without_an_embargo_it_is_open(): void
    {
        $this->assertSame('open', Zenodo::metadata($this->thesis())['access_right']);
    }

    public function test_the_description_leads_back_to_the_library(): void
    {
        // Whoever finds the thesis on Zenodo should be able to find where it
        // came from.
        $thesis = $this->thesis();
        $description = Zenodo::metadata($thesis)['description'];

        $this->assertStringContainsString('A study of solar radiation.', $description);
        $this->assertStringContainsString('/theses/'.$thesis->id, $description);
        $this->assertStringContainsString('Master thesis', $description);
    }

    public function test_an_abstract_full_of_markup_cannot_break_the_page(): void
    {
        $thesis = $this->thesis(['abstract_en' => 'A <script>alert(1)</script> study']);

        $this->assertStringNotContainsString(
            '<script>',
            Zenodo::metadata($thesis)['description']
        );
    }
}
