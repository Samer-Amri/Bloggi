<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

/**
 * Page Model
 *
 * Represents a page in the application.
 *
 * Uses:
 * - HasFactory: For model factories.
 * - Sluggable: For generating slugs.
 * - SearchableTrait: For search functionality.
 */
class Page extends Model
{
    use HasFactory, Sluggable, SearchableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
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
            'posts.title' => 10,
            'posts.title_en' => 10,
            'posts.description' => 10,
            'posts.description_en' => 10,
        ],
    ];

    /**
     * Get the category that the page belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    /**
     * Get the user that the page belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the media associated with the page.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function media()
    {
        return $this->hasMany(PostMedia::class, 'post_id', 'id');
    }

    /**
     * Get the status of the page.
     *
     * @return string
     */
    public function status()
    {
        return $this->status == 1 ? __('Backend/pages.active') : __('Backend/pages.inactive');
    }

    /**
     * Get the title of the page based on the application locale.
     *
     * @return string
     */
    public function title()
    {
        return config('app.locale') == 'ar' ? $this->title : $this->title_en;
    }

    /**
     * Get the URL slug of the page based on the application locale.
     * Note: Using url_slug instead of slug because of conflict with sluggable package.
     *
     * @return string
     */
    public function url_slug()
    {
        return config('app.locale') == 'ar' ? $this->slug : $this->slug_en;
    }

    /**
     * Get the description of the page based on the application locale.
     *
     * @return string
     */
    public function description()
    {
        return config('app.locale') == 'ar' ? $this->description : $this->description_en;
    }
}
