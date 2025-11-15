<?php

namespace Kettasoft\Filterable\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Kettasoft\Filterable\Traits\HasFilterable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kettasoft\Filterable\Tests\Database\Factories\UserFactory;

class User extends Model
{
    use HasFactory, HasFilterable;

    protected $fillable = ['name', 'email', 'is_blocked', 'platform', 'password'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
