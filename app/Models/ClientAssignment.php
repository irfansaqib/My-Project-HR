<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientAssignment extends Model
{
    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}