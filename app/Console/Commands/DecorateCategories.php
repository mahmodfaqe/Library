<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class DecorateCategories extends Command
{
    protected $signature = 'categories:decorate';

    protected $description = 'Give each subject an icon and the order it appears in on the home page';

    /**
     * Icon and position for every subject the collection is organised into.
     *
     * The College of Science's own subjects lead, in the order the college
     * lists them; the rest of the library follows.
     *
     * @var array<string, array{0: string, 1: int}>
     */
    private const SUBJECTS = [
        // College of Science
        'بایۆلۆجی' => ['🧬', 1],
        'کیمیا' => ['⚗️', 2],
        'فیزیا' => ['⚛️', 3],
        'زانستی تاقیگەی پزیشکی' => ['🔬', 4],

        // The wider collection
        'پەرستاری' => ['🩺', 5],
        'دەرمانسازی' => ['💊', 6],
        'بابەتی پزیشکی' => ['🏥', 7],
        'فریاگوزاری' => ['🚑', 8],
        'زاراوەزانی' => ['🔤', 9],
        'ڕێگاکانی توێژینەوە' => ['🔎', 10],
        'وەرزشی' => ['🏃', 11],
        'بیرکاری' => ['📐', 12],
        'فێربوونی زمان' => ['🗣️', 13],
        'ئاینی' => ['🕌', 14],
        'هۆنراوە و ئەدەبیات' => ['✍️', 15],
        'ئابووری' => ['📈', 16],
        'ئەندازیاری' => ['⚙️', 17],
        'تەکنەلۆجیای زانیاری' => ['💻', 18],
        'جوگرافیا' => ['🌍', 19],
        'مێژوو' => ['🏛️', 20],
        'پەروەردەیی' => ['🎓', 21],
        'ڕۆمان و چیرۆک' => ['📚', 22],
        'بابەتی جۆراوجۆری تر' => ['🗂️', 23],
    ];

    public function handle(): int
    {
        $matched = $unmatched = 0;

        foreach (Category::all() as $category) {
            [$icon, $order] = $this->lookup($category->name) ?? [null, null];

            if ($icon === null) {
                $this->warn("  no icon for “{$category->name}”");
                $unmatched++;

                continue;
            }

            $category->update(['icon' => $icon, 'sort_order' => $order]);
            $this->line("  {$icon}  {$category->name}");
            $matched++;
        }

        $this->newLine();
        $this->info("Decorated {$matched} subject(s), {$unmatched} unmatched.");

        return self::SUCCESS;
    }

    /**
     * Names arrive from folder titles, so match on a normalised form rather
     * than requiring them to be identical.
     *
     * @return array{0: string, 1: int}|null
     */
    private function lookup(string $name): ?array
    {
        $normalise = fn (string $v) => trim(preg_replace('/[\s.،…]+/u', ' ', $v));
        $needle = $normalise($name);

        foreach (self::SUBJECTS as $subject => $pair) {
            $candidate = $normalise($subject);

            if ($needle === $candidate || str_starts_with($needle, $candidate)) {
                return $pair;
            }
        }

        return null;
    }
}
