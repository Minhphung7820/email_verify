<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    use HasFactory;

    protected $table = 'oauth_otp_user';
    protected $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'otp_code',
        'expired',
        'created_at',
        'updated_at',
    ];
}
