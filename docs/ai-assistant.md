---
title: AI Assistant Guide
description: Add version-aware Filterable instructions to AI coding assistants without replacing your project's existing rules.
---

# AI Assistant Guide

Filterable provides a vendor-neutral instruction file that helps AI coding assistants use
the package's current APIs, choose the correct engine, and avoid removed or deprecated
patterns.

[Download `filterable-ai.md`](https://kettasoft.github.io/filterable/filterable-ai.md)

The file is guidance for coding tools, not executable application code. Keep your project's
own architecture, security rules, and conventions as the higher priority.

## Add it to a project

Store the file in a dedicated location so it does not replace an existing `AGENTS.md`,
`CLAUDE.md`, or tool-specific rules file:

```bash
mkdir -p .ai
curl -L https://kettasoft.github.io/filterable/filterable-ai.md \
  -o .ai/filterable.md
```

Commit `.ai/filterable.md` with the project if every contributor and coding assistant should
use the same package guidance.

## Connect your AI tool

### AGENTS.md-compatible tools

Add this instruction to the project's existing `AGENTS.md`, or create one if the project
does not have it:

```md
When working with Kettasoft Filterable, read and follow `.ai/filterable.md`.
Project-specific instructions take precedence if they conflict.
```

Do not replace an existing `AGENTS.md`; merge the reference into it.

### Claude Code

Claude Code supports imported instruction files. Add this line to the project's existing
`CLAUDE.md`:

```md
@.ai/filterable.md
```

### Other assistants

Reference `.ai/filterable.md` from the assistant's project-level rules, or ask the assistant
to read it before changing Filterable code. Keeping one canonical copy prevents different
tools from receiving conflicting package guidance.

## Keep it current

Refresh the file when upgrading Filterable:

```bash
curl -L https://kettasoft.github.io/filterable/filterable-ai.md \
  -o .ai/filterable.md
```

Then review the diff before committing it. The installed package version and the project's
published `config/filterable.php` remain the source of truth when they differ from the latest
online guide.

## What the guide covers

- model integration and filter generation
- choosing Invokable, Ruleset, Expression, or Tree
- `Payload`-based filter methods
- request access through `getFromRequest()`
- validation, sanitization, authorization, and allowlists
- relational fields and custom operator strategies
- deprecated and removed APIs that new code should avoid
- a practical test checklist
