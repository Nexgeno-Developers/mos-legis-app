<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamCategory extends Model
{
    //use HasFactory;

    protected $fillable = ['slug', 'name', 'description', 'meta_title', 'meta_description', 'is_active', 'company_id'];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'category_team', 'category_id', 'team_id');
    }
}
