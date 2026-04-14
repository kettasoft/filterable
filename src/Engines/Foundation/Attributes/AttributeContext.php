<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes;

use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Support\Payload;

/**
 * The context in which attributes are processed.
 * 
 * @package Kettasoft\Filterable\Engines\Foundation\Attributes
 */
class AttributeContext
{
  /**
   * Create a new attribute context instance.
   *
   * @param Engine $engine
   * @param Payload $payload
   */
  public function __construct(
    public Engine $engine,
    public Payload $payload
  ) {}
}
