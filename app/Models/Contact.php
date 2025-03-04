<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

/**
 * Contact Model
 *
 * Represents a contact in the application.
 *
 * Uses:
 * - HasFactory: For model factories.
 * - SearchableTrait: For search functionality.
 */
class Contact extends Model
{
    use HasFactory, SearchableTrait;

    /**
     * The searchable configuration for the model.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'contacts.name' => 10,
            'contacts.email' => 10,
            'contacts.mobile' => 10,
            'contacts.title' => 10,
            'contacts.message' => 10,
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the status of the contact.
     *
     * @return string
     */
    public function status()
    {
        return $this->status == 1 ? __('Backend/contact_us.read') : __('Backend/contact_us.new');
    }
}
