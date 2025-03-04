<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Announcement
 *
 * This model represents an announcement and includes functionality for generating slugs,
 * determining the active status, and retrieving localized titles and descriptions.
 *
 * @package App\Models
 */
class Announcement extends Model
{
    use HasFactory, Sluggable;

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
                'source' => 'title',
            ],
            'slug_en' => [
                'source' => 'title_en',
            ],
        ];
    }

    /**
     * Scope a query to only include active announcements.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get the user that owns the announcement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the status of the announcement.
     *
     * @return string
     */
    public function status()
    {
        return $this->status == 1 ? __('Backend/posts.active') : __('Backend/posts.inactive');
    }

    /**
     * Get the title of the announcement based on the current locale.
     *
     * @return string
     */
    public function title()
    {
        return config('app.locale') == 'ar' ? $this->title : $this->title_en;
    }

    /**
     * Get the description of the announcement based on the current locale.
     *
     * @return string
     */
    public function description()
    {
        return config('app.locale') == 'ar' ? $this->description : $this->description_en;
    }

    /**
     * Get the URL slug of the announcement based on the current locale.
     *
     * @return string
     */
    public function url_slug()
    {
        return config('app.locale') == 'ar' ? $this->slug : $this->slug_en;
    }
}
