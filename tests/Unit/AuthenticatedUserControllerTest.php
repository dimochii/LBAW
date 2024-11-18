<?php

namespace Tests\Feature;

use App\Models\AuthenticatedUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedUserControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_user()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'johndoe@example.com',
            'password' => 'securepassword',
            'birth_date' => '2000-01-01',
            'description' => 'A sample user',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'id', 'name', 'username', 'email', 'birth_date', 'description',
                 ]);

        $this->assertDatabaseHas('authenticated_users', [
            'username' => 'johndoe',
            'email' => 'johndoe@example.com',
        ]);
    }
    /** @test */
    public function it_can_show_a_user()
    {
        $user = AuthenticatedUser::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                ]);
    }
    /** @test */
    public function it_can_update_a_user()
    {
        $user = AuthenticatedUser::factory()->create();

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Jane Doe',
            'username' => 'janedoe',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'name' => 'Jane Doe',
                    'username' => 'janedoe',
                ]);

        $this->assertDatabaseHas('authenticated_users', [
            'id' => $user->id,
            'name' => 'Jane Doe',
            'username' => 'janedoe',
        ]);
    }
    /** @test */
    public function it_can_suspend_a_user()
    {
        $user = AuthenticatedUser::factory()->create(['is_suspended' => false]);

        $response = $this->postJson("/api/users/{$user->id}/suspend");

        $response->assertStatus(200)
                ->assertJson(['message' => 'User suspended successfully']);

        $this->assertDatabaseHas('authenticated_users', [
            'id' => $user->id,
            'is_suspended' => true,
        ]);
    }
    /** @test */
    public function it_can_get_authored_posts_of_a_user()
    {
        $user = AuthenticatedUser::factory()->create();
        $posts = Post::factory()->count(3)->create();
        $user->authoredPosts()->attach($posts);

        $response = $this->getJson("/api/users/{$user->id}/authored-posts");

        $response->assertStatus(200)
                ->assertJsonCount(3);
    }




}
