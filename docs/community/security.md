---
title: Security Policy
description: Learn which Filterable versions receive security fixes and how to report a vulnerability privately.
tags: [community, security, vulnerability, disclosure]
---

# Security Policy

## Supported versions

Security fixes are released for the latest stable version of Filterable. Older releases may remain installable through Composer, but they do not receive guaranteed security updates.

| Version | Security updates |
| --- | --- |
| Latest stable release | Supported |
| Older releases | Not supported |

Keep Filterable, Laravel, PHP, and your application dependencies current before reporting a vulnerability.

## Report a vulnerability

Do not disclose suspected vulnerabilities in public issues, discussions, pull requests, or social media.

Email [kettasoft@gmail.com](mailto:kettasoft@gmail.com?subject=Filterable%20security%20report) with the subject `Filterable security report`. Include, when available:

- A clear description and expected impact.
- Affected Filterable, Laravel, and PHP versions.
- Required configuration and the engine involved.
- Reproduction steps or a minimal proof of concept.
- Suggested mitigations or fixes.
- Whether the issue is already public or was shared elsewhere.
- Your preferred credit, or whether you want to remain anonymous.

Remove real credentials, personal data, and production data. Use only the smallest proof of concept necessary to demonstrate the issue.

## What is in scope?

The policy covers vulnerabilities caused by code distributed with Filterable, including its engines, request handling, authorization boundaries, caching integration, configuration defaults, and generated stubs.

Report vulnerabilities that only affect Laravel, PHP, or another dependency to that project. Use the public [issue tracker](https://github.com/kettasoft/filterable/issues) for ordinary bugs, usage problems, and documentation corrections that have no security impact.

## What happens after a report?

The project aims to:

- Acknowledge a complete report within 48 hours.
- Provide an initial assessment or request more details within 7 days.
- Share updates when the status materially changes.
- Prepare a fix and release based on severity and complexity.
- Coordinate disclosure after a fix is available when practical.

These are response targets, not guaranteed service-level agreements. Allow a reasonable remediation window before publishing technical details.

## Responsible research

Use test applications and data you own or are authorized to access. Avoid privacy violations, disruption, data destruction, and accessing more information than is necessary to prove the vulnerability.

Good-faith reports that follow this policy are appreciated. With your permission, the project will credit you in its release notes or security advisory.
