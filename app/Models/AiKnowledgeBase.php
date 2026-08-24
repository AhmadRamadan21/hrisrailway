<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiKnowledgeBase extends Model
{
    use HasFactory;

    protected $fillable = [
        'topik',
        'keywords',
        'jawaban',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
