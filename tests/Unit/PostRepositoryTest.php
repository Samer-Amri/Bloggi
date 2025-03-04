<?php

namespace Tests\Unit;


use App\Models\Post;
use Database\Factories\PostFactory;
use App\Repositories\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class PostRepositoryTest extends TestCase
{
 use RefreshDatabase;

 protected $postRepository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->postRepository =  new PostRepository();
    }

    public function test_all() {
        Post::factory()->count(5)->create();
        $posts = $this->postRepository->all();
        $this->assertCount(5, $posts);
    }

    public function test_find() {
        $post = Post::factory()->create();
        $foundPost = $this->postRepository->find($post->id);
        $this->assertEquals($post->id, $foundPost->id);
    }

    public function test_create() {
        $data = Post::factory()->make()->toArray();
        $createdPost = $this->postRepository->create($data);
        $this->assertDatabaseHas('posts' , ['id' => $createdPost->id]);
    }

    public function test_update() {
        $post = Post::factory()->create();
        $data = ['title' => 'Updated Title'];
        $updatedPost = $this->postRepository->update($data, $post->id);
        $this->assertEquals('Updated Title', $updatedPost->title);
    }

    public function test_delete() {
        $post = Post::factory()->create();
        $this->postRepository->delete($post->id);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
