# CV Module Branch

## Description
This branch manages CV creation, structuring, and export workflows so students can build portfolio-ready resumes directly inside the platform.

## Resources Used
CvManager service, translation integration (Google Translate), PDF generation tools (dompdf/knp-snappy), Symfony forms/Twig templates, Doctrine persistence.

## How It Works
Students create or update CV sections (education, skills, projects, etc.), optionally translate content, and export final CV documents to PDF format. The module centralizes profile data and automates formatting for cleaner application-ready output.

## Setup (Local)
1. Run composer install.
2. Configure .env.local (database and required API keys).
3. Run database migrations.
4. Start Symfony server and required side services (if used by the branch).

## Notes
- This README is branch-specific and should stay aligned with the module scope of $name.
