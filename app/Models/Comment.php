<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

/**
 * Comment Model
 *
 * Represents a comment in the application.
 *
 * Uses:
 * - HasFactory: For model factories.
 * - SearchableTrait: For search functionality.
 */
class Comment extends Model
{
    use HasFactory, SearchableTrait;

    /**
     * The searchable configuration for the model.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'comments.name' => 10,
            'comments.email' => 10,
            'comments.url' => 10,
            'comments.ip_address' => 10,
            'comments.comment' => 10,
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the post that the comment belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user that the comment belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the status of the comment.
     *
     * @return string
     */
    public function status()
    {
        return $this->status == 1 ? __('Backend/post_comments.active') : __('Backend/post_comments.inactive');
    }
}
