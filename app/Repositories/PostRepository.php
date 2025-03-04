<?php

namespace App\Repositories;

use App\Models\Post;

class PostRepository implements PostRepositoryInterface
{

    public function all()
    {
        return Post::all();
    }

    public function find($id)
    {
       return Post::find($id);
    }

    public function create(array $data)
    {
        return Post::create($data);
    }

    public function update(array $data, $id)
    {
        $post = Post::find($id);
        $post->update($data);
        return $post;
    }

    public function delete($id)
    {
        return Post::destroy($id);
    }

    public function with(array $relations): \Illuminate\Database\Eloquent\Builder
    {
        return Post::with($relations);
    }

}
