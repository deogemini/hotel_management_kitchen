<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteEntry extends Model
{
    public const CATEGORIES = ['service', 'offer', 'gallery'];

    protected $fillable = ['category', 'image_category', 'title', 'description', 'image_path', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
