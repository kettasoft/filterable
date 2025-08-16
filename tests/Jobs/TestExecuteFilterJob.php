<?php

namespace Kettasoft\Filterable\Tests\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use YourPackage\Filterable\Invoker;

class TestExecuteFilterJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $invoker;
  public $extra;

  public function __construct(array $data)
  {
    $this->invoker = $data['invoker'];
    $this->extra = $data['extra'] ?? null;
  }

  public function handle()
  {
    //
  }
}
