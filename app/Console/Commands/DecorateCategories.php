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
    /**
     * Name in each locale. Kurdish Sorani is the imported original and lives
     * in the `name` column; the Kurdish variants share it unless they differ,
     * and fall back to it when absent.
     *
     * @var array<string, array<string, string>>
     */
    private const NAMES = [
        'بایۆلۆجی' => ['ku-badini' => 'بایۆلۆژی', 'ku-badini-lat' => 'Biyolojî', 'ku-hawrami' => 'بایۆلۆژی', 'en' => 'Biology', 'ar' => 'علم الأحياء', 'fa' => 'زیست‌شناسی', 'tr' => 'Biyoloji'],
        'کیمیا' => ['ku-badini' => 'کیمیا', 'ku-badini-lat' => 'Kîmya', 'ku-hawrami' => 'کیمیا', 'en' => 'Chemistry', 'ar' => 'الكيمياء', 'fa' => 'شیمی', 'tr' => 'Kimya'],
        'فیزیا' => ['ku-badini' => 'فیزیک', 'ku-badini-lat' => 'Fîzîk', 'ku-hawrami' => 'فیزیا', 'en' => 'Physics', 'ar' => 'الفيزياء', 'fa' => 'فیزیک', 'tr' => 'Fizik'],
        'زانستی تاقیگەی پزیشکی' => ['ku-badini' => 'زانستێن تاقیگەها پزیشکی', 'ku-badini-lat' => 'Zanistên Taqîgeha Pizîşkî', 'ku-hawrami' => 'زانستی تاقیگەی پزیشکی', 'en' => 'Medical Laboratory Science', 'ar' => 'علوم المختبرات الطبية', 'fa' => 'علوم آزمایشگاهی پزشکی', 'tr' => 'Tıbbi Laboratuvar Bilimleri'],
        'پەرستاری' => ['ku-badini' => 'پەرستاری', 'ku-badini-lat' => 'Perestarî', 'ku-hawrami' => 'پەرستاری', 'en' => 'Nursing', 'ar' => 'التمريض', 'fa' => 'پرستاری', 'tr' => 'Hemşirelik'],
        'دەرمانسازی' => ['ku-badini' => 'دەرمانسازی', 'ku-badini-lat' => 'Dermansazî', 'ku-hawrami' => 'دەرمانسازی', 'en' => 'Pharmacy', 'ar' => 'الصيدلة', 'fa' => 'داروسازی', 'tr' => 'Eczacılık'],
        'بابەتی پزیشکی' => ['ku-badini' => 'بابەتێن پزیشکی', 'ku-badini-lat' => 'Babetên Pizîşkî', 'ku-hawrami' => 'بابەتێ پزیشکی', 'en' => 'Medicine', 'ar' => 'مواضيع طبية', 'fa' => 'موضوعات پزشکی', 'tr' => 'Tıp konuları'],
        'فریاگوزاری' => ['ku-badini' => 'هاریکاریا لەز', 'ku-badini-lat' => 'Harîkariya Lez', 'ku-hawrami' => 'فریاگوزاری', 'en' => 'Emergency care', 'ar' => 'الإسعاف', 'fa' => 'فوریت‌های پزشکی', 'tr' => 'Acil yardım'],
        'زاراوەزانی' => ['ku-badini' => 'زاراڤەزانی', 'ku-badini-lat' => 'Zaravezanî', 'ku-hawrami' => 'زاراوەزانی', 'en' => 'Terminology', 'ar' => 'المصطلحات', 'fa' => 'اصطلاح‌شناسی', 'tr' => 'Terminoloji'],
        'ڕێگاکانی توێژینەوە' => ['ku-badini' => 'ڕێکێن ڤەکۆلینێ', 'ku-badini-lat' => 'Rêkên Vekolînê', 'ku-hawrami' => 'ڕاگەکێ وەڵێوەکەردەی', 'en' => 'Research methods', 'ar' => 'مناهج البحث', 'fa' => 'روش تحقیق', 'tr' => 'Araştırma yöntemleri'],
        'وەرزشی' => ['ku-badini' => 'وەرزشی', 'ku-badini-lat' => 'Werzişî', 'ku-hawrami' => 'وەرزشی', 'en' => 'Sport', 'ar' => 'الرياضة', 'fa' => 'ورزش', 'tr' => 'Spor'],
        'بیرکاری' => ['ku-badini' => 'بیرکاری', 'ku-badini-lat' => 'Bîrkarî', 'ku-hawrami' => 'بیرکاری', 'en' => 'Mathematics', 'ar' => 'الرياضيات', 'fa' => 'ریاضیات', 'tr' => 'Matematik'],
        'فێربوونی زمان' => ['ku-badini' => 'فێربوونا زمانی', 'ku-badini-lat' => 'Fêrbûna Zimanî', 'ku-hawrami' => 'فێربیەی زوانی', 'en' => 'Language learning', 'ar' => 'تعلم اللغات', 'fa' => 'آموزش زبان', 'tr' => 'Dil öğrenimi'],
        'ئاینی' => ['ku-badini' => 'ئایینی', 'ku-badini-lat' => 'Ayînî', 'ku-hawrami' => 'ئایینی', 'en' => 'Religion', 'ar' => 'الدين', 'fa' => 'دین', 'tr' => 'Din'],
        'هۆنراوە و ئەدەبیات' => ['ku-badini' => 'هۆزان و ئەدەبیات', 'ku-badini-lat' => 'Hozan û Edebiyat', 'ku-hawrami' => 'هۆرە و ئەدەبیات', 'en' => 'Poetry and literature', 'ar' => 'الشعر والأدب', 'fa' => 'شعر و ادبیات', 'tr' => 'Şiir ve edebiyat'],
        'ئابووری' => ['ku-badini' => 'ئابووری', 'ku-badini-lat' => 'Aborî', 'ku-hawrami' => 'ئابووری', 'en' => 'Economics', 'ar' => 'الاقتصاد', 'fa' => 'اقتصاد', 'tr' => 'Ekonomi'],
        'ئەندازیاری' => ['ku-badini' => 'ئەندازیاری', 'ku-badini-lat' => 'Endazyarî', 'ku-hawrami' => 'ئەندازیاری', 'en' => 'Engineering', 'ar' => 'الهندسة', 'fa' => 'مهندسی', 'tr' => 'Mühendislik'],
        'تەکنەلۆجیای زانیاری' => ['ku-badini' => 'تەکنەلۆژیا زانیاریێ', 'ku-badini-lat' => 'Teknelojiya Zanyariyê', 'ku-hawrami' => 'تەکنەلۆژیای زانیاری', 'en' => 'Information technology', 'ar' => 'تقنية المعلومات', 'fa' => 'فناوری اطلاعات', 'tr' => 'Bilişim teknolojisi'],
        'جوگرافیا' => ['ku-badini' => 'جوگرافیا', 'ku-badini-lat' => 'Cografya', 'ku-hawrami' => 'جوگرافیا', 'en' => 'Geography', 'ar' => 'الجغرافيا', 'fa' => 'جغرافیا', 'tr' => 'Coğrafya'],
        'مێژوو' => ['ku-badini' => 'دیرۆک', 'ku-badini-lat' => 'Dîrok', 'ku-hawrami' => 'مێژوو', 'en' => 'History', 'ar' => 'التاريخ', 'fa' => 'تاریخ', 'tr' => 'Tarih'],
        'پەروەردەیی' => ['ku-badini' => 'پەروەردە', 'ku-badini-lat' => 'Perwerde', 'ku-hawrami' => 'پەروەردەیی', 'en' => 'Education', 'ar' => 'التربية', 'fa' => 'آموزش و پرورش', 'tr' => 'Eğitim'],
        'ڕۆمان و چیرۆک' => ['ku-badini' => 'ڕۆمان و چیرۆک', 'ku-badini-lat' => 'Roman û Çîrok', 'ku-hawrami' => 'ڕۆمان و چیرۆک', 'en' => 'Novels and stories', 'ar' => 'الروايات والقصص', 'fa' => 'رمان و داستان', 'tr' => 'Roman ve öykü'],
        'بابەتی جۆراوجۆری تر' => ['ku-badini' => 'بابەتێن دی یێن جۆراوجۆر', 'ku-badini-lat' => 'Babetên Din ên Cûrbecûr', 'ku-hawrami' => 'بابەتێ جۊراوجۊری تەر', 'en' => 'Other subjects', 'ar' => 'مواضيع متنوعة', 'fa' => 'موضوعات گوناگون', 'tr' => 'Diğer konular'],
    ];

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

            $category->update([
                'icon' => $icon,
                'sort_order' => $order,
                // Never overwrite a name a librarian has already corrected.
                'translations' => array_replace(
                    $this->namesFor($category->name),
                    array_filter($category->translations ?? []),
                ),
            ]);
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

    /**
     * @return array<string, string>
     */
    private function namesFor(string $name): array
    {
        $normalise = fn (string $v) => trim(preg_replace('/[\s.،…]+/u', ' ', $v));
        $needle = $normalise($name);

        foreach (self::NAMES as $subject => $names) {
            if (str_starts_with($needle, $normalise($subject))) {
                return $names;
            }
        }

        return [];
    }
}
