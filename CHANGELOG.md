# Changelog

## [Unreleased] - 2024-12-27

### Fixed

- **EmailMarketing Module**: Harmonized module name in database (`EmailMarketing` instead of mixed `Email Marketing` / `email-marketing`). This fixed the 404 error on `admin/email-marketing/templates`.
- **Documentation**: Updated `Modules/EmailMarketing/MODULE_ACTIVATION_COMPLETE.md` with correct SQL instruction.
- **Micro-Migration**: Added `Modules/EmailMarketing/Database/Migrations/000_register_module.php` to ensure correct module registration in future deployments.

### Changed

- **Core Refactoring**: Updated `App\Core\Application::run()` to handle returned responses from controllers (String or Array/JSON).
- **Controller Refactoring**: Refactored all controllers in `Modules/` to use `return view(...)` and `return json_encode(...)` instead of `echo`. This improves testability and enables response middleware processing.
