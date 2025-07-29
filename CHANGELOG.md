# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2025-07-29

### Added

-   ✅ **Laravel 9, 10, 11, and 12 support**.
-   ✅ **Auto Binding** for filter classes via Laravel container.
-   ✅ **Automatic `filter()` macro registration** for Eloquent models.
-   ✅ **Filter Aliases** system for mapping short keys to fully qualified filter class names.

### Changed

-   🔧 Refactored internal architecture for better modularity and performance.
-   ♻️ Service provider responsibilities reorganized for automatic setup and registration.

### Breaking Changes

-   Auto-registration and macro binding have replaced manual trait inclusion.
-   Custom filter bindings must now follow the new auto-discovery mechanism.
-   Aliases must be declared in the config if you wish to use them in the query input.
