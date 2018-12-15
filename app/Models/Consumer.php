<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumer extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'consumers';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function userType()
    {
        return $this->hasOne(UserType::class, 'id', 'user_type');
    }

    public function usersDataAccess()
    {
        return $this->hasMany(UsersDataAccess::class, 'consumer_id', 'id');
    }
}
