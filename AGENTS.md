# AGENTS.md

This file defines the working rules for AI coding agents that modify the
Filterable repository. It is a repository-maintenance guide, not an end-user
package tutorial.

## Scope and authority

- These instructions apply to the entire repository.
- Follow more specific `AGENTS.md` files if they are added inside a
  subdirectory.
- Follow the user's current request first. Do not expand a focused task into an
  unrelated refactor.
- Treat existing user changes as intentional. Never discard, overwrite, or
  reformat unrelated work.
- Inspect the relevant implementation, tests, documentation, and configuration
  before changing behavior.
- Prefer the smallest coherent change that preserves the current public API.
- Do not create commits, tags, releases, or push branches unless the user asks.

## Project identity

- Package: `kettasoft/filterable`
- Namespace: `Kettasoft\Filterable`
- Purpose: secure, explicit HTTP-request filtering for Laravel Eloquent queries.
- Supported Laravel versions: 10, 11, 12, and 13.
- CI PHP versions:
  - Laravel 10 and 11: PHP 8.2
  - Laravel 12 and 13: PHP 8.3
- Composer currently declares PHP `^8.0`; do not narrow compatibility without
  an explicit compatibility decision and a complete CI update.
- Main development branch: `master`.
- Documentation source: `docs/`.
- Generated documentation branch: `docs`; never edit it directly.

## Non-negotiable architecture

Filterable is currently an Eloquent-focused package. Preserve these boundaries:

- `Filterable` owns configuration, request input, lifecycle orchestration, and
  access to runtime state.
- An `Engine` interprets one request contract and applies the resulting
  filters to an Eloquent builder.
- `Payload` is the single filter-operation value object from parsing through
  validation, transformation, application, applied-state tracking, and skipped
  state.
- `PayloadFactory` validates and resolves fields, operators, mappings,
  relations, and values.
- `PayloadApplier` applies direct and relational payloads through the operator
  strategy resolver.
- `Context` owns transient per-instance data: parsed input, builder, applied
  payload snapshots, skipped payload snapshots, and cache-key state.
- Operator strategies own operator-specific query behavior.
- Method attributes belong to the Invokable engine's payload-processing
  pipeline.

## Execution lifecycle

Preserve the order in `Filterable::apply()`:

1. Authorization pipe.
2. Request validation pipe.
3. Resolve or accept the Eloquent builder.
4. Run `initially()`.
5. Store the active builder in the runtime context.
6. Execute the selected engine.
7. Apply configured sorting.
8. Fire the successful `filterable.applied` event.
9. Run `finally()` and store its returned builder.
10. Return the builder or an `Invoker`.
11. Configure the invoker cache when caching is enabled.

On failure, fire `filterable.failed`, rethrow the exception, and always fire
`filterable.finished`.

Do not move filtering into terminal builder methods. Automatic builder
forwarding may trigger `apply()`, but the lifecycle remains centralized in
`Filterable::apply()`.

## Engine responsibilities

All built-in engines extend
`Kettasoft\Filterable\Engines\Foundation\Engine`.

### Invokable

- Maps declared `$filters` to methods on the filter class.
- Discovers eligible methods automatically.
- Passes a resolved `Payload` to the filter method.
- Runs method attributes before invoking the method.
- Rejects methods that conflict with core `Filterable` methods.
- May express arbitrary domain-specific Eloquent logic; do not force it into a
  generic comparison representation.

### Ruleset

- Parses compact field/operator values.
- Uses the shared dissector, payload factory, operator strategies, relation
  parser, and payload applier.

### Expression

- Normalizes explicit operator/value expressions.
- Supports direct and permitted nested relational fields.
- Optionally validates direct database columns.

### Tree

- Parses nested `AND` and `OR` groups.
- A group owns the boolean used to join its direct children.
- Preserve recursive group boundaries.
- In permissive mode, a rejected child must be omitted without changing the
  boolean relationship between the remaining siblings.

When changing shared behavior, test all four engines unless the behavior is
provably engine-specific.

## Payload rules

