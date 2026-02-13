<?php

namespace Tests\Feature\News;

use App\Models\NewsArticle;
use App\Models\NewsComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_news_index_page_loads(): void
    {
        $response = $this->get(route('news.index'));

        $response->assertOk();
    }

    public function test_news_article_displays_correctly(): void
    {
        $author = User::factory()->create();
        $article = NewsArticle::create([
            'title' => 'Test News Article',
            'slug' => 'test-news-article',
            'content' => 'This is test content',
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('news.show', $article));

        $response->assertOk();
        $response->assertSee('Test News Article');
        $response->assertSee('This is test content');
    }

    public function test_unpublished_article_not_visible(): void
    {
        $author = User::factory()->create();
        $article = NewsArticle::create([
            'title' => 'Draft Article',
            'slug' => 'draft-article',
            'content' => 'Draft content',
            'author_id' => $author->id,
            'status' => 'draft',
        ]);

        $response = $this->get(route('news.show', $article));

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_comment(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();
        $article = NewsArticle::create([
            'title' => 'Article',
            'slug' => 'article',
            'content' => 'Content',
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('news.comment', $article), [
            'body' => 'Great article!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('news_comments', [
            'article_id' => $article->id,
            'user_id' => $user->id,
            'body' => 'Great article!',
        ]);
    }

    public function test_guest_cannot_comment(): void
    {
        $author = User::factory()->create();
        $article = NewsArticle::create([
            'title' => 'Article',
            'slug' => 'article',
            'content' => 'Content',
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->post(route('news.comment', $article), [
            'body' => 'Comment',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_user_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();
        $article = NewsArticle::create([
            'title' => 'Article',
            'slug' => 'article',
            'content' => 'Content',
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $comment = NewsComment::create([
            'article_id' => $article->id,
            'user_id' => $user->id,
            'body' => 'My comment',
        ]);

        $response = $this->actingAs($user)
            ->delete(route('news.comment.destroy', $comment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('news_comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_others_comment(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $author = User::factory()->create();
        $article = NewsArticle::create([
            'title' => 'Article',
            'slug' => 'article',
            'content' => 'Content',
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $comment = NewsComment::create([
            'article_id' => $article->id,
            'user_id' => $user1->id,
            'body' => 'User1 comment',
        ]);

        $response = $this->actingAs($user2)
            ->delete(route('news.comment.destroy', $comment));

        $response->assertStatus(403);
    }

    public function test_user_can_hoorah_article(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();
        $article = NewsArticle::create([
            'title' => 'Article',
            'slug' => 'article',
            'content' => 'Content',
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post(route('news.hoorah', $article));

        $response->assertRedirect();
        $this->assertDatabaseHas('news_hoorahs', [
            'article_id' => $article->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_toggle_hoorah(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();
        $article = NewsArticle::create([
            'title' => 'Article',
            'slug' => 'article',
            'content' => 'Content',
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // First hoorah
        $this->actingAs($user)->post(route('news.hoorah', $article));

        // Second hoorah (toggle off)
        $response = $this->actingAs($user)
            ->post(route('news.hoorah', $article));

        $response->assertRedirect();
        $this->assertDatabaseMissing('news_hoorahs', [
            'article_id' => $article->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_admin_can_create_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.news.store'), [
            'title' => 'New Article',
            'content' => 'Article content',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('news_articles', [
            'title' => 'New Article',
            'author_id' => $admin->id,
        ]);
    }

    public function test_non_admin_cannot_create_article(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post(route('admin.news.store'), [
            'title' => 'New Article',
            'content' => 'Article content',
            'status' => 'published',
        ]);

        $response->assertStatus(403);
    }
}
