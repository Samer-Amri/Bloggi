<?php

namespace App\Console\Commands;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete posts that have been soft deleted for more than 30 days';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $posts  = Post::onlyTrashed()->where('deleted_at', '<=', Carbon::now()->subDays(30))->get();
        foreach ($posts as $post) {
            $post->forceDelete();
        }
        $this->info('Old soft-deleted posts have been permanently deleted.');
    }
}
