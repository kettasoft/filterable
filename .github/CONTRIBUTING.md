# Contributing to Filterable

First off, thank you for considering contributing to Filterable. Every contribution — whether it's a bug report, a feature suggestion, or a pull request — is genuinely appreciated.

---

## Before You Start

For anything beyond a small bug fix or typo, **please open an issue first** to discuss what you'd like to change. This avoids wasted effort if the direction doesn't align with the project's goals.

---

## Local Setup

```bash
git clone https://github.com/kettasoft/filterable.git
cd filterable
composer install
```

Run the test suite to make sure everything is working:

```bash
composer test
```

---

## What You Can Contribute

### Bug Fixes

Open an issue describing the bug with a reproducible example, then submit a PR referencing that issue.

### New Features

Open an issue first and describe the feature and why it belongs in the package. Once discussed and approved, submit your PR.

### New Engine

Filterable is built around its engine architecture. If you want to add a new engine:

1. Extend the `Engine` abstract class in `src/Engines/`
2. Register it in the service provider
3. Add full test coverage
4. Add documentation for it

Open an issue before starting so we can align on the design.

### Documentation

Improvements to the docs are always welcome. The documentation source lives in the `docs/` branch.

---

## Pull Request Guidelines

- **One PR per feature or fix** — keep changes focused
- **Write tests** for any new behavior
- **Follow existing code style** — PSR-12
- **Reference the related issue** in your PR description
- **Update documentation** if your change affects public-facing behavior

---

## Commit Message Format

Follow this format:

```
type: short description
```

Types:

| Type       | When to use                         |
| ---------- | ----------------------------------- |
| `feat`     | New feature                         |
| `fix`      | Bug fix                             |
| `docs`     | Documentation only                  |
| `refactor` | Code change with no behavior change |
| `test`     | Adding or updating tests            |
| `chore`    | Build process, dependencies, config |

Examples:

```
feat: add support for custom operators in Ruleset engine
fix: resolve depth limit not being enforced in Tree engine
docs: add Expression engine examples to README
```

---

## Running Tests

```bash
# Run all tests
composer test

# Run a specific test file
./vendor/bin/phpunit tests/Engines/RulesetEngineTest.php
```

Please make sure all existing tests pass before submitting a PR, and add tests for any new behavior you introduce.

---

## Reporting a Bug

When opening a bug report, please include:

- PHP and Laravel version
- Package version
- A minimal reproducible example
- What you expected vs what actually happened

---

## Code of Conduct

Be respectful. Constructive feedback is welcome, personal attacks are not. This project follows the [Contributor Covenant](https://www.contributor-covenant.org/).

---

## Questions?

Open a [GitHub Discussion](https://github.com/kettasoft/filterable/discussions) or an issue and we'll get back to you.