- Pass `Payload`, not raw scalar values, through engine operations.
- Preserve these meanings:
  - `field`: public field until field mapping, resolved field afterward.
  - `operator`: requested alias until resolution, resolved operator afterward.
  - `value`: current possibly transformed value.
  - `rawValue`: original unsanitized input.
- Mutations should use the payload methods rather than replacing the object
  unexpectedly.
- Commit a cloned, finalized payload only after its filter was applied.
- Store rejected payloads with their reason in the runtime context.
- When cloning a `Filterable` instance, runtime context and engine binding must
  remain isolated.

## Fields, relations, and operators

- Treat all request input as untrusted.
- Never weaken field or operator allowlists implicitly.
- Preserve public-field validation before field mapping.
- Relation access must be explicitly allowed; do not expose arbitrary relation
  paths by default.
- Preserve complete nested relation paths until `PayloadApplier` separates the
  relation from its terminal column.
- Field-specific operator policies restrict the engine-wide allowlist; they
  must never add a globally unavailable operator.
- Exact field policies take precedence over the optional `*` policy.
- Policies are matched against public field names before mapping.
- Accept both public operator aliases and normalized resolved values.
- An explicit field-policy violation must never silently fall back to the
  default operator.
- Custom operator behavior belongs in a class implementing
  `Engines\Foundation\Operators\Contracts\Operator`, not in conditionals
  duplicated across engines.
- Custom strategies must work for both direct and relational payload
  application where the operator semantics permit it.

## Strict and permissive behavior

- Use the configured exception handler; do not swallow exceptions inside an
  engine.
- `Engine::attempt()` is the shared guarded-execution boundary.
- Exceptions derived from `SkipExecution` carry the rejected payload.
- Strict mode rethrows invalid or skipped operations.
- Permissive mode records skippable payloads and continues safely.
- Configuration/programming errors that cannot be safely skipped should still
  surface in permissive mode.
- New failure modes require tests for strict and permissive behavior.

## Pagination policy

- Pagination policy belongs to the Filterable execution boundary, not to an
  engine; keep it consistent across every engine.
- Calls to `paginate()`, `simplePaginate()`, and `cursorPaginate()` forwarded
  through the `Invoker` must use the same policy resolver.
- When `shouldReturnQueryBuilder()` is enabled, pagination called directly on
  the Filterable instance must use the shared argument adapter before forwarding
  to the raw builder. A builder returned by an explicit `apply()` call is outside
  the package execution boundary.
- An explicit `$perPage` argument takes precedence over request input, but the
  effective maximum remains authoritative.
- Preserve Laravel pagination arguments, named arguments, paginator return
  types, and `paginate()` closure support.
- Do not apply Filterable pagination policy to direct Eloquent queries that do
  not pass through `filter()`.
- Normalize pagination arguments before cache keys are generated so equivalent
  effective page sizes share the correct execution identity.

## Invokable attributes and sanitization

- Method attributes operate through `AttributeContext`,
  `AttributePipeline`, and the `MethodAttribute` contract.
- Attribute handlers receive the active engine context and payload.
- Keep attribute ordering and stages explicit.
- Use the shared sanitization pipeline and sanitizer contracts.
- A reusable transformation belongs in a sanitizer or attribute, not copied
  into multiple engines.
- When adding an attribute:
  - implement the existing attribute contract;
  - document its stage and mutation behavior;
  - add a focused feature test;
  - update the Invokable annotations index and dedicated page when public.
- When adding a sanitizer:
  - implement `SanitizeHandler`;
  - preserve mixed input safely;
  - add unit tests for accepted, rejected, null, empty, and boundary values.

## Public API rules

- Prefer expressive bulk APIs when configuration commonly spans many fields.
- Convenience APIs should delegate to one canonical implementation.
- Validate public configuration input early with actionable messages.
- Fluent configuration methods return `static`.
- Preserve aliases unless a breaking release explicitly removes them.
- `Filterable::for()` is the preferred model-aware factory.
- `using()` is the expressive alias for engine selection.
- Terminal builder calls are forwarded automatically after filters are applied.
- Request access uses `getFromRequest()`.
- Use `InteractsWithFilterable` for new model integration.
- Every new or renamed public method on `Filterable` must also update:
  - `src/Facades/Filterable.php` PHPDoc;
  - the relevant API documentation;
  - the AI instruction file when usage guidance changes;
  - tests for both direct and facade access when applicable.

