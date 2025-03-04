<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

/**
 * Tag Model
 *
 * Represents a tag in the application.
 *
 * Uses:
 * - HasFactory: For model factories.
 * - Sluggable: For generating slugs.
 * - SearchableTrait: For search functionality.
 */
class Tag extends Model
{
    use HasFactory, Sluggable, SearchableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded =[];

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
            'tags.name' => 10,
            'tags.name_en' => 10,
        ],
    ];

    /**
     * Get the posts associated with the tag.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function posts()
    {
        return $this->belongsToMany(Tag::class, 'posts_tags');
    }

    /**
     * Get the name of the tag based on the application locale.
     *
     * @return string
     */
    public function name()
    {
        return config('app.locale') == 'ar' ? $this->name : $this->name_en;
    }

    /**
     * Get the URL slug of the tag based on the application locale.
     * Note: Using url_slug instead of slug because of conflict with sluggable package.
     *
     * @return string
     */
    public function url_slug()
    {
        return config('app.locale') == 'ar' ? $this->slug : $this->slug_en;
    }

}
