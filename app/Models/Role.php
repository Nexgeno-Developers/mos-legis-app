<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Columns that are mass assignable
    protected $fillable = ['name', 'guard_name'];

    // public function users()
    // {
    //     return $this->hasMany(User::class);
    // }    
}
