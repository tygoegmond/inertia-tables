<?php

namespace Egmond\InertiaTables\Tests\Database\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'views',
        'rating',
        'tags',
        'user_id',
        'category_id',
        'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'rating' => 'decimal:2',
        'published_at' => 'timestamp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    protected static function newFactory()
    {
        return \Egmond\InertiaTables\Tests\Database\Factories\PostFactory::new();
    }
}