<?php

namespace App\Http\Livewire\Backend;

use App\Models\Comment;
use Livewire\Component;
use App\Models\Post;

/**
 * Class LastPostComments
 *
 * Livewire component for displaying the last 5 posts and comments in the backend.
 */
class LastPostComments extends Component
{
    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Retrieve the last 5 posts with their comment count
        $posts = Post::post()->withCount('comments')->orderBy('id', 'desc')->take(5)->get();

        // Retrieve the last 5 comments
        $comments = Comment::orderBy('id', 'desc')->take(5)->get();

        // Return the view with the retrieved posts and comments
        return view('livewire.backend.last-post-comments', [
            'posts' => $posts,
            'comments' => $comments,
        ]);
    }
}
