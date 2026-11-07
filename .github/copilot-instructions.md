# GitHub Copilot Instructions

## Review trigger policy

- Do not initiate, schedule, or request a pull request review on your own.
- Do not automatically start a review after a pull request is opened, updated, or receives new commits.
- Only review a pull request when the repository owner explicitly requests a Copilot review from the GitHub pull request UI.
- These instructions govern Copilot's behavior when it is invoked; they do not override GitHub account-level or repository ruleset settings that trigger automatic reviews.

## General engineering expectations

- Keep changes focused and preserve existing behavior unless the task explicitly requires a behavior change.
- Prefer small, readable PHP functions and reuse the existing helpers in `includes/`.
- Validate and authorize every state-changing request on the server; never rely only on browser-side checks.
- Escape untrusted values when rendering HTML and use safe JSON encoding/decoding patterns.
- Protect authentication, session, role, recruiter ownership, and administrator-only operations.
- Treat files under `data/` as private runtime data. Never commit real user records, credentials, tokens, or personal application data.
- Do not introduce a database, framework, Composer dependency, or build system unless explicitly requested.
- Avoid editing duplicate legacy copies such as `* (1).php`, `assets (1)/`, `data (1)/`, and `includes (1)/`; the canonical application uses the un-suffixed paths.
- Run PHP syntax checks and the relevant regression tests when PHP code changes.
- Report concrete, reproducible findings. Do not invent issues or request changes for stylistic preferences.
