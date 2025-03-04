<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mindscms\Entrust\EntrustPermission;
use Nicolaslopezj\Searchable\SearchableTrait;

/**
 * Permission Model
 *
 * Represents a permission in the application.
 *
 * Uses:
 * - HasFactory: For model factories.
 * - SearchableTrait: For search functionality.
 */
class Permission extends EntrustPermission
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
            'permissions.name' => 10,
            'permissions.display_name' => 10,
            'permissions.display_name_en' => 10,
            'permissions.description' => 10,
            'permissions.description_en' => 10,
        ],
    ];

    /**
     * Get the parent permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent()
    {
        return $this->hasOne(Permission::class, 'id', 'parent');
    }

    /**
     * Get the child permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Permission::class, 'parent', 'id');
    }

    /**
     * Get the child permissions that are set to appear.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appearedchildren()
    {
        return $this->hasMany(Permission::class, 'parent', 'id')->where('appear', 1);
    }

    /**
     * Get the permission tree.
     *
     * @param int $level
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function tree($level = 1)
    {
        return static::with(implode('.', array_fill(0, $level, 'children')))
                     ->whereParent(0)
                     ->whereAppear(1)
                     ->whereSidebarLink(1)
                     ->orderBy('ordering', 'asc')
                     ->get();
    }

    /**
     * Get the sub menu items for the supervisor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assign_children()
    {
        return $this->hasMany(Permission::class, 'parent_original', 'id');
    }

    /**
     * Get the assigned permissions.
     *
     * @param int $level
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function assign_permissions($level = 1)
    {
        return static::with(implode('.', array_fill(0, $level, 'assign_children')))
                     ->whereParentOriginal(0)
                     ->whereAppear(1)
                     ->orderBy('ordering', 'asc')
                     ->get();
    }

    /**
     * Get the display name of the permission based on the application locale.
     *
     * @return string
     */
    public function display_name ()
    {
        return config('app.locale') == 'ar' ? $this->display_name : $this->display_name_en;
    }

    /**
     * Get the description of the permission based on the application locale.
     *
     * @return string
     */
    public function description()
    {
        return config('app.locale') == 'ar' ? $this->description : $this->description_en;
    }

}
