<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Seed the departments table with the 5 science departments
     * and their translations in all 8 site languages.
     */
    public function run(): void
    {
        $departments = [
            [
                'sort_order' => 1,
                'icon' => '🧬',
                'drive_url' => 'https://drive.google.com/drive/folders/1daH4QdXeR7IglIiKeTNzjozlrqaRMXiN',
                'translations' => [
                    'ku-sorani' => [
                        'title' => 'کتێبخانەی بەشی بایۆلۆجی',
                        'desc' => 'پەرتووک و سەرچاوەکانی بواری بایۆلۆجی، بۆماوە، مایکرۆبایۆلۆجی و زیاتر',
                        'button' => 'چونە ناو بەش',
                    ],
                    'en' => [
                        'title' => 'Biology Department Library',
                        'desc' => 'Books and resources in life sciences, genetics, microbiology and more',
                        'button' => 'Enter Department',
                    ],
                    'ar' => [
                        'title' => 'مكتبة قسم علوم الحياة',
                        'desc' => 'الكتب والمصادر في علوم الحياة والوراثة والأحياء الدقيقة والمزيد',
                        'button' => 'دخول القسم',
                    ],
                    'ku-badini' => [
                        'title' => 'بەشێ بایۆلۆجیێ',
                        'desc' => 'پرتوک و چاوکانیێن زانستێن ژیانێ، جینیتیک، مایکرۆبایۆلۆجی و زیاتر',
                        'button' => 'بکەڤن بەشێ',
                    ],
                    'ku-badini-lat' => [
                        'title' => 'Pirtûkxaneya Beşa Biyolojiyê',
                        'desc' => 'Pirtûk û çavkaniyên zanistên jiyanê, genetîk, mîkrobiyolojî û zêdetir',
                        'button' => 'Têkeve Beşê',
                    ],
                    'ku-hawrami' => [
                        'title' => 'کتێبخانەیا بەشی بایۆلۆجی',
                        'desc' => 'کتێب و سەرچاوەکانی زانستی ژیان، ژینیتیک، مایکرۆبایۆلۆجی و زیاتر',
                        'button' => 'بچۆ ناو بەش',
                    ],
                    'fa' => [
                        'title' => 'کتابخانه گروه زیست شناسی',
                        'desc' => 'کتاب‌ها و منابع در زمینه علوم زیستی، ژنتیک و میکروبیولوژی',
                        'button' => 'ورود به بخش',
                    ],
                    'tr' => [
                        'title' => 'Biyoloji Bölümü Kütüphanesi',
                        'desc' => 'Yaşam bilimleri, genetik, mikrobiyoloji ve daha fazlasında kaynaklar',
                        'button' => 'Bölüme Gir',
                    ],
                ],
            ],
            [
                'sort_order' => 2,
                'icon' => '⚗️',
                'drive_url' => 'https://drive.google.com/drive/folders/18Bng-T1WJS7s_WQVKWqsCTFfsdFMFAqh',
                'translations' => [
                    'ku-sorani' => [
                        'title' => 'کتێبخانەی بەشی کیمیا',
                        'desc' => 'سەرچاوەکانی کیمیای ئەندامی، نا ئەندامی، فیزیکی و کیمیای تاقیگەیی',
                        'button' => 'چونە ناو بەش',
                    ],
                    'en' => [
                        'title' => 'Chemistry Department Library',
                        'desc' => 'Resources for organic, inorganic, physical and laboratory chemistry',
                        'button' => 'Enter Department',
                    ],
                    'ar' => [
                        'title' => 'مكتبة قسم الكيمياء',
                        'desc' => 'مصادر الكيمياء العضوية وغير العضوية والفيزيائية والمختبرية',
                        'button' => 'دخول القسم',
                    ],
                    'ku-badini' => [
                        'title' => 'بەشێ کیمیایێ',
                        'desc' => 'چاوکانیێن کیمیایا ئەندامی، نائەندامی، فیزیکی و تاقیگەیی',
                        'button' => 'بکەڤن بەشێ',
                    ],
                    'ku-badini-lat' => [
                        'title' => 'Pirtûkxaneya Beşa Kimyayê',
                        'desc' => 'Çavkaniyên kimyaya organîk, neorganîk, fîzîkî û laboratuwarê',
                        'button' => 'Têkeve Beşê',
                    ],
                    'ku-hawrami' => [
                        'title' => 'کتێبخانەیا بەشی کیمیا',
                        'desc' => 'سەرچاوەکانی کیمیای ئەندامی، نائەندامی، فیزیکی و تاقیگەیی',
                        'button' => 'بچۆ ناو بەش',
                    ],
                    'fa' => [
                        'title' => 'کتابخانه دانشکده شیمی',
                        'desc' => 'منابع شیمی آلی، معدنی، فیزیکی و آزمایشگاهی',
                        'button' => 'ورود به بخش',
                    ],
                    'tr' => [
                        'title' => 'Kimya Bölümü Kütüphanesi',
                        'desc' => 'Organik, inorganik, fiziksel ve laboratuvar kimyası kaynakları',
                        'button' => 'Bölüme Gir',
                    ],
                ],
            ],
            [
                'sort_order' => 3,
                'icon' => '⚛️',
                'drive_url' => 'https://drive.google.com/drive/folders/1BrvmaZTBXwCzPWpp-lFqAxOFXX-NBByW',
                'translations' => [
                    'ku-sorani' => [
                        'title' => 'کتێبخانەی بەشی فیزیا',
                        'desc' => 'پەرتووکەکانی فیزیای کلاسیک، مۆدێرن، کوانتەم و فیزیای تاقیگەیی',
                        'button' => 'چونە ناو بەش',
                    ],
                    'en' => [
                        'title' => 'Physics Department Library',
                        'desc' => 'Books on classical, modern, quantum and experimental physics',
                        'button' => 'Enter Department',
                    ],
                    'ar' => [
                        'title' => 'مكتبة قسم الفيزياء',
                        'desc' => 'كتب الفيزياء الكلاسيكية والحديثة والكمية والتجريبية',
                        'button' => 'دخول القسم',
                    ],
                    'ku-badini' => [
                        'title' => 'بەشێ فیزیایێ',
                        'desc' => 'پرتوکێن فیزیایا کلاسیک، مودێرن، کوانتوم و تاقیگەیی',
                        'button' => 'بکەڤن بەشێ',
                    ],
                    'ku-badini-lat' => [
                        'title' => 'Pirtûkxaneya Beşa Fîzîkê',
                        'desc' => 'Pirtûkên fîzîka klasîk, modern, kuantum û ezmûnî',
                        'button' => 'Têkeve Beşê',
                    ],
                    'ku-hawrami' => [
                        'title' => 'کتێبخانەیا بەشی فیزیکا',
                        'desc' => 'کتێبەکانی فیزیکای کلاسیک، مۆدێرن، کوانتوم و تاقیگەیی',
                        'button' => 'بچۆ ناو بەش',
                    ],
                    'fa' => [
                        'title' => 'کتابخانه دانشکده فیزیک',
                        'desc' => 'کتاب‌های فیزیک کلاسیک، مدرن، کوانتوم و تجربی',
                        'button' => 'ورود به بخش',
                    ],
                    'tr' => [
                        'title' => 'Fizik Bölümü Kütüphanesi',
                        'desc' => 'Klasik, modern, kuantum ve deneysel fizik kitapları',
                        'button' => 'Bölüme Gir',
                    ],
                ],
            ],
            [
                'sort_order' => 4,
                'icon' => '🔬',
                'drive_url' => 'https://drive.google.com/drive/folders/1tR1dwkEy9M4yM3CBiwajDRQ4-lCtp13i',
                'translations' => [
                    'ku-sorani' => [
                        'title' => 'کتێبخانەی بەشی زانستی تاقیگەی پزیشکی',
                        'desc' => 'سەرچاوەکانی شیکردنەوەی کلینیکی، میکرۆبایۆلۆجی پزیشکی و ڕێنماییەکان',
                        'button' => 'چونە ناو بەش',
                    ],
                    'en' => [
                        'title' => 'Medical Laboratory Science Department Library',
                        'desc' => 'Resources for clinical analysis, medical microbiology and guidelines',
                        'button' => 'Enter Department',
                    ],
                    'ar' => [
                        'title' => 'مكتبة قسم علوم المختبرات الطبية',
                        'desc' => 'مصادر التحليل السريري والأحياء الدقيقة الطبية والإرشادات',
                        'button' => 'دخول القسم',
                    ],
                    'ku-badini' => [
                        'title' => 'زانستێن تاقیگەیا پزیشکی',
                        'desc' => 'چاوکانیێن تەهلیلێ کلینیکی، مایکرۆبایۆلۆجیا پزیشکی',
                        'button' => 'بکەڤن بەشێ',
                    ],
                    'ku-badini-lat' => [
                        'title' => 'Pirtûkxaneya Beşa Zanistên Laboratuwara Bijîşkî',
                        'desc' => 'Çavkaniyên analîza klînîkî, mîkrobiyolojiya bijîşkî û rênimayan',
                        'button' => 'Têkeve Beşê',
                    ],
                    'ku-hawrami' => [
                        'title' => 'کتێبخانەیا بەشی زانستی تاقیگەی پزیشکی',
                        'desc' => 'سەرچاوەکانی تەهلیلی کلینیکی، مایکرۆبایۆلۆجی پزیشکی',
                        'button' => 'بچۆ ناو بەش',
                    ],
                    'fa' => [
                        'title' => 'کتابخانه گروه علوم آزمایشگاهی پزشکی',
                        'desc' => 'منابع آنالیز بالینی، میکروبیولوژی پزشکی و راهنماها',
                        'button' => 'ورود به بخش',
                    ],
                    'tr' => [
                        'title' => 'Tıbbi Laboratuvar Bilimleri Bölümü Kütüphanesi',
                        'desc' => 'Klinik analiz, tıbbi mikrobiyoloji ve rehber kaynakları',
                        'button' => 'Bölüme Gir',
                    ],
                ],
            ],
            [
                'sort_order' => 5,
                'icon' => '🏗️',
                'drive_url' => 'https://bio.site/civil.engineer',
                'translations' => [
                    'ku-sorani' => [
                        'title' => 'کتێبخانەی ئەندازیاری شارستانی',
                        'desc' => 'پەرتووکەکانی بواری ئەندازیاری شارستانی',
                        'button' => 'چونە ناو بەش',
                    ],
                    'en' => [
                        'title' => 'Civil Engineering Library',
                        'desc' => 'Books and resources in the field of civil engineering',
                        'button' => 'Enter Department',
                    ],
                    'ar' => [
                        'title' => 'مكتبة الهندسة المدنية',
                        'desc' => 'الكتب والمصادر في مجال الهندسة المدنية',
                        'button' => 'دخول القسم',
                    ],
                    'ku-badini' => [
                        'title' => 'پرتوکخانەیا ئەندازیاریا شاری',
                        'desc' => 'پرتوک و چاوکانیێن بوارێ ئەندازیاریا شاری',
                        'button' => 'بکەڤن بەشێ',
                    ],
                    'ku-badini-lat' => [
                        'title' => 'Kitêbxaneya Endezyariya Sivîl',
                        'desc' => 'Pirtûk û çavkaniyên qada endezyariya sivîl',
                        'button' => 'Têkeve Beşê',
                    ],
                    'ku-hawrami' => [
                        'title' => 'کتێبخانەیا ئەندازیاری شارستانی',
                        'desc' => 'کتێبەکانی بواری ئەندازیاری شارستانی',
                        'button' => 'بچۆ ناو بەش',
                    ],
                    'fa' => [
                        'title' => 'کتابخانه مهندسی عمران',
                        'desc' => 'کتاب‌ها و منابع در حوزه مهندسی عمران',
                        'button' => 'ورود به بخش',
                    ],
                    'tr' => [
                        'title' => 'İnşaat Mühendisliği Kütüphanesi',
                        'desc' => 'İnşaat mühendisliği alanındaki kitaplar ve kaynaklar',
                        'button' => 'Bölüme Gir',
                    ],
                ],
            ],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['sort_order' => $department['sort_order'], 'drive_url' => $department['drive_url']],
                $department
            );
        }
    }
}
