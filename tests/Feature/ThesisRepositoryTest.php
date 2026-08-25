<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Thesis;
use App\Models\User;
use App\Support\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThesisRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private function thesis(array $attributes = []): Thesis
    {
        return Thesis::create(array_merge([
            'title' => 'کاریگەری تیشکی خۆر لەسەر ڕووەکە کشتوکاڵییەکان',
            'title_en' => 'The effect of solar radiation on agricultural plants',
            'author' => 'Sarwar Ahmed Hama',
            'supervisor' => 'Dr. Karzan Omar',
            'degree' => 'master',
            'year' => 2024,
            'language' => 'کوردی',
            'status' => Thesis::PUBLISHED,
            'url' => 'https://drive.test/'.uniqid(),
        ], $attributes));
    }

    private function staff(): User
    {
        return User::create([
            'name' => 'Library Staff',
            'email' => 'staff@uor.edu.krd',
            'password' => 'correct-horse-battery-staple',
            'role' => User::ROLE_ADMIN,
        ]);
    }

    // ── What the public may and may not see ─────────────────────────────

    public function test_a_published_thesis_has_a_page(): void
    {
        $thesis = $this->thesis();

        $this->get("/theses/{$thesis->id}")
            ->assertOk()
            ->assertSee('Sarwar Ahmed Hama')
            ->assertSee('Dr. Karzan Omar')
            ->assertSee('2024');
    }

    public function test_a_draft_is_nobody_elses_business(): void
    {
        // It is somebody's unfinished work, and it is not published.
        $draft = $this->thesis(['status' => Thesis::DRAFT]);

        $this->get("/theses/{$draft->id}")->assertNotFound();
        $this->get('/theses')->assertOk()->assertDontSee('Sarwar Ahmed Hama');
    }

    public function test_a_thesis_under_review_is_not_public_either(): void
    {
        $waiting = $this->thesis(['status' => Thesis::UNDER_REVIEW]);

        $this->get("/theses/{$waiting->id}")->assertNotFound();
    }

    public function test_a_withdrawn_thesis_stops_being_listed(): void
    {
        $withdrawn = $this->thesis(['status' => Thesis::WITHDRAWN]);

        $this->get("/theses/{$withdrawn->id}")->assertNotFound();
    }

    public function test_the_repository_exists_in_every_language(): void
    {
        $thesis = $this->thesis();

        foreach (Locale::SUPPORTED as $locale) {
            $prefix = $locale === Locale::DEFAULT ? '' : "/{$locale}";

            $this->get("{$prefix}/theses")
                ->assertOk()
                ->assertSee(__('theses.title', [], $locale));

            $this->get("{$prefix}/theses/{$thesis->id}")
                ->assertOk()
                ->assertSee(__('theses.cite', [], $locale));
        }
    }

    public function test_an_english_reader_gets_the_english_title(): void
    {
        // A thesis written in Kurdish is invisible to the world without one.
        $thesis = $this->thesis();

        $this->get("/en/theses/{$thesis->id}")
            ->assertOk()
            ->assertSee('The effect of solar radiation on agricultural plants');

        $this->get("/theses/{$thesis->id}")
            ->assertOk()
            ->assertSee('کاریگەری تیشکی خۆر لەسەر ڕووەکە کشتوکاڵییەکان');
    }

    // ── Embargo ─────────────────────────────────────────────────────────

    public function test_an_embargo_withholds_the_file_and_not_the_record(): void
    {
        // A thesis awaiting a journal's decision still exists, is still
        // citable, and its author still gets the credit.
        $thesis = $this->thesis(['embargo_until' => now()->addYear()]);

        $this->get("/theses/{$thesis->id}")
            ->assertOk()
            ->assertSee('Sarwar Ahmed Hama')
            ->assertSee(__('theses.embargoed'))
            // The link to the file is not offered.
            ->assertDontSee($thesis->url, false);

        // And its citation still works.
        $this->get("/theses/{$thesis->id}/cite.ris")->assertOk();
    }

    public function test_an_embargoed_file_is_refused_even_at_its_own_address(): void
    {
        $thesis = $this->thesis([
            'embargo_until' => now()->addYear(),
            'file_path' => 'somewhere.pdf',
            'url' => null,
        ]);

        $this->get("/theses/{$thesis->id}/download")->assertForbidden();
    }

    public function test_an_embargo_that_has_passed_lets_the_file_through(): void
    {
        $thesis = $this->thesis(['embargo_until' => now()->subDay()]);

        $this->assertFalse($thesis->isUnderEmbargo());
        $this->get("/theses/{$thesis->id}")->assertOk()->assertSee($thesis->url, false);
    }

    // ── The workflow ────────────────────────────────────────────────────

    public function test_staff_can_add_a_thesis(): void
    {
        $department = Department::create([
            'sort_order' => 1,
            'drive_url' => 'https://drive.test/biology',
            'translations' => ['ku-sorani' => ['title' => 'بایۆلۆجی']],
        ]);

        $this->actingAs($this->staff())->post('/admin/theses', [
            'title' => 'توێژینەوەیەکی نوێ',
            'author' => 'Hemin Rasul',
            'degree' => 'phd',
            'year' => 2025,
            'status' => Thesis::PUBLISHED,
            'department_id' => $department->id,
            'url' => 'https://drive.test/new',
        ])->assertRedirect(route('admin.theses'));

        $this->assertDatabaseHas('theses', ['title' => 'توێژینەوەیەکی نوێ', 'degree' => 'phd']);
        $this->assertDatabaseHas('activity_log', ['action' => 'thesis.created']);
    }

    public function test_publishing_records_who_approved_it(): void
    {
        // A repository that cannot say who published a thesis is not one
        // anybody should trust.
        $staff = $this->staff();
        $thesis = $this->thesis(['status' => Thesis::UNDER_REVIEW]);

        $this->actingAs($staff)->put("/admin/theses/{$thesis->id}", [
            'title' => $thesis->title,
            'author' => $thesis->author,
            'degree' => $thesis->degree,
            'year' => $thesis->year,
            'status' => Thesis::PUBLISHED,
            'url' => $thesis->url,
        ])->assertRedirect();

        $fresh = $thesis->fresh();
        $this->assertSame($staff->id, $fresh->approved_by);
        $this->assertNotNull($fresh->approved_at);
    }

    public function test_taking_a_thesis_back_clears_the_approval(): void
    {
        $staff = $this->staff();
        $thesis = $this->thesis(['approved_by' => $staff->id, 'approved_at' => now()]);

        $this->actingAs($staff)->put("/admin/theses/{$thesis->id}", [
            'title' => $thesis->title,
            'author' => $thesis->author,
            'degree' => $thesis->degree,
            'year' => $thesis->year,
            'status' => Thesis::WITHDRAWN,
            'url' => $thesis->url,
        ])->assertRedirect();

        $this->assertNull($thesis->fresh()->approved_by);
    }

    public function test_a_thesis_nobody_can_read_cannot_be_published(): void
    {
        $this->actingAs($this->staff())
            ->from(route('admin.theses.create'))
            ->post('/admin/theses', [
                'title' => 'A thesis with nowhere to go',
                'author' => 'Someone',
                'degree' => 'master',
                'year' => 2025,
                'status' => Thesis::PUBLISHED,
            ])
            ->assertSessionHasErrors('url');

        $this->assertDatabaseCount('theses', 0);
    }

    public function test_a_draft_needs_no_file(): void
    {
        // Work in progress is exactly what a draft is for.
        $this->actingAs($this->staff())->post('/admin/theses', [
            'title' => 'Still being written',
            'author' => 'Someone',
            'degree' => 'master',
            'year' => 2025,
            'status' => Thesis::DRAFT,
        ])->assertRedirect(route('admin.theses'));

        $this->assertDatabaseCount('theses', 1);
    }

    public function test_a_guest_cannot_reach_the_repository_admin(): void
    {
        $this->get('/admin/theses')->assertRedirect(route('admin.login'));
        $this->post('/admin/theses', [])->assertRedirect(route('admin.login'));
    }

    // ── Finding things ──────────────────────────────────────────────────

    public function test_it_searches_titles_authors_and_supervisors(): void
    {
        $this->thesis();
        $this->thesis(['title' => 'Something else', 'author' => 'Other Person', 'supervisor' => null]);

        $this->get('/theses?q=Karzan')->assertOk()->assertSee('Sarwar Ahmed Hama');
        $this->get('/theses?q=solar')->assertOk()->assertSee('Sarwar Ahmed Hama');
        $this->get('/theses?q=Karzan')->assertOk()->assertDontSee('Other Person');
    }

    public function test_it_filters_by_degree_and_year(): void
    {
        $this->thesis(['degree' => 'master', 'year' => 2024]);
        $this->thesis(['title' => 'A doctorate', 'author' => 'Doctoral Student', 'degree' => 'phd', 'year' => 2023]);

        $this->get('/theses?degree=phd')->assertOk()
            ->assertSee('Doctoral Student')->assertDontSee('Sarwar Ahmed Hama');

        $this->get('/theses?year=2024')->assertOk()
            ->assertSee('Sarwar Ahmed Hama')->assertDontSee('Doctoral Student');
    }

    public function test_a_wildcard_is_not_a_wildcard(): void
    {
        $this->thesis(['author' => 'Sarwar']);

        $this->get('/theses?q=Sar%25war')->assertOk()->assertDontSee('Sarwar Ahmed');
    }

    // ── Citing it ───────────────────────────────────────────────────────

    public function test_it_is_cited_as_a_thesis_not_as_a_book(): void
    {
        $thesis = $this->thesis(['degree' => 'phd']);

        $bib = $this->get("/theses/{$thesis->id}/cite.bib")->assertOk()->getContent();
        $ris = $this->get("/theses/{$thesis->id}/cite.ris")->assertOk()->getContent();

        // A reference manager told "book" prints the wrong thing.
        $this->assertStringContainsString('@phdthesis{', $bib);
        $this->assertStringContainsString('school     = {', $bib);
        $this->assertStringStartsWith("TY  - THES\r\n", $ris);
        // The supervisor is a secondary author, which is how "supervised by"
        // is carried.
        $this->assertStringContainsString('A2  - Dr. Karzan Omar', $ris);
    }

    public function test_a_masters_thesis_takes_the_masters_entry_type(): void
    {
        $thesis = $this->thesis(['degree' => 'master']);

        $this->assertStringContainsString(
            '@mastersthesis{',
            $this->get("/theses/{$thesis->id}/cite.bib")->assertOk()->getContent()
        );
    }

    public function test_the_page_tells_scholar_it_is_a_dissertation(): void
    {
        $thesis = $this->thesis();

        $html = $this->get("/en/theses/{$thesis->id}")->assertOk()->getContent();

        $this->assertStringContainsString('name="citation_dissertation_name"', $html);
        $this->assertStringContainsString('"@type":"Thesis"', $html);
    }

    public function test_a_doi_becomes_the_cited_address(): void
    {
        $thesis = $this->thesis(['doi' => '10.5281/zenodo.1234567']);

        $this->assertSame('https://doi.org/10.5281/zenodo.1234567', $thesis->permanentUrl());
    }

    public function test_without_a_doi_its_own_page_is_the_permanent_address(): void
    {
        $thesis = $this->thesis();

        $this->assertSame(Locale::thesisUrl($thesis->id), $thesis->permanentUrl());
    }

    public function test_a_draft_cannot_be_cited(): void
    {
        $draft = $this->thesis(['status' => Thesis::DRAFT]);

        $this->get("/theses/{$draft->id}/cite.bib")->assertNotFound();
    }
}
