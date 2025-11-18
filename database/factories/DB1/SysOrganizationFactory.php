<?php

namespace Database\Factories\DB1;

use App\Enums\StorageSource;
use App\Models\DB1\SysOrganization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DB1\SysOrganization>
 */
class SysOrganizationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SysOrganization::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code'         => Str::random(10),
            'name'         => $this->faker->company,
            'address'      => $this->faker->address,
            'phone'        => $this->faker->phoneNumber,
            'email'        => $this->faker->unique()->safeEmail,
            'website'      => $this->faker->url,
            'logo_path'    => 'images/organizations/default.png',
            'logo_storage' => StorageSource::S3->value,
        ];
    }
}
