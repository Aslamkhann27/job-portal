# Job Portal

A job portal built with HTML, CSS, JavaScript, PHP, and Bootstrap.

## Features

- Clean landing page with job overview
- Search and filter jobs by keyword, location, and category
- Job detail page with requirements and application form
- PHP form validation and application persistence in JSON
- User registration and login with session-based authentication
- Recruiter portal to create and manage jobs
- Admin dashboard to review submitted job applications
- Mini Skill Test system with timed quiz and instant scoring
- Per-job control for making Mini Skill Test compulsory or optional
- Per-job seat management and automatic seat-capacity checks
- Mobile-friendly layout

## Project Structure

- `index.php` - Home page and job listings
- `job.php` - Job details and apply form
- `apply.php` - Form submission and validation
- `skill_test.php` - Mini skill test page for each job
- `recruiter.php` - Recruiter portal for creating jobs
- `register.php` - User registration page
- `login.php` - User login page
- `logout.php` - Session logout endpoint
- `admin.php` - Admin dashboard for application management
- `includes/auth.php` - Authentication and session helpers
- `includes/skill_tests.php` - Skill test question bank and scoring logic
- `includes/job_repository.php` - Job storage and management helpers
- `includes/jobs.php` - Job data
- `assets/css/style.css` - Custom styling
- `assets/js/app.js` - Filtering and UI interactions
- `assets/js/skill-test.js` - Skill test countdown timer logic
- `data/applications.json` - Stored applications (auto-generated)
- `data/skill_tests.json` - Stored skill test attempts (auto-generated)
- `data/users.json` - Registered users (auto-generated)

## Auth Notes

- The first registered account is automatically promoted to `admin`.
- Only logged-in users can submit applications.
- Admin users can access `admin.php` to review all applications.
- Recruiter users can access `recruiter.php` and create jobs.
- Exactly one admin account is supported.
- Recruiters can create jobs only (with seats and Mini Skill Test policy).
- Admin can remove jobs and remove seeker accounts.
- Recruiter sets whether Mini Skill Test is required for each created job.

## Project Note

- This project is for learning and practical implementation.
- Data is stored in local JSON files.
- You can extend it with database integration and stronger security.

## Run Locally

Use PHP built-in server:

```bash
php -S localhost:8000
```

Then open:

- `http://localhost:8000/index.php`
