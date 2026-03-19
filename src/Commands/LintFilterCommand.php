<?php

namespace Kettasoft\Filterable\Commands;

use ReflectionClass;
use ReflectionMethod;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Commands\Concerns\CommandHelpers;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Cast;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Scope;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Authorize;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute;

class LintFilterCommand extends Command
{
  use CommandHelpers;

  protected $signature = 'filterable:lint
                              {filter? : Filter class name or FQCN to lint (omit to lint all)}
                              {--strict : Exit with non-zero code if any warnings are found}';

  protected $description = 'Analyse Filterable classes for configuration issues and mismatches.';

  // ── Issue severity constants ────────────────────────────────────────────

  private const ERROR   = 'error';
  private const WARNING = 'warning';
  private const INFO    = 'info';

    // ── Internal state ──────────────────────────────────────────────────────

  /** @var array<array{class: string, severity: string, code: string, message: string}> */
  private array $issues = [];

  // ──────────────────────────────────────────────────────────────────────────
  // Entry point
  // ──────────────────────────────────────────────────────────────────────────

  public function handle(): int
  {
    $this->issues = [];

    $filterInput = $this->argument('filter');

    if ($filterInput) {
      $class = $this->resolveFilterClass($filterInput);

      if (!$class) {
        $this->error("❌ Filter class '{$filterInput}' could not be found.");
        return Command::FAILURE;
      }

      $classes = [$class];
    } else {
      $classes = $this->getFilters();

      if (empty($classes)) {
        $this->warn('⚠️  No Filterable classes found to lint.');
        return Command::SUCCESS;
      }
    }

    foreach ($classes as $class) {
      $this->lintClass($class);
    }

    return $this->report();
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Lint one class
  // ──────────────────────────────────────────────────────────────────────────

  private function lintClass(string $class): void
  {
    if (!class_exists($class) || !is_subclass_of($class, Filterable::class)) {
      $this->addIssue($class, self::ERROR, 'L001', "Class does not exist or does not extend Filterable.");
      return;
    }

    // Guard against classes that throw during construction (misconfiguration, missing deps…)
    try {
      $instance = new $class();
    } catch (\Throwable $e) {
      $this->addIssue(
        $class,
        self::ERROR,
        'L001',
        "Failed to instantiate class — " . get_class($e) . ": " . $e->getMessage()
      );
      return;
    }

    $reflection = new ReflectionClass($class);
    $filters    = $instance->getFilterAttributes(); // keys in $filters = []

    $this->checkEmptyFiltersArray($class, $filters);
    $this->checkOrphanedFilterKeys($class, $instance, $reflection, $filters);
    $this->checkMethodsWithoutFilterKey($class, $instance, $reflection, $filters);
    $this->checkAnnotations($class, $instance, $reflection, $filters);
    $this->checkValidationRulesOrphans($class, $instance, $filters);
    $this->checkSanitizerOrphans($class, $instance, $filters);
    $this->checkCoreMethodConflicts($class, $reflection, $filters);
  }

    // ──────────────────────────────────────────────────────────────────────────
    // Individual checks
    // ──────────────────────────────────────────────────────────────────────────

  /**
   * L002 — $filters array is empty.
   */
  private function checkEmptyFiltersArray(string $class, array $filters): void
  {
    if (empty($filters)) {
      $this->addIssue($class, self::WARNING, 'L002', "\$filters array is empty — no methods will be executed.");
    }
  }

  /**
   * L003 — A key in $filters has no corresponding public method in the class.
   * L003b — The method exists but its first parameter is not type-hinted as Payload.
   * L011 — The method is named after the raw key instead of its camelCase equivalent.
   */
  private function checkOrphanedFilterKeys(
    string $class,
    Filterable $instance,
    ReflectionClass $reflection,
    array $filters
  ): void {
    foreach ($filters as $key) {
      $method = $this->resolveMethodName($key);

      // L011 — raw-key method exists instead of its camelCase equivalent
      // e.g. key 'user_id' → expected 'userId()', but 'user_id()' exists
      if ($key !== $method && $reflection->hasMethod($key) && $reflection->getMethod($key)->isPublic()) {
        $this->addIssue(
          $class,
          self::WARNING,
          'L011',
          "Filter key '{$key}' has a method named '{$key}()' but the engine resolves it to '{$method}()'. "
            . "Rename the method to '{$method}()' or the filter will never execute."
        );
        continue;
      }

      // Method missing entirely
      if (!$reflection->hasMethod($method) || !$reflection->getMethod($method)->isPublic()) {
        $this->addIssue(
          $class,
          self::ERROR,
          'L003',
          "Filter key '{$key}' is registered in \$filters but has no corresponding public method '{$method}()'."
        );
        continue;
      }

      // Method exists but first parameter is not Payload
      $reflMethod = $reflection->getMethod($method);
      if (!$this->methodAcceptsPayload($reflMethod)) {
        $this->addIssue(
          $class,
          self::ERROR,
          'L003',
          "Method '{$method}()' is registered for filter key '{$key}' but its first parameter is not type-hinted as Payload."
        );
      }
    }
  }

  /**
   * L004 — A public method accepts Payload but is not reachable from any $filters key.
   *
   * A method named `userId` is reachable from the key `user_id` (via Str::camel).
   * We check both the direct name and any key that would camelCase to this method name.
   */
  private function checkMethodsWithoutFilterKey(
    string $class,
    Filterable $instance,
    ReflectionClass $reflection,
    array $filters
  ): void {
    $coreMethods = $this->getCoreMethods();

    // Pre-compute all method names that $filters keys resolve to
    $resolvedMethods = array_map(fn($key) => $this->resolveMethodName($key), $filters);

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
      // Skip inherited / static / abstract / core
      if ($method->class !== $class) continue;
      if ($method->isStatic() || $method->isAbstract()) continue;
      if (in_array($method->getName(), $coreMethods, true)) continue;

      $name = $method->getName();

      if (!$this->methodAcceptsPayload($method)) continue;

      // Is this method reachable from any key in $filters?
      if (!in_array($name, $resolvedMethods, true)) {
        $this->addIssue(
          $class,
          self::WARNING,
          'L004',
          "Method '{$name}()' accepts a Payload but is not reachable from any key in \$filters — it will never be called."
        );
      }
    }
  }

