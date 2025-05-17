<?php

namespace Kettasoft\Filterable\HttpIntegration;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class HeaderDrivenEngineSelector
{
  /**
   * Request instance.
   * @var Request
   */
  protected Request $request;

  /**
   * HTTP Header-driven mode config.
   * @var Collection
   */
  protected Collection $config;

  /**
   * HeaderDrivenEngineSelector constructor.
   * @param \Illuminate\Http\Request $request
   */
  public function __construct(Request $request, $config = [])
  {
    $this->request = $request;
    $this->config = collect(empty($config) ? config('filterable.header_driven_mode', []) : $config);
  }

  /**
   * Resolve the HTTP header to engine name.
   * @return string
   */
  public function resolve(): string
  {
    $defaultEngine = $this->config->get('default_engine', config('filterable.default_engine'));

    if (! $this->config->get('enabled', false)) {
      return $defaultEngine;
    }

    $headerValue = $this->getHeaderValue();

    if (! $headerValue) {
      return $defaultEngine;
    }

    $mappedEngine = $this->mapToEngine($headerValue);

    return array_key_exists($mappedEngine, config('filterable.engines')) ? $mappedEngine : $defaultEngine;
  }

  /**
   * Get filter mode name from header.
   * @return array|string|null
   */
  protected function getHeaderValue()
  {
    return $this->request->header(
      $this->config->get('header_name')
    );
  }

  /**
   * Mapping engine name.
   * @param string $headerValue
   */
  protected function mapToEngine(string $headerValue)
  {
    // Check engine map first.
    $engine = $this->config->get('engine_map')[$headerValue] ?? $headerValue;

    $this->validateEngine($engine);

    return $engine;
  }

  /**
   * Validate engine.
   * @param string $engine
   * @return void
   * @throws ValidationException
   */
  protected function validateEngine(string $engine)
  {
    $allowedEngines = $this->config->get('allowed_engines') ?: array_keys(config('filterable.engines'));

    if (in_array($engine, $allowedEngines)) {
      return;
    }

    if ($this->config->get('fallback_strategy', 'error') === 'error') {
      throw ValidationException::withMessages([
        $this->config->get('header_name') => [
          'Invalid filter engine specified. ' . 'Allowed: ' . implode(', ', $this->config->get('allowed_engines', []))
        ]
      ]);
    }
  }
}
