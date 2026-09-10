# Contributing to Filterable

Thank you for helping improve Filterable. Contributions can include bug reports, documentation fixes, tests, performance improvements, and new features.

## Before You Start

- Search the [existing issues](https://github.com/kettasoft/filterable/issues) before opening a new one.
- Small fixes and documentation corrections can go directly to a pull request.
- For new features, public API changes, or architectural work, open an issue or [discussion](https://github.com/kettasoft/filterable/discussions) first so the approach can be agreed on.
- Report security vulnerabilities privately according to the [security policy](SECURITY.md), not in a public issue.

## Development Setup

Fork the repository, then clone your fork and install the dependencies:

```bash
git clone git@github.com:YOUR-USERNAME/filterable.git
cd filterable
git remote add upstream https://github.com/kettasoft/filterable.git
composer install
```

Create a focused branch from the latest `master`:

```bash
git fetch upstream
git switch master
git pull --ff-only upstream master
git switch -c type/short-description
```

Use a current PHP version supported by the dependency set in `composer.lock`. The package compatibility matrix is documented in the [installation guide](https://kettasoft.github.io/filterable/installation.html).

## Making Changes

- Keep each pull request focused on one feature, fix, or documentation improvement.
- Follow the existing code style and PSR-12 conventions.
- Preserve backward compatibility unless a breaking change has been discussed first.
- Add or update tests for behavior changes and bug fixes.
- Update public documentation when an API, configuration option, request shape, or command changes.
- Never commit credentials, tokens, private request data, or generated build output.

The source documentation lives in `docs/` on `master`. The `docs` branch contains the generated website and should not be edited directly.

### Engine Changes

Changes to an existing engine should cover its request shape, strict and permissive behavior, operators, relational fields, and error paths where relevant.

New engines must extend `Kettasoft\Filterable\Engines\Foundation\Engine`, implement the required behavior, and be registered through `EngineManager`. See the [custom engines guide](https://kettasoft.github.io/filterable/features/custom-engines.html) before proposing one.

## Running Checks

Validate Composer metadata and run the complete PHP test suite:

```bash
composer validate --strict
composer test
```

Run a focused test while developing:

```bash
./vendor/bin/phpunit tests/Unit/Engines/RulesetEngineTest.php
```

For documentation changes, install the Node dependencies and verify the production build:

```bash
npm ci
npm run docs:build
```

The GitHub Actions matrix runs the package suite against all supported Laravel versions. Your pull request should pass every matrix job.

## Commit Messages

Use a short, imperative summary in this format:

```text
type: short description
```

| Type | Use it for |
| --- | --- |
| `feat` | New user-facing behavior |
| `fix` | Bug fixes |
| `docs` | Documentation-only changes |
| `refactor` | Internal changes without behavior changes |
| `test` | Test-only changes |
| `chore` | Dependencies, CI, and maintenance |

Examples:

```text
feat: add a custom operator strategy
fix: enforce the tree engine depth limit
docs: clarify expression request syntax
```

## Pull Request Checklist

Before requesting review, confirm that:

- The PR explains the problem and the chosen solution.
- Unrelated changes are excluded.
- New and existing tests pass.
- Documentation and examples match the implementation.
- Backward-compatibility or migration concerns are called out.
- The PR references its related issue or discussion when one exists.

Draft pull requests are welcome when you want early feedback on an agreed direction.

## Reporting Bugs

Include enough information for someone else to reproduce the problem:

- Filterable, Laravel, and PHP versions.
- The selected engine and relevant configuration.
- A minimal filter class and request payload.
- Expected and actual behavior.
- The exception and stack trace, with sensitive values removed.

Use [GitHub Issues](https://github.com/kettasoft/filterable/issues) for reproducible bugs and [GitHub Discussions](https://github.com/kettasoft/filterable/discussions) for usage questions and design ideas.

## Conduct

Be respectful and constructive. Harassment, personal attacks, and discriminatory behavior are not accepted. By participating, you agree to follow the [Contributor Covenant](https://www.contributor-covenant.org/).
