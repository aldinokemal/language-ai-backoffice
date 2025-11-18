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
            'name' => 'Perumda Tirta Manuntung Balikpapan',
            'address' => 'Graha Tirta Building, Jl. Ruhui Rahayu I, Balikpapan, Kalimantan Timur',
            'phone' => '(0542) 7218831 / 7218832',
            'email' => 'humas@tirtamanuntung.co.id',
            'website' => 'https://www.tirtamanuntung.co.id',
        ]);
    }
}
