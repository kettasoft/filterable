<?php

namespace Kettasoft\Filterable\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilerTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('profiler', function (Blueprint $table) {
      $table->id();
      $table->text('query');
      $table->integer('execution_time');
      $table->integer('memory_usage');
      $table->string('status')->default('success'); // 'success' or 'error'
      $table->text('error_message')->nullable(); // For storing error messages if any
      $table->string('connection_name')->nullable(); // For multi-connection support
      $table->string('model_class')->nullable(); // For storing the model class if applicable
      $table->string('query_type')->nullable(); // For storing the type of query (e.g., 'select', 'insert', 'update', 'delete')
      $table->string('user_id')->nullable(); // For storing the user ID if applicable
      $table->string('ip_address')->nullable(); // For storing the IP address of the user executing the query
      $table->string('session_id')->nullable(); // For storing the session ID if applicable
      $table->timestamp('executed_at')->useCurrent(); // Timestamp for when the query was executed
      $table->string('environment')->nullable(); // For storing the environment (e.g., 'production', 'development', 'testing')
      $table->string('application_version')->nullable(); // For storing the application version if applicable
      $table->string('query_hash')->nullable(); // For storing a hash of the query for quick lookups
      $table->string('request_method')->nullable(); // For storing the HTTP request method (e.g., 'GET', 'POST')
      $table->string('request_uri')->nullable(); // For storing the request URI
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
    Schema::dropIfExists('profiler');
  }
}
