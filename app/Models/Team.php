<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    //use HasFactory;

    protected $fillable = ['slug', 'name', 'image', 'designation', 'description', 'is_active', 'company_id'];

    public function categories()
    {
        return $this->belongsToMany(TeamCategory::class, 'category_team', 'team_id', 'category_id');
    }
}
