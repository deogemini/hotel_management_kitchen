<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteEnquiry extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'message', 'delivery_status', 'status'];
}
