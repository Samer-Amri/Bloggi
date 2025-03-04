<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Setting Model
 *
 * Represents a setting in the application.
 *
 * Uses:
 * - HasFactory: For model factories.
 */
class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the display name of the setting based on the application locale.
     *
     * @return string
     */
    public function display_name()
    {
        return config('app.locale') == 'ar' ? $this->display_name : $this->display_name_en;
    }
}
