<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Mindscms\Entrust\Traits\EntrustUserWithPermissionsTrait;
use Nicolaslopezj\Searchable\SearchableTrait;

/**
 * User Model
 *
 * Represents a user in the application.
 *
 * Uses:
 * - Notifiable: For notifications.
 * - EntrustUserWithPermissionsTrait: For role and permission management.
 * - SearchableTrait: For search functionality.
 * - HasApiTokens: For API token management.
 * - HasFactory: For model factories.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, EntrustUserWithPermissionsTrait, SearchableTrait, HasApiTokens, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The searchable configuration for the model.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'users.name' => 10,
            'users.username' => 10,
            'users.email' => 10,
            'users.mobile' => 10,
            'users.bio' => 10,
        ],
    ];

    /**
     * Get the broadcast notification channel name for the user.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn()
    {
        return 'App.User.'.$this->id;
    }

    /**
     * Get the posts for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the comments for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the status of the user.
     *
     * @return string
     */
    public function status()
    {
        return $this->status == 1 ? __('Backend/supervisors.active') : __('Backend/supervisors.inactive');
    }

    /**
     * Get the user's profile image URL.
     *
     * @return string
     */
    public function userImage()
    {
        return $this->user_image != '' ? asset('assets/users/'. $this->user_image ) : asset('assets/users/default.jpg');
    }
}
