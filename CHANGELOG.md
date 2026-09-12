# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added

- Field-specific operator policies with exact-field and wildcard rules across
  Invokable, Ruleset, Expression, and Tree engines.

### Removed

- The unused `Resources`, `FilterableSettings`, and resource bag APIs.
- Legacy field-validation, mapping, and tree-relation helpers superseded by the
  shared payload pipeline.

## [3.0.1] - 2026-09-12

### Fixed

- Tree groups now join their direct children using the group's declared `AND`
  or `OR` boolean, including across recursively nested groups.

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

## [2.15.0] - 2026-03-09

### Added

- Expanded the Invokable method-attribute suite with authorization, range and
  membership checks, casting, splitting, value mapping, regex validation,
  sanitization, scopes, conditional skipping, and trimming.
- `Payload::cast()` and `Payload::as()` helpers for dynamic value casting.
- Optional in-place value replacement for `Payload::explode()` and `split()`.

### Changed

- Attribute processing now distinguishes method attributes through the
  `MethodAttribute` contract.

## [2.14.1] - 2026-03-01

### Added

- The `Outcome` contract for representing resolved and rejected attribute
  processing results.

### Changed

- Renamed the payload's pre-sanitization value property from `beforeSanitize`
  to `rawValue`; `raw()` remains the accessor.
- Attribute processing now returns an `Outcome`.

### Fixed

- Filter execution now returns the result of the `finally()` lifecycle hook.

## [2.14.0] - 2026-01-26

### Added

- Dynamic Eloquent builder method forwarding from a `Filterable` instance via
  the `HandleFluentReturn` trait.

## [2.13.3] - 2025-12-29

### Fixed

- Relaxed Illuminate dependency constraints so Laravel 10, 11, and 12 can all
  be installed with the package.

## [2.13.2] - 2025-12-29

### Changed

- Updated Illuminate and related dependencies for Laravel 12 compatibility.

## [2.13.1] - 2025-12-04

### Fixed

- Eloquent builder type declarations now use the builder contract for broader
  implementation compatibility.

## [2.13.0] - 2025-11-19

### Added

- Configurable exception handlers for filtering failures.
- `FilterableExceptionHandler`, the default handler, `StrictnessException`,
  `SkipExecution`, and dedicated empty-value errors.
- The `Skippable` contract and engine-level guarded execution.

### Changed

- All engines now route filtering failures through a shared exception-handling
  path, allowing strict execution or controlled skipping.
- Engine exceptions were consolidated under the Engines namespace.

## [2.12.0] - 2025-11-18

### Added

- Applied-filter state through `commit()` and `applied()` across all engines.
- Payload helpers for membership checks, rule matching, empty-value checks,
  slugs, regular expressions, date and timestamp validation, Carbon conversion,
  and value splitting.
- Macro support on `Payload`.

### Changed

- Shared engine configuration for allowed fields, operators, and empty-value
  behavior was centralized in the engine foundation.

## [2.11.0] - 2025-11-12

### Added

- `initially()` and `finally()` lifecycle hooks for transforming the query
  builder before and after filtering.
- Lifecycle hook placeholders in generated filter classes.

## [2.10.4] - 2025-11-12

### Changed

- Metadata-only release tag. It points to the same commit as `v2.10.0` and
  contains no additional code changes.

## [2.10.0] - 2025-11-12

### Added

- Query-result caching with deterministic cache keys, tags, scopes, and
  profile-aware configuration.
- Automatic model-event cache invalidation.
- Cache controls on `Filterable`, the facade, and fluent invoker.

## [2.9.4] - 2025-10-30

### Fixed

- Invokable filter methods can no longer conflict with core `Filterable`
  methods.

## [2.9.3] - 2025-10-27

### Fixed

- Provider inheritance now resolves profiler driver definitions consistently.
- Raw field, operator, and value expressions are parsed more reliably by the
  dissector.

## [2.9.2] - 2025-10-26

### Fixed

- Generated filter method names are validated and normalized before files are
  written.

## [2.9.1] - 2025-10-25

### Changed

- Corrected public API names and references, including `Invokeable` to
  `Invokable`, `useEngin` to `useEngine`, and misspelled allowed-field methods.

## [2.9.0] - 2025-10-25

### Added

- CLI commands to set up the package, create filters and filter methods, list
  filters, inspect filter configuration, test generated SQL, and discover
  searchable columns and suggested indexes.
- Reusable command helpers and request-source inspection.

### Fixed

- Stub path resolution, command signatures, directory creation, model
  detection, and invalid filter-class reporting.

