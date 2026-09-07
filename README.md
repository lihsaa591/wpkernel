# WPSprout

A PHP DI + REST + React foundation for building modern WordPress plugins.

WPSprout is **infrastructure, not a framework of domain abstractions**. It gives you a dependency-injection container, a service-provider boot lifecycle, versioned database migrations, a capability-gated REST API base, and a `@wordpress/scripts` React admin scaffold — the plumbing every non-trivial plugin needs and nobody enjoys re-writing. It deliberately does **not** ship a Model/Repository/ORM layer: that's coupled to what your plugin actually manages, and a generic version of it would be abstraction with nothing behind it. Build that on top, in your own plugin.

## Why this exists

Most "WordPress plugin boilerplate" repos are either a single flat file with a giant class, or a full framework that assumes you want its opinions about everything. WPSprout sits in between: enough structure that a plugin with real domain complexity doesn't turn into a 3,000-line `class-plugin.php`, but no domain layer imposed on you before you've decided what your plugin's domain even is.

## What's in the box

| Piece | What it gives you |
|---|---|
| **DI container** (`league/container`) | PSR-11 container, bound once in `Plugin`, shared across every provider |
| **Service providers** | Two-phase boot: every provider's `register()` runs, then every provider's `boot()` runs — so provider order never breaks a cross-provider dependency |
| **Migrations** | Laravel-style timestamped migration files, tracked in a single `wp_options` row, with rollback support |
| **REST API base** | `AbstractController` — every route is capability-gated **by default**; a route is public only if you explicitly say so, per-route |
| **Admin UI** | `AdminPage` mounts a React root and enqueues the matching `@wordpress/scripts` build, only on its own screen |
| **Quality gates** | PHPUnit + Brain Monkey, PHPCS (WPCS), PHPStan (WordPress-aware), ESLint, GitHub Actions CI, `wp-env` for local/integration testing |

## What's *not* in the box (yet)

An AI provider abstraction and a WordPress Abilities/MCP registration helper are planned as **optional, separate modules** — added once a real plugin built on WPSprout actually needs them, not spec'd in speculatively. Same for a licensing/paid-tier system: `LicenseInterface` exists as a seam, with no implementation forced on you.

## Getting started

```bash
composer install
npm install
npm run build      # or: npm start, for watch mode
```

Then point `wp-env` (or your own local WP install) at this directory as a plugin:

```bash
npx wp-env start
```

Visit **wp-admin → WPSprout** to see the worked example end-to-end: a migration creates a table, a REST controller reads/writes it, a React admin page calls that REST API.

### Renaming for your own plugin

```bash
php bin/scaffold.php --namespace=YourPlugin --slug=your-plugin --text-domain=your-plugin --name="Your Plugin"
```

This renames the PHP namespace, plugin slug, text domain, and the main plugin file across the codebase. Review the diff before committing — a search/replace script is a starting point, not a guarantee.

Then delete the worked example (`includes/Examples/`, the example migration, the example admin page entry in `config/app.php`) and build your own.

## Architecture notes

- **Custom tables via migrations, not CPTs**, for anything transactional or queried by non-post criteria. CPTs remain a fine choice for content-shaped data.
- **A REST route is capability-gated unless you explicitly opt it out** (`is_public: true`), never the other way around — the failure mode of a forgotten check is "access denied," not "accidentally public."
- **Config is a plain PHP array** (`config/app.php`) — add a provider, a REST controller, or an admin page by adding a line, not by writing more wiring code.
- **Providers only bind in `register()`**; anything that touches a WordPress hook belongs in `boot()`, which runs after every provider has finished registering.

## Requirements

- PHP 8.1+
- WordPress 6.4+
- Node 20+ (build tooling only — not a runtime dependency)

## Keeping dependencies current

[Dependabot](.github/dependabot.yml) opens a weekly PR per outdated dependency (composer, npm, and the GitHub Actions used by CI), grouped by concern (PHPUnit/testing, WPCS, PHPStan, `@wordpress/*`) so related bumps land together instead of as a flood of single-package PRs. Every PR runs the full CI matrix (PHPCS, PHPStan, PHPUnit across PHP 8.1/8.2/8.3, JS lint, build) before it's safe to merge — this is what catches a break like *"a transitive dependency's newest version quietly requires a newer PHP than this project's stated baseline"* automatically, rather than discovering it during an actual plugin install.

For an ad-hoc check between scheduled runs: `composer outdated --direct` and `npm outdated` show what's behind right now.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
