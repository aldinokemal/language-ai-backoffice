<?php

namespace Database\Seeders;

use App\Models\DB1\SysOrganization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SysOrganization::factory()->create([
            'name' => 'Language AI',
            'address' => 'Yogyakarta',
            'phone' => '-',
            'email' => 'info@language-ai.app',
            'website' => 'https://www.language-ai.app',
        ]);
    }
}
