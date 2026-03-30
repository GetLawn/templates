# Security Policy

## Scope

This policy covers the app templates in this repository. For security issues in the Lawn application itself, see the [main Lawn repository](https://github.com/getlawn/lawn/security).

## What to report

- Default or hardcoded credentials that should be generated
- Container images with known critical vulnerabilities
- Ports exposed beyond localhost when they shouldn't be
- Insecure default configurations (e.g., authentication disabled)
- Secrets or tokens committed in template files

## Reporting a vulnerability

**Do not open a public issue.**

Use GitHub's private vulnerability reporting to submit a report:

[Report a vulnerability](https://github.com/getlawn/templates/security/advisories/new)

Include:
- The affected app template
- A description of the vulnerability
- Steps to reproduce, if applicable

## Response timeline

- **Acknowledgment** within 48 hours
- **Assessment** within 5 business days
- **Fix** within 30 days for confirmed issues

## Credit

Reporters are credited in the fix commit unless they prefer to remain anonymous.
