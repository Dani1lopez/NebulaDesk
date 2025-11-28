<?php

namespace Tests\Unit\Infrastructure\Persistence;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NebulaDesk\Domain\Entities\User;
use NebulaDesk\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Tests\TestCase;

class EloquentUserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_save_and_retrieve_a_user()
    {
        $repository = new EloquentUserRepository();

        $user = new User(
            id: null,
            name: 'Test User',
            email: 'test@example.com',
            password: 'hashed_password',
            organizationId: null,
            role: 'customer'
        );

        $savedUser = $repository->save($user);

        $this->assertNotNull($savedUser->id);
        $this->assertEquals('Test User', $savedUser->name);

        $retrievedUser = $repository->findById($savedUser->id);

        $this->assertNotNull($retrievedUser);
        $this->assertEquals($savedUser->id, $retrievedUser->id);
        $this->assertEquals('test@example.com', $retrievedUser->email);
    }
}
