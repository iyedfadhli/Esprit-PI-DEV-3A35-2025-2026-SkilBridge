# Feed Management Branch

## Description
This branch focuses on the social feed module where users publish posts, react, comment, and report content. The goal is to provide a moderated and interactive community space with fast content flows and role-aware permissions.

## Resources Used
Symfony 6.4 controllers/services, Doctrine entities (Posts, Commentaires, reactions/reporting), Twig frontoffice pages, moderation integrations (Neutrino/Perspective/fight moderation service), MySQL persistence.

## How It Works
A student publishes a post with optional media, other users react and comment, and reporting/moderation checks run to keep content safe. The service layer applies validation and moderation logic before storing or displaying content. Supervisors/admins can audit flagged posts and enforce policy decisions.

## Setup (Local)
1. Run composer install.
2. Configure .env.local (database and required API keys).
3. Run database migrations.
4. Start Symfony server and required side services (if used by the branch).

## Notes
- This README is branch-specific and should stay aligned with the module scope of $name.
