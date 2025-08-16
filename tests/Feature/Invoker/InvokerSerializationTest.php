<?php

namespace Kettasoft\Filterable\Tests\Feature\Invoker;

use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class InvokerSerializationTest extends TestCase
{
  public function test_it_can_be_serialized_and_unserialized()
  {
    $builder = Post::query();
    $invoker = new \Kettasoft\Filterable\Foundation\Invoker($builder);

    // Set some callbacks
    $invoker->beforeExecute(function () {
      return 'before';
    });
    $invoker->afterExecute(function () {
      return 'after';
    });
    $invoker->onError(function () {
      return 'error';
    });

    // Serialize the invoker
    $serialized = serialize($invoker);

    // Unserialize the invoker
    $unserializedInvoker = unserialize($serialized);

    // Assert that the unserialized object is an instance of Invoker
    $this->assertInstanceOf(\Kettasoft\Filterable\Foundation\Invoker::class, $unserializedInvoker);
  }
}
