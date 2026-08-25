<?php

namespace App\Support;

use App\Models\Thesis;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Zenodo
{
    /**
     * Zenodo, run by CERN, issues a real DataCite DOI for nothing.
     *
     * That is the whole reason this exists: a university that cannot yet pay
     * for a Crossref membership can still give its students' work a permanent
     * identifier today, and can move to its own prefix later without any of
     * those identifiers breaking.
     *
     * Two things about it are worth keeping in mind while reading this class.
     *
     * A published record is permanent. Zenodo will not delete one, because a
     * DOI that stops resolving is worse than no DOI at all — so everything
     * here refuses by default and asks to be told twice.
     *
     * And a deposit is a rights decision, not a technical one: it puts a
     * student's thesis on a public archive under a named licence, for good.
     * The command that calls this will not deposit a thesis whose licence
     * nobody has recorded.
     */
    private const LIVE = 'https://zenodo.org/api';

    private const SANDBOX = 'https://sandbox.zenodo.org/api';

    /**
     * The licence identifiers Zenodo knows, against the ones the repository
     * records.
     */
    private const LICENCES = [
        'cc-by' => 'cc-by-4.0',
        'cc-by-sa' => 'cc-by-sa-4.0',
        'cc-by-nc' => 'cc-by-nc-4.0',
        'cc-by-nc-nd' => 'cc-by-nc-nd-4.0',
        'cc0' => 'cc-zero',
    ];

    /**
     * ISO 639-3, which is what Zenodo asks for. The catalogue stores the
     * language as a word, so it goes through the locale first.
     */
    private const LANGUAGES = [
        'ku-sorani' => 'ckb',
        'ar' => 'ara',
        'en' => 'eng',
        'fa' => 'fas',
        'tr' => 'tur',
    ];

    public function __construct(private bool $live = false) {}

    public function base(): string
    {
        return $this->live ? self::LIVE : self::SANDBOX;
    }

    public function isLive(): bool
    {
        return $this->live;
    }

    public function configured(): bool
    {
        return filled($this->token());
    }

    private function token(): ?string
    {
        $token = $this->live
            ? config('library.zenodo.token')
            : config('library.zenodo.sandbox_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    private function request(): PendingRequest
    {
        // The token goes in the header, never in the URL: a query string is
        // written to every log and proxy it passes through.
        return Http::withToken((string) $this->token())
            ->acceptJson()
            ->timeout(120);
    }

    // ── The deposit, one step at a time ─────────────────────────────────

    /**
     * Start a draft. Nothing is public until publish() is called.
     *
     * @return array{id: int, bucket: string}
     */
    public function begin(): array
    {
        $response = $this->request()->post($this->base().'/deposit/depositions', (object) []);

        $this->check($response, 'could not start a deposit');

        return [
            'id' => (int) $response->json('id'),
            'bucket' => (string) $response->json('links.bucket'),
        ];
    }

    /**
     * Put the file in the draft, streamed from disk rather than read into
     * memory: a thesis is a few hundred pages of scanned paper.
     */
    public function attach(string $bucket, string $path, string $filename): void
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException("The file at {$path} could not be opened.");
        }

        try {
            $response = $this->request()
                ->withBody($handle, 'application/pdf')
                ->put($bucket.'/'.rawurlencode($filename));

            $this->check($response, 'could not upload the file');
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    public function describe(int $id, array $metadata): void
    {
        $response = $this->request()->put(
            $this->base().'/deposit/depositions/'.$id,
            ['metadata' => $metadata]
        );

        $this->check($response, 'could not set the metadata');
    }

    /**
     * The irreversible step. After this the record exists for good and its
     * DOI resolves for good.
     *
     * @return array{doi: string, url: string}
     */
    public function publish(int $id): array
    {
        $response = $this->request()->post(
            $this->base().'/deposit/depositions/'.$id.'/actions/publish'
        );

        $this->check($response, 'could not publish the deposit');

        return [
            'doi' => (string) $response->json('doi'),
            'url' => (string) ($response->json('links.record_html') ?: $response->json('links.html')),
        ];
    }

    // ── What Zenodo is told about a thesis ──────────────────────────────

    /**
     * The thesis as Zenodo's metadata.
     *
     * The author's name is passed as it was written. Zenodo would rather have
     * "Family, Given", but deciding which part of a Kurdish or Arabic name is
     * the family name is a guess, and a wrong guess is printed in every
     * bibliography that cites this thesis afterwards.
     */
    public static function metadata(Thesis $thesis): array
    {
        $metadata = [
            'upload_type' => 'publication',
            'publication_type' => 'thesis',
            'title' => $thesis->title_en ?: $thesis->title,
            'creators' => [array_filter([
                'name' => $thesis->author,
                'affiliation' => __('messages.university_name', [], 'en'),
            ])],
            'description' => self::description($thesis),
            'publication_date' => $thesis->defended_on?->toDateString()
                ?: $thesis->year.'-01-01',
            'thesis_university' => __('messages.university_name', [], 'en'),
            'communities' => [],
        ];

        $supervisors = array_filter([$thesis->supervisor, $thesis->co_supervisor]);

        if ($supervisors) {
            $metadata['thesis_supervisors'] = array_map(
                fn ($name) => ['name' => $name, 'affiliation' => __('messages.university_name', [], 'en')],
                array_values($supervisors)
            );
        }

        if ($thesis->keywordList()) {
            $metadata['keywords'] = $thesis->keywordList();
        }

        if ($language = self::language($thesis)) {
            $metadata['language'] = $language;
        }

        // An embargo here means the same thing it means on the site: the
        // record is public and the file waits.
        if ($thesis->isUnderEmbargo()) {
            $metadata['access_right'] = 'embargoed';
            $metadata['embargo_date'] = $thesis->embargo_until->toDateString();
        } else {
            $metadata['access_right'] = 'open';
        }

        $metadata['license'] = self::LICENCES[$thesis->license] ?? null;

        return array_filter($metadata, fn ($value) => $value !== null && $value !== []);
    }

    /**
     * Zenodo shows the description as HTML, so the abstract is escaped and a
     * line back to the repository is added: whoever finds the thesis there
     * should be able to find the library it came from.
     */
    private static function description(Thesis $thesis): string
    {
        $parts = [];

        if ($abstract = ($thesis->abstract_en ?: $thesis->abstract)) {
            $parts[] = '<p>'.nl2br(e($abstract)).'</p>';
        }

        if ($thesis->title_en && $thesis->title_en !== $thesis->title) {
            $parts[] = '<p><em>'.e($thesis->title).'</em></p>';
        }

        $parts[] = '<p>'.e(__('theses.degrees.'.$thesis->degree, [], 'en'))
            .' thesis, '.e(__('messages.university_name', [], 'en')).', '.$thesis->year.'.</p>';

        $parts[] = '<p><a href="'.e(Locale::thesisUrl($thesis->id)).'">'
            .e(__('messages.site_title', [], 'en')).'</a></p>';

        return implode("\n", $parts);
    }

    private static function language(Thesis $thesis): ?string
    {
        $locale = BookLanguage::locale($thesis->language);

        return $locale ? (self::LANGUAGES[$locale] ?? null) : null;
    }

    /**
     * Whether this thesis may be deposited at all, or the reason it may not.
     *
     * Depositing puts a student's work on a public archive for good, under a
     * licence. Doing that without knowing which licence they agreed to is not
     * a technical mistake but a rights one, and it cannot be undone.
     */
    public static function refuse(Thesis $thesis): ?string
    {
        if (! $thesis->isPublished()) {
            return 'it is not published';
        }

        if ($thesis->zenodo_id) {
            return 'it is already deposited as '.$thesis->zenodo_url;
        }

        if (blank($thesis->license) || $thesis->license === 'all-rights-reserved') {
            return 'no licence is recorded, and a deposit is permanent';
        }

        if (! array_key_exists((string) $thesis->license, self::LICENCES)) {
            return "Zenodo does not know the licence [{$thesis->license}]";
        }

        return null;
    }

    private function check(Response $response, string $doing): void
    {
        if ($response->successful()) {
            return;
        }

        $reason = $response->json('message') ?: $response->body();
        $errors = $response->json('errors');

        if (is_array($errors)) {
            foreach ($errors as $error) {
                $reason .= ' — '.($error['field'] ?? '?').': '.($error['message'] ?? '');
            }
        }

        throw new \RuntimeException("Zenodo {$doing} ({$response->status()}): {$reason}");
    }
}
