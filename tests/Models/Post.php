<?php

namespace Kettasoft\Filterable\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Kettasoft\Filterable\Tests\Models\Tag;
use Kettasoft\Filterable\Traits\HasFilterable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kettasoft\Filterable\Tests\Database\Factories\PostFactory;

class Post extends Model
{
  use HasFactory, HasFilterable;

  protected $fillable = ['title', 'status', 'content', 'views', 'is_featured', 'description', 'tags'];

  protected $casts = [
    'is_featured' => 'boolean',
    'views' => 'integer',
    'tags' => 'array',
  ];

  public function tags(): HasMany
  {
    return $this->hasMany(Tag::class);
  }

  protected static function newFactory(): PostFactory
  {
    return PostFactory::new();
  }
}
