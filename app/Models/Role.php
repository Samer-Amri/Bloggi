<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mindscms\Entrust\EntrustRole;
use Nicolaslopezj\Searchable\SearchableTrait;

/**
 * Role Model
 *
 * Represents a role in the application.
 *
 * Uses:
 * - HasFactory: For model factories.
 * - SearchableTrait: For search functionality.
 */
class Role extends EntrustRole
{
    use HasFactory, SearchableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The searchable configuration for the model.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'roles.name' => 10,
            'roles.display_name' => 10,
            'roles.display_name_en' => 10,
            'roles.description' => 10,
            'roles.description_en' => 10,
        ],
    ];

    /**
     * Get the display name of the role based on the application locale.
     *
     * @return string
     */
    public function display_name()
    {
        return config('app.locale') == 'ar' ? $this->display_name : $this->display_name_en;
    }

    /**
     * Get the description of the role based on the application locale.
     *
     * @return string
     */
    public function description()
    {
        return config('app.locale') == 'ar' ? $this->description : $this->description_en;
    }
}
