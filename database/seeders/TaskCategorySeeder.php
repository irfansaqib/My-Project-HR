<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaskCategory;

class TaskCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Taxation' => [
                'Compliance' => ['Sales Tax', 'Withholding Tax', 'Income Tax', 'Others'],
                'Litigations' => ['Commissioner', 'Commissioner Appeals', 'Tribunal', 'Others'],
                'Registration' => ['NTN', 'Sales Tax - FBR/ICT', 'Sales Tax - Provincial', 'Others'],
            ],
            'Accounting' => [
                'Services' => ['Monthly Bookkeeping', 'Monthly Reports', 'Annual Accounts', 'Others'] 
                // Note: PDF just listed these directly under Accounting, I grouped them under a generic 'Services' container for consistency, or we can make them Level 1 if preferred. 
                // Based on PDF structure: "Accounting" is Level 0.
            ],
            'Audit' => [
                'Services' => ['Annual Audit', 'Internal Audit', 'Special Audit', 'Other Audit']
            ],
            'Corporate' => [
                'Registration' => ['SECP Registration', 'Partnership Registration', 'Other Registration'],
                'Annual Filing' => ['Form A/29', 'Form 45', 'Annual Accounts', 'Other Annual Filings'],
                'Other Filing' => ['Form 21', 'Form 29', 'Form 3, 3A', 'Others', 'Approvals']
            ]
        ];

        foreach ($categories as $main => $subs) {
            // Level 0
            $mainCat = TaskCategory::create(['name' => $main, 'level' => 0]);

            foreach ($subs as $sub1 => $sub2List) {
                // Special check for Accounting/Audit if they don't have explicit Sub-1 in PDF
                // If the key is 'Services', we treat the values as Level 1 directly if needed, 
                // but your PDF implies a 3-layer structure for most. 
                // For this code, I am following the Taxation structure (3 Levels).
                
                // Level 1
                $sub1Cat = TaskCategory::create([
                    'name' => $sub1, 
                    'parent_id' => $mainCat->id, 
                    'level' => 1
                ]);

                // Level 2
                foreach ($sub2List as $sub2) {
                    TaskCategory::create([
                        'name' => $sub2, 
                        'parent_id' => $sub1Cat->id, 
                        'level' => 2
                    ]);
                }
            }
        }
    }
}