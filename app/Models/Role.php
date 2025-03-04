<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mindscms\Entrust\EntrustRole;
use Nicolaslopezj\Searchable\SearchableTrait;

class Role extends EntrustRole
{
    use HasFactory, SearchableTrait;
    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'roles.name' => 10,
            'roles.display_name' => 10,
            'roles.display_name_en' => 10,
            'roles.description' => 10,
            'roles.description_en' => 10,
        ],
    ];

    public function display_name ()
    {
        return config('app.locale') == 'ar' ? $this->display_name : $this->display_name_en;
    }

    public function description()
    {
        return config('app.locale') == 'ar' ? $this->description : $this->description_en;
    }

}
