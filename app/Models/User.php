<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

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

    public function userData()
    {
        return $this->hasOne(UsersData::class, 'user_id', 'id');
    }

    public function usersDataAccess()
    {
        return $this->hasMany(UsersDataAccess::class, 'user_id', 'id');
    }
}
