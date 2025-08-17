# Filterable Profiler

## Overview

The **Filterable Profiler** is a lightweight query observer that runs **only for queries executed via the Filterable layer.**

## ⚙️ Configuration

```php
'profiler' => [
  /*
  |--------------------------------------------------------------------------
  | Enable or Disable Query Profiler
  |--------------------------------------------------------------------------
  | Turn the profiler on or off globally.
  | Example: FILTERABLE_PROFILER_ENABLED=false
  */
  'enabled' => env('FILTERABLE_PROFILER_ENABLED', true),

  /*
  |--------------------------------------------------------------------------
  | Storage Method
  |--------------------------------------------------------------------------
  | Determines how query profiling data will be stored.
  | Options: "log", "database", "none"
  */
  'store' => env('FILTERABLE_PROFILER_STORE', 'log'),

  /*
  |--------------------------------------------------------------------------
  | Minimum Execution Time (ms)
  |--------------------------------------------------------------------------
  | Only queries slower than this threshold will be stored.
  */
  'slow_query_threshold' => env('FILTERABLE_PROFILER_MIN_TIME', 1.0),

  /*
  |--------------------------------------------------------------------------
  | Sampling Percentage
  |--------------------------------------------------------------------------
  | To reduce overhead, profile only X% of requests.
  | Example: FILTERABLE_PROFILER_SAMPLING=10 (10% of calls)
  */
  'sampling' => env('FILTERABLE_PROFILER_SAMPLING', 100),

  /*
  |--------------------------------------------------------------------------
  | Database Table
  |--------------------------------------------------------------------------
  | Table name for storing query profiles when using "database".
  */
  'table' => 'query_profiles',

  /*
  |--------------------------------------------------------------------------
  | Log Channel
  |--------------------------------------------------------------------------
  | Log channel to use when "log" storage is enabled.
  */
  'log_channel' => env('FILTERABLE_PROFILER_LOG_CHANNEL', 'daily'),
],
```

## 🛠 How it Works

-   The profiler **only monitors queries triggered via the Filterable system**.
-   At the end of the request, it stores the captured data according to the configured `store` method (`log` or `database`).
-   Overhead can be controlled via `sampling` and `slow_query_threshold`.

## 📌 Example Usage

Enable the profiler in .env:

```dotenv
FILTERABLE_PROFILER_ENABLED=true
FILTERABLE_PROFILER_STORE=log
FILTERABLE_PROFILER_MIN_TIME=5
FILTERABLE_PROFILER_SAMPLING=50
```

Result:

-   50% of requests are sampled.
-   Only queries slower than 5ms are logged.
-   Data is stored in the log channel.
