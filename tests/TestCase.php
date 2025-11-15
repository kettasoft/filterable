<?php

namespace Kettasoft\Filterable\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Kettasoft\Filterable\Tests\Database\Migrations\CreateTagsTable;
use Kettasoft\Filterable\Tests\Database\Migrations\CreatePostsTable;
use Kettasoft\Filterable\Tests\Database\Migrations\CreateUsersTable;

class TestCase extends BaseTestCase
{
  public function setUp(): void
  {
    parent::setUp();

    $this->migrate();
  }

  protected function getPackageProviders($app)
  {
    return [\Kettasoft\Filterable\Providers\FilterableServiceProvider::class];
  }

  protected function getEnvironmentSetUp($app)
  {
    $app['config']->set('cache.default', 'array');
    $app['config']->set('database.default', 'testing');
    $app['config']->set('database.connections.testing', [
      'driver' => 'sqlite',
      'database' => ':memory:',
      'prefix' => '',
    ]);
  }

  public function migrate()
  {
    $migrations = [
      CreatePostsTable::class,
      CreateTagsTable::class,
      CreateUsersTable::class
    ];

    foreach ($migrations as $migration) {
      (new $migration)->up();
    }
  }
}
