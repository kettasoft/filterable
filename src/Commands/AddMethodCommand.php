<?php

namespace Kettasoft\Filterable\Commands;

use Illuminate\Support\Facades\File;
use Kettasoft\Filterable\Commands\Concerns\CommandHelpers;
use Kettasoft\Filterable\Support\Stub;
use Symfony\Component\Console\Command\Command;

class AddMethodCommand extends \Illuminate\Console\Command
{
  use CommandHelpers;

  protected $signature = 'filterable:add-method
                              {filter : The filter class name or fully qualified class name}
                              {--name= : The method name to add}
                              {--after= : Insert the new method after this existing method name}';

  protected $description = 'Add a new filter method to an existing filter class';

  public function handle(): int
  {
    $filterInput = trim($this->argument('filter'));
    $name        = trim((string) $this->option('name'));
    $after       = $this->option('after') ? trim((string) $this->option('after')) : null;

    // ── Validate --name ──────────────────────────────────────────────────
    if ($name === '') {
      $this->error('❌ The --name option is required.');
      return Command::FAILURE;
    }

    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
      $this->error("❌ Invalid method name: '{$name}'. Only letters, digits, and underscores are allowed, and it must not start with a digit.");
      return Command::FAILURE;
    }

    // ── Resolve filter class ─────────────────────────────────────────────
    $filterClass = $this->resolveFilterClass($filterInput);

    if (!$filterClass) {
      $this->error("❌ Filter class '{$filterInput}' could not be found.");
      return Command::FAILURE;
    }

    // ── Get source file path ─────────────────────────────────────────────
    try {
      $reflection = new \ReflectionClass($filterClass);
      $filePath   = $reflection->getFileName();
    } catch (\ReflectionException $e) {
      $this->error("❌ Could not reflect on class '{$filterClass}': {$e->getMessage()}");
      return Command::FAILURE;
    }

    if (!$filePath || !File::exists($filePath)) {
      $this->error("❌ Source file for '{$filterClass}' not found.");
      return Command::FAILURE;
    }

    $content = File::get($filePath);

    // ── Guard: method already exists ─────────────────────────────────────
    if (preg_match('/\bfunction\s+' . preg_quote($name, '/') . '\s*\(/', $content)) {
      $this->warn("⚠️  Method '{$name}' already exists in '{$filterClass}'. Skipping.");
      return Command::SUCCESS;
    }

    // ── Render method stub ───────────────────────────────────────────────
    Stub::setBasePath(config('filterable.generator.stubs'));
    $newMethod = Stub::create('method.stub', ['NAME' => $name])->render();

    // ── Insert the method ────────────────────────────────────────────────
    if ($after !== null) {
      $result = $this->insertAfterMethod($content, $newMethod, $after);

      if ($result === null) {
        $this->error("❌ Method '{$after}' not found in '{$filterClass}'. Cannot insert after it.");
        return Command::FAILURE;
      }

      $content = $result;
    } else {
      $content = $this->insertBeforeClassEnd($content, $newMethod);
    }

    // ── Update $filters array ────────────────────────────────────────────
    $content = $this->addToFiltersArray($content, $name);

    // ── Write back ───────────────────────────────────────────────────────
    File::put($filePath, $content);

    $this->info("✅ Method '{$name}' added to '{$filterClass}' successfully.");

    if ($after) {
      $this->line("   Inserted after method: {$this->highlight($after, 'cyan')}");
    }

    return Command::SUCCESS;
  }

  /**
   * Insert the new method immediately after the closing brace of $afterMethod.
   * Returns the modified content, or null if $afterMethod was not found.
   */
  protected function insertAfterMethod(string $content, string $newMethod, string $afterMethod): ?string
  {
    // Locate the function declaration for $afterMethod
    if (!preg_match(
      '/\bfunction\s+' . preg_quote($afterMethod, '/') . '\s*\(/m',
      $content,
      $matches,
      PREG_OFFSET_CAPTURE
    )) {
      return null;
    }

    $funcStart = $matches[0][1];

    // Walk forward from the opening brace of the method body to find its closing brace
    $openBracePos = strpos($content, '{', $funcStart);

    if ($openBracePos === false) {
      return null;
    }

    $depth       = 0;
    $closingPos  = null;
    $length      = strlen($content);

    for ($i = $openBracePos; $i < $length; $i++) {
      if ($content[$i] === '{') {
        $depth++;
      } elseif ($content[$i] === '}') {
        $depth--;
        if ($depth === 0) {
          $closingPos = $i;
          break;
        }
      }
    }

    if ($closingPos === null) {
      return null;
    }

    // Insert the new method after the closing brace (+ newline)
    $insertAt = $closingPos + 1;

    return substr($content, 0, $insertAt)
      . "\n\n"
      . $newMethod
      . substr($content, $insertAt);
  }

  /**
   * Insert the new method just before the final closing brace of the class.
   */
  protected function insertBeforeClassEnd(string $content, string $newMethod): string
  {
    $lastBrace = strrpos($content, '}');

    if ($lastBrace === false) {
      return $content . "\n" . $newMethod . "\n";
    }

    return substr($content, 0, $lastBrace)
      . $newMethod
      . "\n"
      . substr($content, $lastBrace)
      . "\n";
  }

  /**
   * Add the method name to the $filters array in the class source if not already present.
   */
  protected function addToFiltersArray(string $content, string $name): string
  {
    // Match: protected $filters = [...];
    // Handles empty array, single-line, or multi-line formats.
    if (!preg_match('/(\$filters\s*=\s*\[)([^\]]*?)(\])/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
      return $content;
    }

    $fullMatch   = $matches[0][0];
    $matchOffset = $matches[0][1];
    $prefix      = $matches[1][0];
    $existing    = $matches[2][0];
    $suffix      = $matches[3][0];

    // Check if the key is already present
    if (preg_match('/[\'"]' . preg_quote($name, '/') . '[\'"]/', $existing)) {
      return $content;
    }

    // Determine separator style (match existing formatting)
    $trimmed = trim($existing);

    if ($trimmed === '') {
      // Empty array → single line
      $newInner = "'{$name}'";
    } elseif (str_contains($existing, "\n")) {
      // Multi-line array
      $indent   = $this->detectIndent($existing);
      $newInner = rtrim($existing, " \t");
      // Ensure trailing comma on last item
      if (!str_ends_with(rtrim($newInner), ',')) {
        $newInner = rtrim($newInner) . ',';
      }
      $newInner .= "\n{$indent}'{$name}',\n";
    } else {
      // Single-line array with existing items
      $newInner = rtrim($trimmed, ',') . ", '{$name}'";
    }

    $newMatch = $prefix . $newInner . $suffix;

    return substr($content, 0, $matchOffset)
      . $newMatch
      . substr($content, $matchOffset + strlen($fullMatch));
  }

  /**
   * Detect the leading whitespace indent used inside a multi-line array body.
   */
  protected function detectIndent(string $arrayBody): string
  {
    if (preg_match('/\n([ \t]+)/', $arrayBody, $m)) {
      return $m[1];
    }

    return '        '; // fallback: 8 spaces
  }
}
