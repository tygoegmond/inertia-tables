<?php

namespace Egmond\InertiaTables\Tests\Database\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'status',
        'age',
        'salary',
    ];

    protected $casts = [
        'email_verified_at' => 'timestamp',
        'salary' => 'decimal:2',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    protected static function newFactory()
    {
        return \Egmond\InertiaTables\Tests\Database\Factories\UserFactory::new();
    }
}
