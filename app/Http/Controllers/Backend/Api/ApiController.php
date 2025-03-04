<?php

namespace App\Http\Controllers\Backend\Api;

use App\Http\Controllers\Controller;
use App\Models\{Comment, Post, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class ApiController
 *
 * Controller for handling backend API requests.
 */
class ApiController extends Controller
{
    /**
     * Get comments and posts data for chart visualization.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function comments_chart()
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $posts = Post::wherePostType('post')
                     ->groupBy(DB::raw('Month(created_at)'))
                     ->selectRaw('COUNT(*) as count, Month(created_at) as month')
                     ->pluck('count', 'month');

        $comments = Comment::groupBy(DB::raw('Month(created_at)'))
                           ->selectRaw('COUNT(*) as count, Month(created_at) as month')
                           ->pluck('count', 'month');

        $labels = array_values($months);
        $postValues = [];
        $commentValues = [];
        foreach ($months as $month_number => $month_name) {
            $postValues [] = $posts->get($month_number, 0);
            $commentsValues [] = $comments->get($month_number, 0);
        }

        $chart['labels'] = $labels;
        $chart['datasets'][0]['name'] = 'Comments';
        $chart['datasets'][0]['values'] = $commentsValues;

        $chart['datasets'][1]['name'] = 'Posts';
        $chart['datasets'][1]['values'] = $postValues;

        return response()->json($chart);
    }

    /**
     * Get top users data for chart visualization.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function users_chart()
    {
        $users = User::withCount('posts')
                     ->orderBy('posts_count', 'desc')
                     ->take(3)
                     ->pluck('posts_count', 'name');

        $chart['labels'] = $users->keys()->toArray();
        $chart['datasets']['name'] = 'Top Users';
        $chart['datasets']['values'] = $users->values()->toArray();

        return response()->json($chart);
    }
}