## [2.8.0] - 2025-10-25

### Added

- Invokable operator allowlists and automatic allowed-field discovery from
  declared filter methods.
- Mutable field and operator accessors on `Payload`.

### Changed

- Clause creation and application now use `Payload`, the dissector, and the
  shared clause factory consistently.

## [2.7.0] - 2025-10-20

### Added

- Data provisioning for sharing contextual values across `Filterable`
  instances and retrieving either a single key or the complete data set.

## [2.6.1] - 2025-10-20

### Fixed

- Corrected sanitizer constructor typing and normalized internal trait
  references.

## [2.6.0] - 2025-10-19

### Added

- Filter profiles for applying reusable, context-dependent filtering
  configuration.
- `FilterableState` for exposing filtering status to event observers.
- Matchable profile conditions.

### Changed

- Event callbacks now receive filtering state through `FilterableState`.

## [2.5.3] - 2025-10-14

### Added

- A centralized event system for observing the filtering lifecycle.
- Filterable event registration and a singleton event manager.

### Fixed

- Observer payloads are now passed to callbacks correctly.

## [2.4.3] - 2025-10-13

### Fixed

- Package configuration now uses cache-safe arrays instead of collections.

## [2.4.2] - 2025-10-13

### Added

- The `Filterable` facade and its service-provider bindings.
- Runtime registration of custom engines through `EngineManager::extend()`.
- `tap()` and `unless()` for fluent instance configuration and conditional
  filtering.

### Changed

- Service-provider registration was corrected to rely on Laravel package
  discovery.

## [2.3.2] - 2025-10-09

### Added

- Mutable payload values through `Payload::setValue()`.
- The initial attribute pipeline, registry, handler contract, and processing
  context for Invokable filters.
- `DefaultValue` and `Required` attributes.

### Changed

- Invokable filter initialization now processes method attributes.

## [2.2.2] - 2025-09-01

### Changed

- Renamed the internal `FilterRegisterator` to `FilterResolver` and reduced
  duplicate resolution logic.

### Fixed

- `filter()` now accepts a filter class name directly and resolves aliases and
  model-declared filters consistently.

## [2.2.1] - 2025-08-28

### Fixed

- Sanitization is now applied consistently by Invokable, Ruleset, Expression,
  and Tree engines.

## [2.2.0] - 2025-08-28

### Added

- Query sorting with field allowlists, aliases, field mapping, multi-field
  input, and Invokable integration.
- Per-instance controls for the sort key, delimiter, and SQL null placement.
- Relation-path validation and optional empty-value skipping in the Ruleset
  engine.

### Changed

- Clause construction was centralized in `ClauseFactory`.

## [2.1.0] - 2025-08-18

### Added

- Payload conversion and inspection helpers for LIKE values, booleans,
  integers, arrays, JSON, length, type, null, and empty-value checks.

## [2.0.0] - 2025-08-17

### Added

- Laravel 12 support alongside Laravel 8, 9, 10, and 11.
- Automatic model `filter()` macro registration and filter-class resolution
  from a model's `$filterable` property.
- Filter aliases, a global `filterable()` helper, and Laravel container-based
  filter resolution.
- Fluent `when()`, `through()`, `toSql()`, and query execution through
  the serializable `Invoker` wrapper.
- Customizable published filter stubs.
- Query profiling with file and database storage plus profiler events.
- Per-page pagination limits, field normalization, and optional sanitizer
  disabling.

### Changed

- Rebuilt the engine foundation around a shared abstract engine, contracts,
  settings, bags, parsing, mapping, clause application, and execution services.
- Service-provider responsibilities now use package discovery and automatic
  model integration.
- Renamed the sanitization contract from `HasSanitize` to `Sanitizable`.

### Breaking Changes

- Engine implementations now extend the shared abstract engine and use the new
  execution contracts.
- Automatic macro registration replaces manual model-trait setup for normal
  usage.

## [1.0.0] - 2025-05-22

### Added

- Initial stable release with Invokable, Ruleset, Expression, and Tree filtering
  engines.
- Eloquent model scope integration and dynamic engine selection, including
  optional header-driven selection.
- Allowed-field and operator validation, field mapping, relational filtering,
  and hierarchical `AND`/`OR` groups.
- Request-backed or manually supplied input, empty-value handling,
  sanitization, authorization, and validation hooks.
- `Payload` as the Invokable engine's filter-value context.
- Artisan filter generation and publishable package configuration.
- Initial PHPUnit suite, CI workflow, README, and documentation site.
