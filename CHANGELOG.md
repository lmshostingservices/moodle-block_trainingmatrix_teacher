## [1.2.9] - 2026-11-02

Tested on Moodle 4.5, 5.0, 5.1, 5.2 and 5.3.

- Users without oversight permission saw a hard-coded "You do not have permission" message on their Dashboard; the block now stays hidden for them.
- Teachers could not add the block to their own Dashboard (myaddinstance was only granted to course-level archetypes).
- The overview now lists the first 25 staff (with a count and a link to the full dashboard) instead of loading everyone on every page view.
- Removed ~220 lines of unreachable legacy rendering.
- Requires Training Matrix 1.0.21; supported range 4.5–5.3.

## [1.2.8] - 2026-10-12

- Graduate the approved staff oversight design with real active staff, evidence counts, search and expandable details.
- Retain system viewall authorisation and require separate Certificate Pro permissions for other users' PDFs.
- Replace inferred compliance verdicts with explicitly labelled evidence counts; require Training Matrix 1.0.19.

## [1.2.7] - 2026-10-12

- Require the compatible Training Matrix parent release for staff compliance reporting.
- Align the declared compatibility target with the training suite.

## [v1.2.2] - 2025-12-23

### Security
- Added Privacy API provider for GDPR compliance

# Changelog - Staff Training Dashboard Block

All notable changes to this plugin will be documented in this file.

## [1.2.1] - 2025-12-22

### Changed
- Added official Moodle 5.x compatibility declaration (`$plugin->supported = [400, 500]`)



## [1.2.0] - 2025-12-20

### Fixed
- Removed empty folders that caused Moodle plugin validation errors
- Corrected ZIP folder structure (trainingmatrix_teacher/ not block_trainingmatrix_teacher/)
- Aligned UI with lms-labs.com design system (Inter font)

### Changed
- Versioned ZIP filename (block_trainingmatrix_teacher_v1.2.0.zip)

## [1.1.0] - 2025-12-17

### Added
- Manager/teacher view of team training compliance
- Filterable staff list with compliance status
- Export functionality for compliance reports

## [1.0.0] - 2025-12-15

### Added
- Initial release
- Dashboard block for managers to view staff training
- Moodle 4.0+ compatibility
