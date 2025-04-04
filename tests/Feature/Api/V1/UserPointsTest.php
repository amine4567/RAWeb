<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPointsTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsApiV1;

    public function testItValidates(): void
    {
        $this->get($this->apiUrl('GetUserCompletionProgress'))
            ->assertJsonValidationErrors([
                'u',
            ]);
    }

    public function testGetUserPointsUnknownUser(): void
    {
        $this->user->RAPoints = 600; // make sure enough points to be ranked
        $this->user->save();

        $this->get($this->apiUrl('GetUserPoints', ['u' => 'nonExistant']))
            ->assertNotFound()
            ->assertJson([]);
    }

    public function testGetUserPointsByName(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->get($this->apiUrl('GetUserPoints', ['u' => $user->User]))
            ->assertSuccessful()
            ->assertJson([
                'Points' => $user->RAPoints,
                'SoftcorePoints' => $user->RASoftcorePoints,
            ]);
    }

    public function testGetUserPointsByUlid(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->get($this->apiUrl('GetUserPoints', ['u' => $user->ulid]))
            ->assertSuccessful()
            ->assertJson([
                'Points' => $user->RAPoints,
                'SoftcorePoints' => $user->RASoftcorePoints,
            ]);
    }
}
