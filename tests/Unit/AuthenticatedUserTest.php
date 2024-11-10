<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\AuthenticatedUser;
use App\Models\Community;
use App\Models\Image;
use App\Models\Post;
use App\Models\Vote;
use App\Models\Comment;
use App\Models\Supension;
use App\Models\Report;
use App\Models\Notification;
use App\Models\FollowNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticatedUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $user = AuthenticatedUser::factory()->create();
        
        $this->assertEquals([
            'name', 'username', 'email', 'password', 'reputation', 
            'is_suspended', 'creation_date', 'birth_date', 'description', 
            'is_admin', 'image_id'
        ], $user->getFillable());
    }

    /** @test */
    public function it_has_a_custom_creation_date()
    {
        $user = AuthenticatedUser::factory()->create(['creation_date' => now()]);
        
        $this->assertNotNull($user->creation_date);
        $this->assertNull($user->updated_at);
    }

    /** @test */
    public function it_has_an_image_relationship()
    {
        $user = AuthenticatedUser::factory()->create();
        $image = Image::factory()->create(['authenticated_user_id' => $user->id]);
        
        $this->assertTrue($user->image()->exists());
        $this->assertInstanceOf(Image::class, $user->image);
    }

    /** @test */
    public function it_belongs_to_many_communities()
    {
        $user = AuthenticatedUser::factory()->create();
        $community = Community::factory()->create();
        
        $user->communities()->attach($community);
        
        $this->assertTrue($user->communities()->exists());
    }

    /** @test */
    public function it_has_many_followers()
    {
        $user = AuthenticatedUser::factory()->create();
        $follower = AuthenticatedUser::factory()->create();
        
        $follower->follows()->create(['follower_id' => $user->id]);
        
        $this->assertTrue($user->followers()->exists());
    }

    /** @test */
    public function it_has_many_authored_posts_with_pivot()
    {
        $user = AuthenticatedUser::factory()->create();
        $post = Post::factory()->create();
        
        $user->authoredPosts()->attach($post, ['pinned' => true]);
        
        $this->assertTrue($user->authoredPosts()->exists());
        $this->assertEquals(true, $user->authoredPosts()->first()->pivot->pinned);
    }

    /** @test */
    public function it_has_many_favourite_posts()
    {
        $user = AuthenticatedUser::factory()->create();
        $post = Post::factory()->create();
        
        $user->favouritePosts()->attach($post);
        
        $this->assertTrue($user->favouritePosts()->exists());
    }

    /** @test */
    public function it_has_many_votes()
    {
        $user = AuthenticatedUser::factory()->create();
        Vote::factory()->create(['authenticated_user_id' => $user->id]);
        
        $this->assertTrue($user->votes()->exists());
    }

    /** @test */
    public function it_has_many_comments()
    {
        $user = AuthenticatedUser::factory()->create();
        Comment::factory()->create(['authenticated_user_id' => $user->id]);
        
        $this->assertTrue($user->comments()->exists());
    }

    /** @test */
    public function it_has_many_suspensions()
    {
        $user = AuthenticatedUser::factory()->create();
        Supension::factory()->create(['authenticated_user_id' => $user->id]);
        
        $this->assertTrue($user->suspensions()->exists());
    }

    /** @test */
    public function it_has_many_reports()
    {
        $user = AuthenticatedUser::factory()->create();
        Report::factory()->create(['authenticated_user_id' => $user->id]);
        
        $this->assertTrue($user->reports()->exists());
    }

    /** @test */
    public function it_has_many_notifications()
    {
        $user = AuthenticatedUser::factory()->create();
        Notification::factory()->create(['authenticated_user_id' => $user->id]);
        
        $this->assertTrue($user->notifications()->exists());
    }

    /** @test */
    public function it_has_one_follow_user_notification()
    {
        $user = AuthenticatedUser::factory()->create();
        FollowNotification::factory()->create(['authenticated_user_id' => $user->id]);
        
        $this->assertTrue($user->followUserNotification()->exists());
    }
}
