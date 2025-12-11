<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCategory extends Model
{
    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(TaskCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(TaskCategory::class, 'parent_id');
    }
    
    // Helper to get full path (e.g. "Taxation > Compliance > Sales Tax")
    public function getFullPathAttribute()
    {
        if ($this->parent) {
            return $this->parent->full_path . ' > ' . $this->name;
        }
        return $this->name;
    }
}