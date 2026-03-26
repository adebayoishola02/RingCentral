<?php

namespace Database\Factories;

use App\Models\RingCentral;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<RingCentral>
 */
class RingCentralFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'company_uuid' => Uuid::uuid4()->toString(),
            'created_by_uuid' => Uuid::uuid4()->toString(),
            'phone_number' => $this->faker->phoneNumber,
            'country_code' => $this->faker->countryCode,
            'status' => 'active',
            'sms_enabled' => true,
            'voice_enabled' => true
        ];
    }
}
