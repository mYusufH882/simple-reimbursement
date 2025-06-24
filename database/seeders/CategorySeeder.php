<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // ==========================================
            // QUOTA-BASED CATEGORIES
            // ==========================================
            [
                'name' => 'Medical',
                'limit_type' => 'quota',
                'limit_value' => 3, // 3 submissions per month
            ],
            [
                'name' => 'Training & Education',
                'limit_type' => 'quota',
                'limit_value' => 2, // 2 submissions per month
            ],
            [
                'name' => 'Team Building',
                'limit_type' => 'quota',
                'limit_value' => 1, // 1 submission per month
            ],
            [
                'name' => 'Equipment & Supplies',
                'limit_type' => 'quota',
                'limit_value' => 5, // 5 submissions per month
            ],

            // ==========================================
            // AMOUNT-BASED CATEGORIES
            // ==========================================
            [
                'name' => 'Transportation',
                'limit_type' => 'amount',
                'limit_value' => 1000000, // 1,000,000 IDR per month
            ],
            [
                'name' => 'Food & Meals',
                'limit_type' => 'amount',
                'limit_value' => 500000, // 500,000 IDR per month
            ],
            [
                'name' => 'Communication',
                'limit_type' => 'amount',
                'limit_value' => 300000, // 300,000 IDR per month
            ],
            [
                'name' => 'Accommodation',
                'limit_type' => 'amount',
                'limit_value' => 2000000, // 2,000,000 IDR per month
            ],
            [
                'name' => 'Office Supplies',
                'limit_type' => 'amount',
                'limit_value' => 750000, // 750,000 IDR per month
            ],
            [
                'name' => 'Entertainment & Client',
                'limit_type' => 'amount',
                'limit_value' => 1500000, // 1,500,000 IDR per month
            ],

            // ==========================================
            // SPECIAL CATEGORIES
            // ==========================================
            [
                'name' => 'Emergency',
                'limit_type' => 'amount',
                'limit_value' => 5000000, // 5,000,000 IDR per month
            ],
            [
                'name' => 'Conference & Seminar',
                'limit_type' => 'quota',
                'limit_value' => 2, // 2 submissions per month
            ],
            [
                'name' => 'Software & Subscriptions',
                'limit_type' => 'amount',
                'limit_value' => 1000000, // 1,000,000 IDR per month
            ],
            [
                'name' => 'Marketing & Promotion',
                'limit_type' => 'amount',
                'limit_value' => 2500000, // 2,500,000 IDR per month
            ],
            [
                'name' => 'Miscellaneous',
                'limit_type' => 'amount',
                'limit_value' => 500000, // 500,000 IDR per month
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('Created ' . Category::count() . ' categories successfully!');

        // Display summary
        $quotaCategories = Category::where('limit_type', 'quota')->count();
        $amountCategories = Category::where('limit_type', 'amount')->count();

        $this->command->info("Categories breakdown:");
        $this->command->info("- Quota-based: {$quotaCategories}");
        $this->command->info("- Amount-based: {$amountCategories}");

        // Display some examples
        $this->command->info("\nExample categories:");
        $examples = Category::take(5)->get(['name', 'limit_type', 'limit_value']);
        foreach ($examples as $example) {
            $limitInfo = $example->limit_type === 'quota'
                ? "{$example->limit_value} submissions/month"
                : "IDR " . number_format($example->limit_value) . "/month";
            $this->command->info("- {$example->name}: {$limitInfo}");
        }
    }
}
