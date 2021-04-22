# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.1] - 2021-04-22
## Changed
+ Updated to Laravel Mix 6

## [2.0.0] - 2021-04-15
## Changed
+ The project is now open source!

## [1.5.1] - 2021-02-17
## Fixed
+ Fix for rate limit behind a load balancer

## [1.5.0] - 2021-02-02
## Added
+ [SP-2573] - add forced flag checks and tests
+ Update dependencies, remove unused ones

## [1.4.1] - 2020-11-24
## Fixed
+ Removed exception logging when no updates are available.
+ Logout route was missing.

## [1.4.0] - 2020-11-11
## Added
+ Ability to track device installations.
+ Delete an already uploaded build.

## Changed
+ Updated composer binary to 2.0
+ Unused routes cleanup

## [1.3.0] - 2020-11-04
## Added
+ Force an update on already uploaded builds.
+ Download statistics on the main page.
+ Progress bar on upload build page.

## Changed
+ Main dashboard redesign.
+ The available from can be leaved empty when uploading a build.

## [1.2.0] - 2020-10-20
### Added
+ Added nodejs docker containers.

### Changed
+ A temporary url to Amazon s3 is returned instead of streaming the file from the server.
+ Vue doesn't show the redirecting text anymore.

### Fixed
+ Redirect to login if the sanctum session token is expired.
+ Use cookie based sessions.


## [1.1.0] - 2020-10-15
### Added
+ Added an initial basic frontend dashboard.
+ Automatic redirect to the right platform download.
+ API authentication ported to Laravel Sanctum.

### Changed
+ Compiled assests are no longer in VCS.

### Fixed
+ Migrate error with old Mysql versions.

## [1.0.0] - 2020-09-18
### Added
+ Initial release. Yay!
