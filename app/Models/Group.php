<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'owner_id'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}