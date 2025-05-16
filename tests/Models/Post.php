<?php

namespace Kettasoft\Filterable\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Kettasoft\Filterable\Traits\HasFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kettasoft\Filterable\Tests\Database\Factories\PostFactory;

class Post extends Model
{
  use HasFactory, HasFilterable;

  protected $fillable = ['title', 'status', 'content'];

  protected static function newFactory(): PostFactory
  {
    return PostFactory::new();
  }
}
