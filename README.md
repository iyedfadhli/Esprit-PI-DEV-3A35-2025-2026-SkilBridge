# SKILLBRIDGE - Academic Web Application
## Overview
This project was developed as part of the PIDEV - 3rd Year Engineering Program at **Esprit School of Engineering** (Academic Year 2025-2026).
It is a full-stack web platform that includes social feed and group management, AI-powered moderation, challenge and quiz workflows, CV and offer management, and back-office administration.

## Features
- User and role-based access (frontoffice, student, backoffice/admin flows)
- Groups, memberships, posts, comments, reactions, and reporting
- Multi-layer content moderation (Neutrino, Perspective, and fight/image moderation microservice)
- Challenge, activity, evaluation, and certificate workflows
- Quiz attempts, timer logic, and AI-generated feedback APIs
- CV creation, translation, and PDF export
- Offers, applications, and sponsor-related management
- Notifications and Mercure-based real-time features
- Stripe integration for payment-related flows

## Tech Stack
### Frontend
- Twig templates
- Symfony UX (Turbo, Stimulus)
- JavaScript and Asset Mapper
- Bootstrap-based UI components

### Backend
- PHP 8.1+ (commonly run with PHP 8.2 in local setup)
- Symfony 6.4
- Doctrine ORM + Doctrine Migrations
- MySQL (XAMPP local environment)
- Docker (fight moderation microservice)
- External APIs: Perspective, Neutrino, Google Translate, Stripe, Mercure

## Architecture
- Monolithic Symfony application structured by domain modules (controllers, entities, services, repositories, templates)
- Dedicated service layer for moderation, translation, AI feedback, notifications, and integrations
- Doctrine-based persistence with relational schema and migration support
- Complementary containerized microservice for image/fight moderation integrated via HTTP endpoint
- Separation between frontoffice and backoffice navigation flows

## Contributors
- PIDEV student team contributors (3rd Year Engineering)
- Mohamed Iyed Fadhli
- Tasnym Sammoud
- Nouh Mezened
- Takoua Btayeb
- Mohamed Salim Ben Hamouda
- Oumayma Barhoumi 
- Module-based collaboration across social, challenge, CV, and administration features

## Academic Context
Developed at **Esprit School of Engineering - Tunisia**  
PIDEV - 3A | 2025-2026

## Getting Started
1. Clone the repository and open it locally.
2. Install PHP dependencies with Composer.
3. Configure environment variables in `.env.local` (database, API keys, moderation endpoints).
4. Create/update the database schema (migrations or schema update depending on local state).
5. Start the Symfony app and required services.
6. Start Docker service for image moderation when needed.

## Acknowledgments
- **Esprit School of Engineering** for the academic framework and project supervision
- Open-source ecosystems used in this project: Symfony, Doctrine, Twig, Docker, and related integrations
