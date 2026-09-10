---
title: Contributing
description: Set up Filterable locally, make focused changes, run the required checks, and prepare a pull request.
tags: [community, contributing, development, pull requests]
---

# Contributing

Thank you for helping improve Filterable. Contributions can include bug reports, documentation fixes, tests, performance improvements, and new features.

## Before you start

- Search the [existing issues](https://github.com/kettasoft/filterable/issues) before opening a new one.
- Small fixes and documentation corrections can go directly to a pull request.
- Discuss new features, public API changes, and architectural work in an issue or [GitHub Discussion](https://github.com/kettasoft/filterable/discussions) first.
- Report vulnerabilities privately according to the [security policy](/community/security), never in a public issue.

## Set up the project

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

Use a PHP version supported by the current dependency set. See [Installation](/installation) for the package compatibility matrix.

## Make a focused change

- Keep one feature, fix, or documentation improvement per pull request.
- Follow the existing style and PSR-12 conventions.
- Preserve backward compatibility unless a breaking change was discussed first.
- Add or update tests for behavior changes and bug fixes.
- Update documentation when public APIs, configuration, request shapes, or commands change.
- Never commit credentials, private request data, or generated documentation output.

Documentation source lives in `docs/` on `master`. The `docs` branch contains the generated website and should not be edited directly.

### Engine changes

Changes to an engine should cover its request shape, strict and permissive behavior, operators, relational fields, and error paths where relevant.

A new engine must extend `Kettasoft\Filterable\Engines\Foundation\Engine`, implement its required behavior, and be registered through `EngineManager`. Read [Custom Engines](/features/custom-engines) before proposing one.

## Run the checks

Validate Composer metadata and run the complete test suite:

```bash
composer validate --strict
composer test
```

During development, you can run one test file:

```bash
./vendor/bin/phpunit tests/Unit/Engines/RulesetEngineTest.php
```

For documentation changes, verify the production build:

```bash
npm ci
npm run docs:build
```

GitHub Actions runs the package suite across all supported Laravel versions. Every matrix job should pass before merge.

## Write commit messages

Use a short, imperative summary:

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

## Prepare the pull request

Before requesting review, confirm that:

- The PR explains the problem and the chosen solution.
- Unrelated changes are excluded.
- New and existing tests pass.
- Documentation and examples match the implementation.
- Compatibility or migration concerns are called out.
- The related issue or discussion is referenced when one exists.

Draft pull requests are welcome when you want early feedback on an agreed direction.

## Report a bug

Provide enough information for another person to reproduce the problem:

- Filterable, Laravel, and PHP versions.
- The selected engine and relevant configuration.
- A minimal filter class and request payload.
- Expected and actual behavior.
- The exception and stack trace, with sensitive values removed.

Use [GitHub Issues](https://github.com/kettasoft/filterable/issues) for reproducible bugs and [GitHub Discussions](https://github.com/kettasoft/filterable/discussions) for usage questions and design ideas.

## Conduct

Be respectful and constructive. Harassment, personal attacks, and discriminatory behavior are not accepted. By participating, you agree to follow the [Contributor Covenant](https://www.contributor-covenant.org/).
