<?php

namespace App\Http\Livewire\Backend;

use App\Models\Post;
use App\Models\User;
use Livewire\Component;
use App\Models\Comment;

/**
 * Class Statistics
 *
 * Livewire component for displaying various statistics in the backend.
 */
class Statistics extends Component
{
    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Count all users with the role 'user' and status 1
        $all_users = User::whereHas('roles',  function($query){
            $query->where('name', 'user');
        })->whereStatus(1)->count();

        // Count all active posts
        $active_posts = Post::active()->post()->count();

        // Count all inactive posts
        $inactive_posts = Post::whereStatus(0)->post()->count();

        // Count all active comments
        $active_comments = Comment::whereStatus(1)->count();

        // Return the view with the retrieved statistics
        return view('livewire.backend.statistics', [
            'all_users' => $all_users,
            'active_posts' => $active_posts,
            'inactive_posts' => $inactive_posts,
            'active_comments' => $active_comments,
        ]);
    }
}