  /**
   * L005 — Annotation references a class (Cast / Authorize) that does not exist.
   * L006 — Scope annotation references a scope that is not defined on the model.
   * L007 — Annotation is placed on a method not listed in $filters.
   */
  private function checkAnnotations(
    string $class,
    Filterable $instance,
    ReflectionClass $reflection,
    array $filters
  ): void {
    $model = method_exists($instance, 'getModel') ? $instance->getModel() : null;

    // Pre-compute resolved method names from $filters keys
    $resolvedMethods = array_map(fn($key) => $this->resolveMethodName($key), $filters);

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
      if ($method->class !== $class) continue;

      $methodName = $method->getName();

      foreach ($method->getAttributes() as $attr) {
        /** @var string $attrName */
        $attrName = $attr->getName();

        // Only care about MethodAttribute implementations
        if (!is_a($attrName, MethodAttribute::class, true)) continue;

        // L007 — annotation on method not reachable from any $filters key
        if (!in_array($methodName, $resolvedMethods, true)) {
          $this->addIssue(
            $class,
            self::WARNING,
            'L007',
            "Annotation #[{$this->shortName($attrName)}] on '{$methodName}()' has no effect — method is not listed in \$filters."
          );
        }

        $args = $attr->getArguments();

        // L005 — Cast: type class does not exist
        if ($attrName === Cast::class && isset($args[0]) && !$this->isBuiltinCastType($args[0]) && !class_exists($args[0])) {
          $this->addIssue(
            $class,
            self::ERROR,
            'L005',
            "#[Cast('{$args[0]}')] on '{$methodName}()' references a type/class '{$args[0]}' that does not exist."
          );
        }

        // L005 — Authorize: class does not implement Authorizable
        if ($attrName === Authorize::class && isset($args[0])) {
          if (!class_exists($args[0])) {
            $this->addIssue(
              $class,
              self::ERROR,
              'L005',
              "#[Authorize('{$args[0]}')] on '{$methodName}()' references a class that does not exist."
            );
          } elseif (!is_a($args[0], \Kettasoft\Filterable\Contracts\Authorizable::class, true)) {
            $this->addIssue(
              $class,
              self::ERROR,
              'L005',
              "#[Authorize('{$args[0]}')] on '{$methodName}()' — class does not implement the Authorizable contract."
            );
          }
        }

        // L006 — Scope: scope method missing on model
        if ($attrName === Scope::class && isset($args[0]) && $model && class_exists($model)) {
          $scopeMethod = 'scope' . ucfirst($args[0]);
          if (!method_exists($model, $scopeMethod)) {
            $this->addIssue(
              $class,
              self::WARNING,
              'L006',
              "#[Scope('{$args[0]}')] on '{$methodName}()' — scope method '{$scopeMethod}' not found on model '{$model}'."
            );
          }
        }
      }
    }
  }

  /**
   * L008 — rules() defines a key not listed in $filters.
   */
  private function checkValidationRulesOrphans(string $class, Filterable $instance, array $filters): void
  {
    $rules = $instance->rules();

    foreach (array_keys($rules) as $field) {
      // Strip dot-notation (e.g. "title.0" → "title")
      $baseField = explode('.', $field)[0];

      if (!in_array($baseField, $filters, true)) {
        $this->addIssue(
          $class,
          self::WARNING,
          'L008',
          "Validation rule defined for '{$field}' but '{$baseField}' is not in \$filters — rule will never be checked."
        );
      }
    }
  }

  /**
   * L009 — $sanitizers array defines a key not listed in $filters.
   */
  private function checkSanitizerOrphans(string $class, Filterable $instance, array $filters): void
  {
    $sanitizerInstance = method_exists($instance, 'getSanitizerInstance') ? $instance->getSanitizerInstance() : null;

    if (!$sanitizerInstance) return;

    $sanitizerKeys = array_keys($sanitizerInstance->getSanitizers());

    foreach ($sanitizerKeys as $key) {
      if (!in_array($key, $filters, true)) {
        $this->addIssue(
          $class,
          self::WARNING,
          'L009',
          "Sanitizer defined for '{$key}' but it is not in \$filters — sanitizer will never run."
        );
      }
    }
  }

  /**
   * L010 — A $filters key conflicts with a core Filterable method name.
   */
  private function checkCoreMethodConflicts(string $class, ReflectionClass $reflection, array $filters): void
  {
    $coreMethods = $this->getCoreMethods();

    foreach ($filters as $key) {
      $method = $this->resolveMethodName($key);

      if (in_array($method, $coreMethods, true)) {
        $this->addIssue(
          $class,
          self::ERROR,
          'L010',
          "Filter key '{$key}' resolves to method '{$method}()' which conflicts with a core Filterable method."
        );
      }
    }
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Reporting
  // ──────────────────────────────────────────────────────────────────────────

  private function report(): int
  {
    if (empty($this->issues)) {
      $this->info('✅ No issues found — all filters passed lint checks.');
      return Command::SUCCESS;
    }

    // Group by class
    $byClass = [];
    foreach ($this->issues as $issue) {
      $byClass[$issue['class']][] = $issue;
    }

    foreach ($byClass as $class => $classIssues) {
      $this->line('');
      $this->line(" <fg=cyan;options=bold>" . class_basename($class) . "</> <fg=gray>(" . $class . ")</>");
      $this->line(str_repeat('─', 72));

      foreach ($classIssues as $issue) {
        $icon  = $issue['severity'] === self::ERROR   ? '  <fg=red>✖</>'
          : ($issue['severity'] === self::WARNING ? '  <fg=yellow>⚠</>'
            :                                         '  <fg=blue>ℹ</>');

        $color = $issue['severity'] === self::ERROR   ? 'red'
          : ($issue['severity'] === self::WARNING ? 'yellow'
            :                                         'blue');

        $this->line(
          "{$icon} <fg={$color};options=bold>[{$issue['code']}]</> {$issue['message']}"
        );
      }
    }

    $errors   = count(array_filter($this->issues, fn($i) => $i['severity'] === self::ERROR));
    $warnings = count(array_filter($this->issues, fn($i) => $i['severity'] === self::WARNING));

    $this->line('');
    $this->line(sprintf(
      ' <fg=red>%d error%s</>, <fg=yellow>%d warning%s</> found across %d class%s.',
      $errors,
      $errors   === 1 ? '' : 's',
      $warnings,
      $warnings === 1 ? '' : 's',
      count($byClass),
      count($byClass) === 1 ? '' : 'es'
    ));

    // Fail if there are errors, or if --strict and there are warnings
    if ($errors > 0 || ($this->option('strict') && $warnings > 0)) {
      return Command::FAILURE;
    }

    return Command::SUCCESS;
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Helpers
  // ──────────────────────────────────────────────────────────────────────────

  private function addIssue(string $class, string $severity, string $code, string $message): void
  {
    $this->issues[] = compact('class', 'severity', 'code', 'message');
  }

  /**
   * Convert a filter key to its method name — mirrors Invokable engine: Str::camel().
   * e.g. 'user_id' → 'userId', 'status' → 'status'
   */
  private function resolveMethodName(string $key): string
  {
    return Str::camel($key);
  }

  /**
   * Check whether a ReflectionMethod's first parameter type-hints Payload.
   */
  private function methodAcceptsPayload(ReflectionMethod $method): bool
  {
    $params = $method->getParameters();

    if (empty($params)) return false;

    $type = $params[0]->getType();

    if (!$type instanceof \ReflectionNamedType) return false;

    $typeName = $type->getName();

    return $typeName === \Kettasoft\Filterable\Support\Payload::class
      || $typeName === 'Payload';
  }

  /**
   * Get the list of core Filterable public method names to avoid flagging them.
   */
  private function getCoreMethods(): array
  {
    return array_map(
      fn($m) => $m->getName(),
      (new ReflectionClass(Filterable::class))->getMethods(ReflectionMethod::IS_PUBLIC)
    );
  }

  /**
   * Whether the given Cast type is a PHP built-in (int, float, bool, string, array, object).
   */
  private function isBuiltinCastType(string $type): bool
  {
    return in_array(strtolower($type), ['int', 'integer', 'float', 'double', 'bool', 'boolean', 'string', 'array', 'object', 'null'], true);
  }

  /**
   * Return the short class name from a FQCN.
   */
  private function shortName(string $fqcn): string
  {
    return class_basename($fqcn);
  }
}
