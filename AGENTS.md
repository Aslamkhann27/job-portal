# Repository Guide

## Project overview

This repository contains a lightweight PHP job portal using Bootstrap, HTML, CSS, vanilla JavaScript, and JSON-file persistence. It is a learning/demo application, not a production-ready multi-user service.

## Canonical architecture

- Root PHP pages implement the main routes and role-specific screens:
  - `index.php`: searchable job listing and landing page.
  - `job.php`: job details and application entry point.
  - `apply.php`: application validation and persistence.
  - `skill_test.php`: timed skill assessment and scoring.
  - `register.php`, `login.php`, `logout.php`: authentication flows.
  - `recruiter.php`: recruiter job management.
  - `assessment_builder.php`: recruiter-created assessment editing.
  - `admin.php`: administrator dashboard.
- `includes/auth.php`: JSON helpers, authentication, roles, and sessions.
- `includes/job_repository.php`: job loading, creation, and update operations.
- `includes/jobs.php`: seed job data.
- `includes/skill_tests.php`: question banks and assessment result helpers.
- `assets/css/style.css` and `assets/js/`: frontend styling and browser behavior.
- `data/`: runtime JSON data. Do not treat committed sample JSON as a production data model.

Files and directories with a ` (1)` suffix are duplicate copies and are not used by the canonical application. Prefer the un-suffixed files unless a task explicitly targets a duplicate.

## Intentional design choices

- The project intentionally uses JSON files instead of a database.
- No Composer packages or frontend build step are currently required.
- PHP sessions provide authentication state.
- The first account created in an empty user store becomes the administrator.
- Recruiters may manage only their own jobs and assessments.
- Skill tests use five questions with four options; recruiter-created assessments override category defaults for the relevant job.

Do not replace these design choices as incidental cleanup. If a requested change requires altering them, explain the impact and update documentation/tests.

## Review priorities

Pay particular attention to:
1. Authentication, session fixation, role checks, and privilege escalation.
2. Recruiter ownership checks on job and assessment mutations.
3. Candidate application validation and seat-limit enforcement.
4. Assessment answer handling, scoring, and attempts required before applying.
5. CSRF protection for state-changing requests.
6. Output escaping, JSON file integrity, concurrent writes, and access to `data/`.
7. Backward compatibility with existing JSON records and seed data.

## Testing and validation

- Use PHP 8.2 in CI; the project targets PHP 7.4+ with PHP 8+ recommended.
- Run `php -l <file>` for changed PHP files.
- Run `php tests/skill_tests_test.php` for skill-assessment behavior changes.
- The GitHub Actions workflow `.github/workflows/php-checks.yml` lints PHP files and runs the assessment regression test.
- For user-facing flows, include a concise manual test plan covering candidate, recruiter, and administrator permissions as applicable.

## Scope discipline

- Keep changes focused; do not reformat unrelated files.
- Do not commit secrets, real accounts, resumes, or runtime user/application data.
- Update `README.md` when setup steps, routes, architecture, or user-visible features change.
