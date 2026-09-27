# Job Portal

A lightweight job portal built with PHP, Bootstrap, HTML, CSS, and vanilla JavaScript. It supports job discovery, candidate applications, recruiter job posting, administrator review, seat limits, and short category-based skill tests.

The project is intentionally database-free: application data is stored in JSON files, making it suitable for learning, demonstrations, and small local prototypes.

## Features

- Browse jobs and filter them by keyword, location, and category.
- View job details, requirements, available seats, and skill-test policy.
- Register, sign in, and sign out using PHP sessions.
- Register as a candidate or recruiter; the first registered account becomes the single administrator account.
- Submit applications with validation for name, email, resume URL, and cover note.
- Prevent applications once a job's configured seat capacity is filled.
- Take a five-question Mini Skill Test tailored to the job category.
- Require a skill-test attempt before application when a recruiter marks it mandatory.
- Create and manage recruiter-owned job listings.
- Review applications, remove jobs, and remove candidate accounts from the admin dashboard.
- Use responsive Bootstrap layouts with custom CSS and small JavaScript enhancements.

## Tech stack

- PHP 7.4+ (PHP 8+ recommended)
- HTML5 and CSS3
- Bootstrap 5.3.3, loaded from jsDelivr CDN
- Vanilla JavaScript
- JSON files for persistence

No Composer packages, database server, or build step are required.

## Quick start

1. Install PHP and confirm it is on your PATH.

   ```powershell
   php -v
   ```

2. Open a terminal in the project directory and start PHP's development server.

   ```powershell
   php -S localhost:8000
   ```

3. Open [http://localhost:8000/index.php](http://localhost:8000/index.php) in a browser.

4. Create an account. On a fresh data set, the first account registered is assigned the `admin` role. Later registrations can be candidates or recruiters.

> The web server process must have write access to the `data/` directory, since registrations, job postings, applications, and skill-test attempts are saved there.

## How to use the portal

### Candidates

1. Register as a candidate and sign in.
2. Search or filter the job list.
3. Open a job and review its details.
4. If a Mini Skill Test is required, complete it first.
5. Submit a resume URL and cover note while seats remain available.

The test result is attached to the submitted application. A score of 60% or higher is recorded as `Pass`; lower scores are recorded as `Needs Improvement`.

### Recruiters

1. Register as a recruiter and sign in.
2. Open the Recruiter Portal.
3. Add a job with its company, location, type, category, salary, requirements, and seat count.
4. Choose whether a Mini Skill Test is required before candidates can apply.

Recruiters can view the jobs they created. Job data is saved to `data/jobs.json`.

### Administrators

The first account created in an empty `data/users.json` becomes the administrator. The admin dashboard provides a consolidated view of jobs, candidate accounts, and submitted applications, and allows removal of jobs and seeker accounts.

## Project structure

```text
job-portal/
|-- index.php                   # Landing page and searchable job list
|-- job.php                     # Job details, seats, and application form
|-- apply.php                   # Application validation and persistence
|-- skill_test.php              # Timed Mini Skill Test and scoring
|-- register.php                # Candidate/recruiter registration
|-- login.php                   # Session login
|-- logout.php                  # Session logout
|-- recruiter.php               # Recruiter job-posting portal
|-- admin.php                   # Administrator dashboard
|-- includes/
|   |-- auth.php                # JSON helpers, authentication, roles, sessions
|   |-- job_repository.php      # Job loading, creation, and update helpers
|   |-- jobs.php                # Seed job data
|   `-- skill_tests.php         # Question banks and test-result helpers
|-- assets/
|   |-- css/style.css           # Custom styles
|   `-- js/
|       |-- app.js              # Job filtering and counter interactions
|       `-- skill-test.js       # Skill-test timer
`-- data/
    |-- users.json              # Registered users and password hashes
    |-- jobs.json               # Active job listings
    |-- applications.json       # Submitted applications
    `-- skill_tests.json        # Created automatically after a test attempt
```

## Data and reset behavior

The JSON files under `data/` are runtime data and may contain local test accounts and applications. To start with a clean instance, back up or remove the files you want to reset, then restart the application.

- Removing `data/users.json` lets the next registered account become admin.
- Removing `data/jobs.json` causes the application to recreate it from `includes/jobs.php` on the next request.
- Removing `data/applications.json` clears submitted applications.
- Removing `data/skill_tests.json` clears saved skill-test attempts.

Do not use this JSON storage approach for concurrent or production workloads without adding file locking, access controls, backups, and a proper database.

## Important implementation notes

- Passwords are stored with PHP's `password_hash()` and checked with `password_verify()`.
- Login regenerates the session ID.
- Protected recruiter and admin pages return a 403 response for unauthorized signed-in users.
- Resume input expects a URL; this project does not upload files.
- Bootstrap assets are fetched from a CDN, so an internet connection is needed for the full styled interface unless those assets are hosted locally.
- The `assets (1)`, `data (1)`, `includes (1)`, and `* (1).php` items are duplicate copies and are not used by the canonical application files.

## Suggested next steps for production

- Replace JSON storage with MySQL, PostgreSQL, or another managed database.
- Add CSRF protection and stricter server-side authorization checks for every mutation.
- Add rate limiting, account verification, password reset, and audit logs.
- Store uploaded resumes securely instead of accepting arbitrary URLs.
- Add automated tests and environment-based configuration.

## License

No license file is currently included. Add one before distributing or open-sourcing the project.
