<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxSlab;
use App\Models\Business;

class TaxSlabSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::first();

        if ($business) {
            TaxSlab::where('business_id', $business->id)->delete();

            $slabs = [
                // Tax Year 2025 (July 2024 - June 2025)
                ['year' => 2025, 'from' => 0, 'to' => 600000, 'fixed' => 0, 'rate' => 0],
                ['year' => 2025, 'from' => 600001, 'to' => 1200000, 'fixed' => 0, 'rate' => 5],
                ['year' => 2025, 'from' => 1200001, 'to' => 2200000, 'fixed' => 30000, 'rate' => 15],
                ['year' => 2025, 'from' => 2200001, 'to' => 3200000, 'fixed' => 180000, 'rate' => 25],
                ['year' => 2025, 'from' => 3200001, 'to' => 4100000, 'fixed' => 430000, 'rate' => 35],
                ['year' => 2025, 'from' => 4100001, 'to' => null, 'fixed' => 745000, 'rate' => 35],
            ];

            foreach ($slabs as $slab) {
                TaxSlab::create([
                    'business_id' => $business->id,
                    'tax_year' => $slab['year'],
                    'effective_from_date' => ($slab['year'] - 1) . '-07-01',
                    'effective_to_date' => $slab['year'] . '-06-30',
                    'income_from' => $slab['from'],
                    'income_to' => $slab['to'],
                    'fixed_tax_amount' => $slab['fixed'],
                    'tax_rate_percentage' => $slab['rate'],
                ]);
            }
        }
    }
}