<?php

namespace Tests\Unit\Models;

use App\Models\AuthenticatedUser;
use App\Models\Community;
use App\Models\Image;
use App\Models\Post;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_fillable_attribute()
    {
        $user = new AuthenticatedUser();
        $this->assertEquals([
            'name', 'username', 'email', 'password', 'reputation', 
            'issuspended', 'creationdate', 'birthdate', 'description', 'isadmin', 'image_id'
        ], $user->getFillable());
    }

    /** @test */
    public function it_has_a_custom_created_at_column()
    {
        $user = AuthenticatedUser::factory()->create();
        $this->assertNotNull($user->creationdate);
    }

    /** @test */
    public function it_can_belong_to_an_image()
    {
        $image = Image::factory()->create();
        $user = AuthenticatedUser::factory()->create(['image_id' => $image->id]);

        $this->assertInstanceOf(Image::class, $user->image);
        $this->assertEquals($image->id, $user->image->id);
    }

    /** @test */
    public function it_can_belong_to_many_communities()
    {
        $user = AuthenticatedUser::factory()->create();
        $community = Community::factory()->create();
        $user->communities()->attach($community->id);

        $this->assertTrue($user->communities->contains($community));
    }

    /** @test */
    public function it_has_many_followed_users()
    {
        $user1 = AuthenticatedUser::factory()->create();
        $user2 = AuthenticatedUser::factory()->create();

        $user1->follows()->attach($user2);

        $this->assertTrue($user1->follows->contains($user2));
    }

    /** @test */
    public function it_has_many_followers()
    {
        $user1 = AuthenticatedUser::factory()->create();
        $user2 = AuthenticatedUser::factory()->create();

        $user2->followers()->attach($user1);

        $this->assertTrue($user2->followers->contains($user1));
    }

    /** @test */
    public function it_has_authored_posts_with_pinned_pivot()
    {
        $user = AuthenticatedUser::factory()->create();
        $post = Post::factory()->create();

        $user->authoredPosts()->attach($post, ['pinned' => true]);

        $this->assertTrue($user->authoredPosts->contains($post));
        $this->assertTrue($user->authoredPosts->first()->pivot->pinned);
    }

    /** @test */
    public function it_has_favorite_posts()
    {
        $user = AuthenticatedUser::factory()->create();
        $post = Post::factory()->create();

        $user->favouritePosts()->attach($post);

        $this->assertTrue($user->favouritePosts->contains($post));
    }

    /** @test */
    public function it_has_votes()
    {
        $user = AuthenticatedUser::factory()->create();
        $vote = Vote::factory()->create(['authenticateduser_id' => $user->id]);

        $this->assertTrue($user->votes->contains($vote));
    }

    /** @test */
    public function it_has_many_comments()
    {
        $user = AuthenticatedUser::factory()->create();
        $comment = Comment::factory()->create(['authenticateduser_id' => $user->id]);

        $this->assertTrue($user->comments->contains($comment));
    }

    /** @test */
    public function it_has_suspensions()
    {
        $user = AuthenticatedUser::factory()->create();
        $suspension = Suspension::factory()->create(['authenticateduser_id' => $user->id]);

        $this->assertTrue($user->suspensions->contains($suspension));
    }

    /** @test */
    public function it_has_reports()
    {
        $user = AuthenticatedUser::factory()->create();
        $report = Report::factory()->create(['authenticateduser_id' => $user->id]);

        $this->assertTrue($user->reports->contains($report));
    }

    /** @test */
    public function it_has_notifications()
    {
        $user = AuthenticatedUser::factory()->create();
        $notification = Notification::factory()->create(['authenticateduser_id' => $user->id]);

        $this->assertTrue($user->notifications->contains($notification));
    }

    /** @test */
    public function it_has_follow_user_notification()
    {
        $user = AuthenticatedUser::factory()->create();
        $followNotification = FollowNotification::factory()->create(['follower_id' => $user->id]);

        $this->assertInstanceOf(FollowNotification::class, $user->followUserNotification);
    }
}
