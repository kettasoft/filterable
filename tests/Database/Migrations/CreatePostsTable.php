<?php

namespace Kettasoft\Filterable\Tests\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('posts', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->text('content')->nullable();
      $table->enum('status', ['active', 'pending', 'stopped']);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('posts');
  }
}
