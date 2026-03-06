# Course Management Branch

## Description
This branch covers course publishing and learning support, including recommendation-oriented logic and course-level interactions for students.

## Resources Used
CourseManager and recommendation services, Symfony controllers/templates, Doctrine entities/repositories for course data, MySQL, validation and pagination helpers.

## How It Works
Supervisors publish and maintain course content while students browse, filter, and access learning material. Recommendation logic personalizes course visibility based on profile/activity context so learners can discover relevant content faster.

## Setup (Local)
1. Run composer install.
2. Configure .env.local (database and required API keys).
3. Run database migrations.
4. Start Symfony server and required side services (if used by the branch).

## Notes
- This README is branch-specific and should stay aligned with the module scope of $name.
