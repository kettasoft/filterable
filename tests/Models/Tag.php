<?php

namespace Kettasoft\Filterable\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kettasoft\Filterable\Tests\Database\Factories\TagFactory;

class Tag extends Model
{
  use HasFactory;

  /**
   * Post fillable.
   * @var array
   */
  protected $fillable = ['name', 'content', 'status'];

  public function post()
  {
    return $this->belongsTo(Post::class);
  }

  protected static function newFactory(): TagFactory
  {
    return TagFactory::new();
  }
}
