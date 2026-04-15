<?php

namespace Kettasoft\Filterable\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kettasoft\Filterable\Tests\Database\Factories\UserFactory;
use Kettasoft\Filterable\Traits\HasFilterable;

class User extends Model
{
    use HasFactory;
    use HasFilterable;

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