## Extension points

Preserve existing extension mechanisms instead of hard-coding application
behavior:

- Engines: extend the foundation `Engine` and register through
  `EngineManager`.
- Operators: implement the operator contract and register through
  `filterable.operator_strategies`.
- Sanitizers: implement the sanitizer contract and use the sanitization
  pipeline.
- Exception handling: implement the exception-handler contract and configure
  it.
- Profiles: implement `FilterableProfile` or use a callable profile.
- Sorting: use the existing Sorter and sorting contracts.
- Events: use `FilterableEventManager` and existing lifecycle event names.
- Profiler storage: implement `ProfilerStorageContract`.

New extension points need a contract, one default implementation when
appropriate, container/config integration, documentation, and tests.

## Repository map

- `src/Filterable.php`: main public context and execution lifecycle.
- `src/Engines/`: request-shape interpreters.
- `src/Engines/Foundation/`: shared payload validation, application,
  operators, attributes, parsing, and execution.
- `src/Foundation/Runtime/`: per-instance mutable runtime state.
- `src/Foundation/Caching/`: cache keys, storage coordination, and
  invalidation.
- `src/Foundation/Events/`: event manager and event state.
- `src/Foundation/Profiler/`: profiling and profiler storage.
- `src/Foundation/Sorting/`: sorting implementation.
- `src/Sanitization/`: sanitization pipeline, handlers, and defaults.
- `src/Support/`: small parsing, validation, payload, and generation helpers.
- `src/Commands/`: Artisan commands.
- `src/Providers/`: Laravel bindings, publishing, commands, and boot logic.
- `src/Facades/`: facade and IDE PHPDoc surface.
- `config/filterable.php`: all package defaults.
- `stubs/`: generated filter templates.
- `tests/Unit/`: isolated behavior and package integration tests.
- `tests/Feature/`: lifecycle, attributes, commands, and database-backed
  behavior.
- `tests/Database/`: test migrations and factories.
- `docs/`: VuePress documentation source.
- `.github/workflows/php.yml`: Laravel/PHP test matrix.
- `.github/workflows/deploy-docs.yml`: versioned documentation validation and
  deployment.
- `CHANGELOG.md`: canonical release history.

## Coding standards

- Follow PSR-12 and the style of the surrounding file.
- Use explicit types when compatible with the supported PHP versions.
- Add PHPDoc when a method's purpose, generic array shape, exception behavior,
  or lifecycle role is not fully expressed by its signature.
- New classes and all public/protected methods should have useful PHPDoc.
- Explain intent and invariants, not line-by-line implementation.
- Keep namespaces and imports consistent; remove unused imports.
- Prefer guard clauses and small focused methods.
- Avoid speculative abstractions and premature generalization.
- Do not perform broad formatting or mechanical rewrites in a focused PR.
- Preserve backward compatibility unless the task explicitly targets a major
  release.

## Test requirements

Run the smallest relevant test while developing, then the complete suite before
handoff.

```bash
./vendor/bin/phpunit tests/Unit/Engines/RulesetEngineTest.php
./vendor/bin/phpunit --filter test_name
composer test
```

The suite uses SQLite in memory and stops on the first failure.

For engine changes, cover the relevant combinations:

- valid and invalid fields;
- valid, aliased, resolved, and invalid operators;
- scalar, array, null, and empty values;
- strict and permissive modes;
- direct and relational fields;
- shallow and deeply nested relations;
- mappings and public names;
- default operators and explicit operators;
- applied and skipped payload state;
- valid siblings after a skipped condition;
- every affected engine;
- facade access when the public facade surface changes.

For Tree changes, include:

- root `AND` and `OR`;
- nested mixed groups;
- single-child groups;
- skipped first, middle, and last children;
- groups in which every child is skipped;
- recursion/depth boundaries when relevant.

For a suspected hang or recursion bug, isolate it with a hard timeout:

```bash
timeout --signal=KILL 20s php vendor/bin/phpunit --filter TestName
```

Never leave an unbounded test command running while diagnosing an infinite
loop.

