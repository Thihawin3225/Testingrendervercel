<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    /**
     * The notifications table has a created_at column but no updated_at column.
     *
     * @var bool
     */
    public $timestamps = false;
}
