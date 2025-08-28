<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCredential extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'company_name',
        'user_name',
        'login_id',
        'password',
        'pin',
        'portal_url',
        'address',
        'email',
        'company_email',
        'director_email',
        'director_email_password',
        'ceo_name',
        'ceo_cnic',
        'contact_number',
    ];
}