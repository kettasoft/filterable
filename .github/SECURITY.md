# Security Policy

## Supported Versions

Security fixes are released for the latest stable version of Filterable. Older releases may still install through their Composer constraints, but they do not receive guaranteed security updates.

| Version | Security updates |
| --- | --- |
| Latest stable release | Supported |
| Older releases | Not supported |

Keep Filterable, Laravel, PHP, and your other application dependencies up to date before reporting a vulnerability.

## Reporting a Vulnerability

Do not disclose suspected vulnerabilities in public issues, discussions, pull requests, or social media.

Email [kettasoft@gmail.com](mailto:kettasoft@gmail.com?subject=Filterable%20security%20report) with the subject `Filterable security report`. Include, when available:

- A clear description of the vulnerability and its impact.
- The affected Filterable, Laravel, and PHP versions.
- Required configuration and the engine involved.
- Reproduction steps or a minimal proof of concept.
- Suggested mitigations or fixes.
- Whether the issue is already public or has been shared elsewhere.
- Your preferred name for acknowledgement, or whether you prefer to remain anonymous.

Remove real credentials, personal data, and production data from the report. Use the smallest proof of concept needed to demonstrate the issue.

## Scope

This policy covers vulnerabilities caused by code distributed in the Filterable package, including its filtering engines, request handling, authorization boundaries, caching integration, configuration defaults, and generated stubs.

Problems that only affect Laravel, PHP, or another dependency should be reported to that project. Usage questions, ordinary bugs without security impact, and documentation corrections can be reported through the public [issue tracker](https://github.com/kettasoft/filterable/issues).

## Response Process

The project aims to:

- Acknowledge a complete report within 48 hours.
- Provide an initial assessment or request more information within 7 days.
- Keep the reporter informed when the status materially changes.
- Prepare a fix and release based on severity and complexity.
- Coordinate public disclosure after a fix is available when practical.

These are response targets rather than guaranteed service-level agreements. Please allow a reasonable remediation window before publishing details.

## Responsible Research

Use test applications and data you own or are authorized to access. Avoid privacy violations, service disruption, data destruction, and accessing more information than is necessary to demonstrate the vulnerability.

Good-faith reports that follow this policy are appreciated. With your permission, the project will credit you in the release notes or security advisory.