## Required verification

Behavior or PHP changes:

```bash
composer validate --strict
composer test
git diff --check
```

Documentation changes:

```bash
npm ci
npm run docs:build
git diff --check
```

Use the existing installed dependencies during normal work. Run `npm ci` only
when dependencies are absent, changed, or a clean install needs verification.

Do not treat existing PHPUnit deprecation counts or Browserslist age warnings as
new failures, but report any new warning introduced by the change.

## Documentation rules

- Update documentation whenever public behavior, configuration, request shape,
  extension points, exceptions, or commands change.
- Keep examples runnable and aligned with the current API.
- Explain the problem and expected behavior before advanced internals.
- Cover strict/permissive behavior and security boundaries where relevant.
- Add new pages to the appropriate navbar or sidebar section.
- Update `docs/.vuepress/public/filterable-ai.md` when package usage guidance
  for external AI assistants changes.
- Never edit generated files under `docs/.vuepress/dist`.
- The root `CHANGELOG.md` is the single source for the generated
  documentation changelog page.

The documentation channels are:

- root URL: latest stable release;
- `/v2/`: version 2;
- `/v3/`: version 3;
- `/next/`: current `master`.

Pull requests build-check documentation. Pushes to `master` publish `next`.
Published stable GitHub releases deploy the versioned major and the root latest
documentation.

## Changelog and releases

- Add user-visible unreleased changes under `## [Unreleased]`.
- Do not create an empty release entry before a release is being prepared.
- Release headings must use:

```markdown
## [3.1.0] - YYYY-MM-DD
```

- Git tags use `vMAJOR.MINOR.PATCH`.
- The documentation page converts release headings to links targeting:
  `https://github.com/kettasoft/filterable/releases/tag/vMAJOR.MINOR.PATCH`.
- Use semantic versioning:
  - patch: backward-compatible fixes;
  - minor: backward-compatible features;
  - major: breaking public API or behavior.
- Update the changelog after a feature/fix is merged or as part of the release
  preparation workflow, according to the maintainer's requested PR split.
- Never tag or publish a release without explicit maintainer authorization.

## Git and pull requests

- Start focused branches from the latest `master`.
- Branch names use `type/short-description`, for example:
  - `feat/field-operator-policies`
  - `fix/tree-group-booleans`
  - `refactor/runtime-context`
  - `test/operator-strategies`
  - `chore/laravel-compatibility`
- The remote branch `docs` prevents names beginning with `docs/`. Use
  `documentation/short-description` for documentation branches.
- Commit messages use `type: short imperative description`.
- Keep implementation/tests and documentation in separate commits when that
  makes review or cherry-picking clearer.
- Do not commit generated documentation output, local caches, temporary files,
  test result caches, screenshots, or downloadable PR-description files.
- A PR must state:
  - the problem;
  - the chosen behavior;
  - public API or compatibility impact;
  - engine coverage;
  - strict/permissive behavior where relevant;
  - documentation changes;
  - exact verification commands and results.

## Security and compatibility

- Filtering APIs are security boundaries. Default to explicit fields,
  operators, and relations.
- Never log or expose private request values in new diagnostics.
- Do not commit credentials, tokens, production data, or private reports.
- Follow `.github/SECURITY.md` and `docs/community/security.md` for
  vulnerability handling.
- Changes to Composer constraints require testing every affected matrix entry.
- Changes to Laravel integration must consider service-provider registration,
  package discovery, container bindings, facades, model macros, and generated
  stubs.
- Changes to serialization must account for queued invokers and closure
  serialization.
- Cache changes must account for deterministic keys, tags, profiles, scopes,
  invalidation, and disabled-cache behavior.

## Definition of done

A task is complete only when:

- the requested behavior is implemented without unrelated changes;
- architecture boundaries above are preserved;
- public API and facade annotations agree;
- relevant focused and regression tests exist;
- the complete applicable test/build commands pass;
- documentation and configuration match the implementation;
- `CHANGELOG.md` is updated when requested or intentionally deferred;
- `git diff --check` passes;
- no generated or temporary files are staged;
- the handoff reports changes, verification, compatibility impact, and any
  deliberately remaining work.
