# Contributing to Lawn Templates

Thanks for your interest in adding apps to Lawn!

For the full template authoring guide — manifest format, compose extensions, variables, health checks, and testing — see the [Template Documentation](https://getlawn.app/docs/templates).

## Quick start

1. Create a directory under `apps/` with your app's ID (lowercase, hyphenated)
2. Add a `manifest.yaml` with app metadata
3. Add a compose file in `lawn-compose/vX.Y.Z.yaml`
4. Test locally with `lawn template validate`
5. Open a PR

## Minimal example

```
apps/my-app/
├── manifest.yaml
└── lawn-compose/
    └── v1.0.0.yaml
```

**manifest.yaml:**

```yaml
id: my-app
name: My App
tagline: A short description
description: |
  A longer markdown description of what this app does.
symbolFallback: server.rack
category: Utilities
webPort: 8080
```

**lawn-compose/v1.0.0.yaml:**

```yaml
services:
  app:
    image: example/my-app:1.0.0
    ports:
      - 8080:8080
    volumes:
      - ./data:/app/data
    x-lawn:
      healthChecks:
        - type: http
          description: "Web UI is accessible"
          path: /
          expectedStatus: 200
```

## PR checklist

- [ ] App ID is lowercase and hyphenated, matches directory name and `manifest.yaml` `id`
- [ ] `manifest.yaml` has all required fields (`id`, `name`, `description`, `symbolFallback`, `category`)
- [ ] Catalog copy describes verifiable capabilities and avoids unsupported absolute guarantees
- [ ] Privacy and local-storage claims account for clients, integrations, external APIs, and services the user may configure
- [ ] `symbolFallback` set to a valid SF Symbol name (shown when no icon image is available)
- [ ] `iconURL` set to a hosted icon URL if one is available (SVG preferred)
- [ ] `iconIsTemplate: true` added when the hosted icon is monochrome with a transparent background
- [ ] Compose file is in `lawn-compose/` with a versioned filename
- [ ] Compose file passes `docker compose config` validation
- [ ] Health checks defined so Lawn knows when the app is ready
- [ ] Tested locally with `lawn template validate`

## Sign your work (optional)

We encourage signing off your commits using the [Developer Certificate of Origin](https://developercertificate.org/) (DCO). This certifies you have the right to submit your contribution under the project's license.

Add `-s` when you commit:

```
git commit -s -m "feat(catalog): add my-app template"
```

This appends a `Signed-off-by: Your Name <your@email.com>` trailer using your Git `user.name` and `user.email`.
