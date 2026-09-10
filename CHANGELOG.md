# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added

- Backend-independent comparison and logical-group operations.
- A driver contract and manager with an initial Eloquent database driver.
- Per-filter Driver selection through `useDriver()` and `getDriver()`.

### Changed

- Ruleset, Expression, and Tree comparisons are dispatched through the selected
  Driver while preserving the existing Eloquent lifecycle.
- Driver infrastructure failures are surfaced in strict and permissive modes
  instead of being silently skipped.

## [3.0.0] - 2026-09-10

### Added

- A composable sanitization pipeline with reusable built-in sanitizers for
  trimming, casting, normalization, clamping, and safe string handling.
- A method-attribute pipeline for validation, authorization, transformation,
  sanitization, scoping, defaults, and conditional skipping.
- `Filterable::for()` for creating model-aware filter instances without
  manually wiring an Eloquent builder.
- A dedicated runtime `Context` for applied and skipped payload state.
- Skipped-filter tracking with the original `Payload` and skip reason.
- Nested and deeply nested relational-field filtering across all engines.
- Extensible operator strategies and custom operator resolution.
- Custom paths and namespaces for the `make-filter` command.
- Laravel 13 support.
- Versioned documentation channels for stable majors and upcoming changes.

### Changed

- The filtering pipeline now uses `Payload` directly from parsing through query
  application, removing the redundant `Clause` layer.
- `Filterable::applied()` returns final `Payload` snapshots, including the
  original raw value.
- Builder methods are forwarded automatically after pending filters are
  applied, without maintaining a manual method allowlist.
- Query application preserves complete nested relation paths consistently
  across the Invokable, Ruleset, Expression, and Tree engines.
- Runtime-aware method attributes now receive the active engine and payload.
- `InteractsWithFilterable` is the preferred model trait; `HasFilterable`
  remains as a deprecated compatibility alias.
- The documentation was reorganized and redesigned with clearer navigation,
  community guides, an AI-assistant guide, and an updated package homepage.

### Breaking Changes

- `Clause`, `ClauseFactory`, `ClauseApplier`, `ClauseKeyMapper`,
  `OperatorMapper`, `OperatorDefinition`, `OperatorDefinitionContract`, and
  `RelationResolver` have been removed.
- `Commitable::commit()` and `Filterable::commit()` accept `Payload` instead of
  `Clause`.
- Engine skip hooks accept `Payload` and an optional message.
- `SkipExecution::getClause()` has been replaced by `getPayload()`.
- `Filterable::get()` has been replaced by `getFromRequest()`.
- `AttributeContext` is constructed from the active engine and payload.
- `HandlerFactory` has been removed in favor of the sanitizer pipeline.

## [2.0.0] - 2025-07-29

### Added

-   ✅ **Laravel 9, 10, 11, and 12 support**.
-   ✅ **Auto Binding** for filter classes via Laravel container.
-   ✅ **Automatic `filter()` macro registration** for Eloquent models.
-   ✅ **Filter Aliases** system for mapping short keys to fully qualified filter class names.

### Changed

-   🔧 Refactored internal architecture for better modularity and performance.
-   ♻️ Service provider responsibilities reorganized for automatic setup and registration.

### Breaking Changes

-   Auto-registration and macro binding have replaced manual trait inclusion.
-   Custom filter bindings must now follow the new auto-discovery mechanism.
-   Aliases must be declared in the config if you wish to use them in the query input.
