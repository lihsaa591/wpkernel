# Contributing to WPKernel

## Setup

```bash
composer install
npm install
```

## Before opening a PR

```bash
composer run phpcs      # WPCS
composer run phpstan    # static analysis
composer run test       # PHPUnit (Brain Monkey, no WP install needed)
npm run lint:js
npm run build
```

CI runs all of the above, plus a PHP 8.1/8.2/8.3 test matrix. A PR that doesn't pass locally won't pass in CI either.

## Scope discipline

WPKernel is infrastructure. Before adding something, ask: *does every plugin built on this need it, or does only my plugin need it?* If it's the latter, it belongs in your plugin's own `includes/`, not here.

## Commit style

Imperative mood, present tense: `Add rollback support to Migrator`, not `Added` or `Adding`. Reference the issue number when one exists.

## Code of conduct

Be direct, be kind, assume good faith. Disagree about the code, not the person.
