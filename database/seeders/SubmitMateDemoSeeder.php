<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SubmitMateDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('users')->updateOrInsert(
                ['email' => 'admin@submitmatebd.test'],
                [
                    'name' => 'Submit Mate Admin',
                    'password' => Hash::make('12345678'),
                    'role' => 'admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('users')->updateOrInsert(
                ['email' => 'student@submitmatebd.test'],
                [
                    'name' => 'Demo Student',
                    'password' => Hash::make('12345678'),
                    'role' => 'student',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $services = [
                [
                    'slug' => 'assignment-guidance',
                    'title' => 'Assignment Guidance',
                    'description' => 'Academic support for assignment planning, structure, formatting, and improvement guidance.',
                    'icon' => '📝',
                ],
                [
                    'slug' => 'research-support',
                    'title' => 'Research Support',
                    'description' => 'Research topic selection, proposal structure, literature review guidance, citation support, and formatting help.',
                    'icon' => '📚',
                ],
                [
                    'slug' => 'presentation-design',
                    'title' => 'Presentation Design',
                    'description' => 'Professional academic PowerPoint presentation design support for students.',
                    'icon' => '📊',
                ],
                [
                    'slug' => 'citation-formatting-help',
                    'title' => 'Citation & Formatting Help',
                    'description' => 'APA, MLA, Harvard, IEEE citation formatting, reference checking, and document formatting support.',
                    'icon' => '📄',
                ],
                [
                    'slug' => 'exam-preparation',
                    'title' => 'Exam Preparation',
                    'description' => 'Study notes, revision sheets, viva preparation, MCQ preparation, and exam support.',
                    'icon' => '🎯',
                ],
            ];

            foreach ($services as $service) {
                DB::table('services')->updateOrInsert(
                    ['slug' => $service['slug']],
                    [
                        'title' => $service['title'],
                        'description' => $service['description'],
                        'icon' => $service['icon'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $servicesFromDb = DB::table('services')
                ->whereIn('slug', collect($services)->pluck('slug')->toArray())
                ->get();

            $packageTemplates = [
                [
                    'tier' => 'basic',
                    'name' => 'Basic',
                    'description' => 'Best for normal support with clear guidance and basic formatting help.',
                    'price' => 500,
                    'turnaround_hours' => 48,
                    'features' => [
                        'Basic academic guidance',
                        'Structure and outline support',
                        'Basic formatting help',
                        'Delivery within 48 hours',
                    ],
                ],
                [
                    'tier' => 'standard',
                    'name' => 'Standard',
                    'description' => 'Best value package for faster support, formatting, citation, and improvement suggestions.',
                    'price' => 1000,
                    'turnaround_hours' => 24,
                    'features' => [
                        'Detailed academic guidance',
                        'Formatting and citation support',
                        'Priority response',
                        'Delivery within 1 day',
                    ],
                ],
                [
                    'tier' => 'express',
                    'name' => 'Express',
                    'description' => 'Urgent support package for tight deadlines and fast academic assistance.',
                    'price' => 1500,
                    'turnaround_hours' => 16,
                    'features' => [
                        'Urgent priority support',
                        'Fast formatting and guidance',
                        'Quick response',
                        'Delivery within 16 hours',
                    ],
                ],
            ];

            $activePackageSlugs = [];

            foreach ($servicesFromDb as $service) {
                foreach ($packageTemplates as $template) {
                    $slug = $service->slug . '-' . $template['tier'];
                    $activePackageSlugs[] = $slug;

                    DB::table('packages')->updateOrInsert(
                        [
                            'slug' => $slug,
                        ],
                        [
                            'service_id' => $service->id,
                            'tier' => $template['tier'],
                            'name' => $template['name'],
                            'description' => $this->servicePackageDescription($service->title, $template['description']),
                            'price' => $this->priceForService($service->slug, $template['tier'], $template['price']),
                            'turnaround_hours' => $template['turnaround_hours'],
                            'delivery_days' => max(1, (int) ceil($template['turnaround_hours'] / 24)),
                            'features' => json_encode($this->serviceFeatures($service->title, $template['features'])),
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            DB::table('packages')
                ->whereNotIn('slug', $activePackageSlugs)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            DB::table('testimonials')->updateOrInsert(
                ['student_name' => 'Arafat Rahman', 'service_name' => 'Presentation Design'],
                [
                    'message' => 'Submit Mate BD helped me organize my presentation in a clean and academic way.',
                    'rating' => 5,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('testimonials')->updateOrInsert(
                ['student_name' => 'Nusrat Jahan', 'service_name' => 'Research Support'],
                [
                    'message' => 'The research guidance was clear, ethical, and very student-friendly.',
                    'rating' => 5,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });
    }

    private function servicePackageDescription(string $serviceTitle, string $baseDescription): string
    {
        return $baseDescription . ' This package is suitable for ' . $serviceTitle . '.';
    }

    private function serviceFeatures(string $serviceTitle, array $baseFeatures): array
    {
        array_unshift($baseFeatures, $serviceTitle . ' support');

        return array_values(array_unique($baseFeatures));
    }

    private function priceForService(string $serviceSlug, string $tier, float $basePrice): float
    {
        $serviceMultiplier = match ($serviceSlug) {
            'presentation-design' => 1.2,
            'research-support' => 1.5,
            'citation-formatting-help' => 0.9,
            'exam-preparation' => 1.0,
            default => 1.0,
        };

        $tierMultiplier = match ($tier) {
            'basic' => 1.0,
            'standard' => 1.7,
            'express' => 2.4,
            default => 1.0,
        };

        return round($basePrice * $serviceMultiplier * $tierMultiplier);
    }
}