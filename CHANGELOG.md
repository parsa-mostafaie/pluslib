# Release Notes for 0.x

## [Unreleased](https://github.com/parsa-mostafaie/pluslib/compare/v0.0.04...master)

- Remove `ABSPATH` and `RELPATH`
- Remove not-needed class and helpers
- Remove `use_sha`
- `WithPaths` Trait
- Remove `secure_redirect`

## [v0.0.04](https://github.com/parsa-mostafaie/pluslib/compare/v0.0.03...v0.0.0.04)

- `Select::first`: apply default value
- `Database\DB`: Custom engine (mysql, ...)
- Implement `ServiceProvider`
- Use `join_paths` in autoloader
- Fix phpdoc of `Model::__call`

## [v0.0.03](https://github.com/parsa-mostafaie/pluslib/compare/v0.0.02...v0.0.03) - 8/29/2024

- Add `Response` Class
- Implement Service Container
- Cache facades by getFacadeAccessor
- Upload Bug Fix
- Fix Version in init.php
- Add `version` constant to Application Class
- Simplify `Application::invalidSessionRedirect`
- Add Abstract Class `Controller`
- Trait: `CallMethod`
- Helpers: `loadenv`, `env`
- `e` helper: Add double-encode parameter, pass encoding & flags to `htmlspecialchars`
- Fix Unwanted Changes in Select
- Remove `Select::last`

## [v0.0.02](https://github.com/parsa-mostafaie/pluslib/compare/v0.0.01...v0.0.02) - 8/26/2024

- Add `asset` (with customizable directory) and `join_paths` Helpers
- Automatically create new application if not created in `app` helper
- Add `any` method to router
- Remove \_\_autoload.txt
