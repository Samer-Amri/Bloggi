<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mindscms\Entrust\EntrustPermission;
use Nicolaslopezj\Searchable\SearchableTrait;

class Permission extends EntrustPermission
{
    use HasFactory, SearchableTrait;

    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'permissions.name' => 10,
            'permissions.display_name' => 10,
            'permissions.display_name_en' => 10,
            'permissions.description' => 10,
            'permissions.description_en' => 10,
        ],
    ];
    public function parent()
    {
        return $this->hasOne(Permission::class, 'id', 'parent');
    }

    public function children()
    {
        return $this->hasMany(Permission::class, 'parent', 'id');
    }
    public function appearedchildren()
    {
        return $this->hasMany(Permission::class, 'parent', 'id')->where('appear', 1);
    }

    public static function tree($level = 1)
    {
        return static::with(implode('.', array_fill(0, $level, 'children')))
            ->whereParent(0)
            ->whereAppear(1)
            ->whereSidebarLink(1)
            ->orderBy('ordering', 'asc')
            ->get();
    }

    // for supervisor to define which sub menu is belonging to super menu
    public function assign_children()
    {
        return $this->hasMany(Permission::class, 'parent_original', 'id');
    }

    public static function assign_permissions($level = 1)
    {
        return static::with(implode('.', array_fill(0, $level, 'assign_children')))
                     ->whereParentOriginal(0)
                     ->whereAppear(1)
                     ->orderBy('ordering', 'asc')
                     ->get();
    }

    public function display_name ()
    {
        return config('app.locale') == 'ar' ? $this->display_name : $this->display_name_en;
    }

    public function description()
    {
        return config('app.locale') == 'ar' ? $this->description : $this->description_en;
    }

}
