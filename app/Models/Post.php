<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

/**
 * Post Model
 *
 * Represents a post in the application.
 *
 * Uses:
 * - HasFactory: For model factories.
 * - Sluggable: For generating slugs.
 * - SearchableTrait: For search functionality.
 * - SoftDeletes: For soft deleting functionality.
 */
class Post extends Model
{
    use HasFactory, Sluggable, SearchableTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attribute for soft delete timestamp.
     *
     * @var string
     */
    protected $deleted_at = 'deleted_at';

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
            'slug_en' => [
                'source' => 'title_en',
            ],
        ];
    }

    /**
     * The searchable configuration for the model.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'posts.title'       => 10,
            'posts.title_en'    => 10,
        ],
    ];

    /**
     * Scope a query to only include active posts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include posts of type 'post'.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePost($query)
    {
        return $query->where('post_type', 'post');
    }

    /**
     * Get the category that the post belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user that the post belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments for the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the approved comments for the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function approved_comments()
    {
        return $this->hasMany(Comment::class)->whereStatus('1');
    }

    /**
     * Get the media associated with the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function media()
    {
        return $this->hasMany(PostMedia::class);
    }

    /**
     * Get the tags associated with the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'posts_tags');
    }

    /**
     * Get the status of the post.
     *
     * @return string
     */
    public function status()
    {
        return $this->status == 1 ? __('Backend/posts.active') : __('Backend/posts.inactive');
    }

    /**
     * Get the title of the post based on the application locale.
     *
     * @return string
     */
    public function title()
    {
        return config('app.locale') == 'ar' ? $this->title : $this->title_en;
    }

    /**
     * Get the URL slug of the post based on the application locale.
     * Note: Using url_slug instead of slug because of conflict with sluggable package.
     *
     * @return string
     */
    public function url_slug()
    {
        return config('app.locale') == 'ar' ? $this->slug : $this->slug_en;
    }

    /**
     * Get the description of the post based on the application locale.
     *
     * @return string
     */
    public function description()
    {
        return config('app.locale') == 'ar' ? $this->description : $this->description_en;
    }
}
