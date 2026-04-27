<?php

namespace Kettasoft\Filterable\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Kettasoft\Filterable\Tests\Models\Tag;
use Kettasoft\Filterable\Traits\InteractsWithFilterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kettasoft\Filterable\Tests\Database\Factories\PostFactory;

class Post extends Model
{
  use HasFactory, InteractsWithFilterable, SoftDeletes;

  protected $fillable = ['title', 'status', 'content', 'views', 'is_featured', 'description', 'tags', 'user_id'];

  protected $casts = [
    'is_featured' => 'boolean',
    'views' => 'integer',
    'tags' => 'array',
  ];

  public function scopeActive(Builder $query, $value = null): Builder
  {
    return $query->where('status', $value ?? 'active');
  }

  public function scopePopular(Builder $query, $minViews = 100): Builder
  {
    return $query->where('views', '>=', $minViews);
  }

  public function tags(): HasMany
  {
    return $this->hasMany(Tag::class);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  protected static function newFactory(): PostFactory
  {
    return PostFactory::new();
  }
}
