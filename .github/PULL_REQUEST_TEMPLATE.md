## What does this PR do?

<!-- Brief description of the change -->

## Type

- [ ] New template
- [ ] Template update
- [ ] Other (docs, CI, etc.)

## Checklist

- [ ] App ID is lowercase and hyphenated, matches directory name and `manifest.yaml` `id`
- [ ] `manifest.yaml` has all required fields (`id`, `name`, `description`, `icon`, `category`)
- [ ] `icon` set to a CDN URL (SVG preferred) or a valid SF Symbol name
- [ ] Compose file is in `lawn-compose/` with a versioned filename
- [ ] Compose file passes `docker compose config` validation
- [ ] Health checks defined so Lawn knows when the app is ready
- [ ] Tested locally with `lawn template validate`
