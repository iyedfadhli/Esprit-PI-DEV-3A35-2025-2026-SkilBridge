# Challenge Module Branch

## Description
This branch implements the challenge workflow with three core domains: Challenge, Activity, and Evaluation. Two student roles are supported: Supervisor and Student.

## Resources Used
Symfony domain services (ChallengeManager/Evaluation logic), Doctrine entities for challenges/activities/evaluations, PDF parsing/feedback services, Twig frontoffice pages, MySQL, PHPUnit service tests.

## How It Works
A supervisor publishes a challenge, students select a challenge and one of their groups to participate, and groups submit their PDF deliverable when work is completed. The platform generates automatic pre-feedback with an initial score and improvement ideas. After that, the supervisor reviews the submission and assigns both an individual score and a group score. Final evaluations are published to the group dashboard; passing groups receive a certificate and each member can view their individual ranking in the project.

## Setup (Local)
1. Run composer install.
2. Configure .env.local (database and required API keys).
3. Run database migrations.
4. Start Symfony server and required side services (if used by the branch).

## Notes
- This README is branch-specific and should stay aligned with the module scope of $name.
