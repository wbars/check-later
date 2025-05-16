# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - YYYY-MM-DD

### Added
- PHPUnit tests with 80%+ code coverage
- PSR-12 coding standards compliance
- PHPStan static analysis at level 7
- Improved error handling and logging with PSR-3 logger
- CI/CD pipeline with GitHub Actions
- Comprehensive documentation
- Composer scripts for testing, linting, and static analysis

### Changed
- Updated dependencies to latest versions
- Enhanced error handling with structured logging
- Improved database operations with better error handling
- Refactored Bot class to use dependency injection for logger

### Fixed
- Various code style issues to comply with PSR-12
- Potential security issues in database operations
- Error handling in webhook processing

## [1.0.0] - Initial Release

### Added
- Basic bot functionality
- Automatic content classification
- Category remapping
- Random suggestions
- Obsolete marking
- SQLite and MySQL support