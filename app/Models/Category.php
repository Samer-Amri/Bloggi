<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

/**
 * Category Model
 *
 * Represents a category in the application.
 *
 * Uses:
 * - HasFactory: For model factories.
 * - Sluggable: For generating slugs.
 * - SearchableTrait: For search functionality.
 */
class Category extends Model
{
    use HasFactory, Sluggable, SearchableTrait;

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
                'source' => 'name',
            ],
            'slug_en' => [
                'source' => 'name_en',
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
            'categories.name' => 10,
            'categories.slug' => 10,
        ],
    ];

    /**
     * Scope a query to only include active categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get the posts for the category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the status of the category.
     *
     * @return string
     */
    public function status()
    {
        return $this->status == 1 ? __('Backend/pages.active') : __('Backend/pages.inactive');
    }

    /**
     * Get the name of the category based on the application locale.
     *
     * @return string
     */
    public function name()
    {
        return config('app.locale') == 'ar' ? $this->name : $this->name_en;
    }

    /**
     * Get the URL slug of the category based on the application locale.
     *
     * @return string
     */
    public function url_slug()
    {
        return config('app.locale') == 'ar' ? $this->slug : $this->slug_en;
    }
}
