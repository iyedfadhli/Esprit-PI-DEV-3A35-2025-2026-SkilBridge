<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227062343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, submission_file VARCHAR(255) DEFAULT NULL, submission_date DATETIME DEFAULT NULL, status VARCHAR(30) DEFAULT NULL, id_challenge_id INT NOT NULL, group_id_id INT NOT NULL, INDEX IDX_AC74095ABB636FB4 (id_challenge_id), INDEX IDX_AC74095A2F68B530 (group_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE answer (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, is_correct TINYINT NOT NULL, question_id INT NOT NULL, INDEX IDX_DADD4A251E27F6BF (question_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE certif (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, issued_by VARCHAR(30) NOT NULL, issue_date DATE NOT NULL, exp_date DATE NOT NULL, cv_id INT NOT NULL, INDEX IDX_EC509872CFE419E2 (cv_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE challenge (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, target_skill VARCHAR(30) NOT NULL, difficulty VARCHAR(30) NOT NULL, min_group_nbr INT NOT NULL, max_group_nbr INT NOT NULL, dead_line DATETIME NOT NULL, created_at DATETIME NOT NULL, content VARCHAR(255) DEFAULT NULL, creator_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_D709895161220EA6 (creator_id), INDEX IDX_D7098951591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE chapter (id INT AUTO_INCREMENT NOT NULL, chapter_order INT NOT NULL, status VARCHAR(30) NOT NULL, min_score DOUBLE PRECISION NOT NULL, content VARCHAR(255) NOT NULL, title VARCHAR(30) NOT NULL, course_id INT NOT NULL, INDEX IDX_F981B52E591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE commentaires (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, likes INT NOT NULL, author_id_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_D9BEC0C469CCBE9A (author_id_id), INDEX IDX_D9BEC0C44B89032C (post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, duration INT NOT NULL, difficulty VARCHAR(20) DEFAULT \'BEGINNER\' NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, validation_score DOUBLE PRECISION NOT NULL, content VARCHAR(255) NOT NULL, material VARCHAR(255) DEFAULT NULL, sections_to_review JSON DEFAULT NULL, creator_id INT NOT NULL, prerequisite_quiz_id INT DEFAULT NULL, INDEX IDX_169E6FB961220EA6 (creator_id), INDEX IDX_169E6FB9618C39EB (prerequisite_quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE cv (id INT AUTO_INCREMENT NOT NULL, nom_cv VARCHAR(30) NOT NULL, langue VARCHAR(30) NOT NULL, id_template INT DEFAULT NULL, progression INT DEFAULT NULL, creation_date DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, linkedin_url VARCHAR(255) DEFAULT NULL, summary LONGTEXT DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_B66FFE92A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE cv_application (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(30) NOT NULL, applied_at DATETIME NOT NULL, ats_score INT DEFAULT NULL, skills_score INT DEFAULT NULL, experience_score INT DEFAULT NULL, education_score INT DEFAULT NULL, matched_skills JSON DEFAULT NULL, missing_skills JSON DEFAULT NULL, ai_score INT DEFAULT NULL, cv_id INT NOT NULL, offer_id INT NOT NULL, INDEX IDX_BA25D93CCFE419E2 (cv_id), INDEX IDX_BA25D93C53C674EE (offer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE education (id INT AUTO_INCREMENT NOT NULL, degree VARCHAR(30) NOT NULL, field_of_study VARCHAR(40) DEFAULT NULL, school VARCHAR(30) NOT NULL, city VARCHAR(40) DEFAULT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, description LONGTEXT DEFAULT NULL, cv_id INT NOT NULL, INDEX IDX_DB0A5ED2CFE419E2 (cv_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE email_verification (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, code VARCHAR(6) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE enrollement (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(30) NOT NULL, progress INT NOT NULL, score DOUBLE PRECISION DEFAULT NULL, completed_at DATE NOT NULL, student_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_1B002285CB944F1A (student_id), INDEX IDX_1B002285591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE evaluation (id INT AUTO_INCREMENT NOT NULL, group_score DOUBLE PRECISION DEFAULT NULL, feedback VARCHAR(255) DEFAULT NULL, pre_feedback LONGTEXT DEFAULT NULL, activity_id_id INT NOT NULL, UNIQUE INDEX UNIQ_1323A5756146A8E4 (activity_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE experience (id INT AUTO_INCREMENT NOT NULL, job_title VARCHAR(30) NOT NULL, company VARCHAR(30) NOT NULL, location VARCHAR(255) DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, currently_working TINYINT NOT NULL, description LONGTEXT NOT NULL, cv_id INT NOT NULL, INDEX IDX_590C103CFE419E2 (cv_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(40) NOT NULL, description LONGTEXT NOT NULL, creation_date DATETIME NOT NULL, type VARCHAR(20) NOT NULL, level VARCHAR(20) NOT NULL, max_members INT NOT NULL, rating_score DOUBLE PRECISION NOT NULL, icon VARCHAR(255) NOT NULL, leader_id_id INT NOT NULL, INDEX IDX_6DC044C5EFE6DECF (leader_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE hackathon (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, theme VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, rules LONGTEXT NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, registration_open_at DATETIME NOT NULL, registration_close_at DATETIME NOT NULL, fee DOUBLE PRECISION NOT NULL, max_teams INT NOT NULL, team_size_max INT NOT NULL, location VARCHAR(255) NOT NULL, cover_url VARCHAR(255) NOT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, creator_id_id INT NOT NULL, INDEX IDX_8B3AF64FF05788E9 (creator_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE langue (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(30) NOT NULL, niveau VARCHAR(30) NOT NULL, cv_id INT NOT NULL, INDEX IDX_9357758ECFE419E2 (cv_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE lessons_learned (id INT AUTO_INCREMENT NOT NULL, lesson_description LONGTEXT NOT NULL, id_activity_id INT NOT NULL, INDEX IDX_A4F157B699D2AD61 (id_activity_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE member_activity (id INT AUTO_INCREMENT NOT NULL, activity_description LONGTEXT NOT NULL, indiv_score DOUBLE PRECISION DEFAULT NULL, id_activity_id INT NOT NULL, user_id_id INT NOT NULL, INDEX IDX_5BA9AAB099D2AD61 (id_activity_id), INDEX IDX_5BA9AAB09D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE membership (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(20) NOT NULL, contribution_score DOUBLE PRECISION NOT NULL, achievement_unlocked VARCHAR(255) DEFAULT NULL, is_active TINYINT NOT NULL, user_id_id INT NOT NULL, group_id_id INT NOT NULL, INDEX IDX_86FFD2859D86650F (user_id_id), INDEX IDX_86FFD2852F68B530 (group_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, sender_id INT NOT NULL, group_id_id INT NOT NULL, INDEX IDX_B6BD307FF624B39D (sender_id), INDEX IDX_B6BD307F2F68B530 (group_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE notif (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_C0730D6BA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, is_read TINYINT NOT NULL, data JSON NOT NULL, created_at DATETIME NOT NULL, owner_id INT NOT NULL, INDEX IDX_BF5476CA7E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE offer (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, offer_type VARCHAR(30) NOT NULL, field VARCHAR(30) NOT NULL, required_level VARCHAR(30) NOT NULL, required_skills LONGTEXT NOT NULL, location VARCHAR(40) NOT NULL, contract_type VARCHAR(40) NOT NULL, duration INT DEFAULT NULL, salary_range DOUBLE PRECISION DEFAULT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, entreprise_id INT NOT NULL, INDEX IDX_29D6873EA4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(30) NOT NULL, payment_status VARCHAR(30) NOT NULL, payment_ref VARCHAR(30) NOT NULL, registred_at DATETIME NOT NULL, hackathon_id INT NOT NULL, group_id_id INT NOT NULL, INDEX IDX_AB55E24F996D90CF (hackathon_id), INDEX IDX_AB55E24F2F68B530 (group_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE posts (id INT AUTO_INCREMENT NOT NULL, description LONGTEXT NOT NULL, titre VARCHAR(30) NOT NULL, status VARCHAR(30) NOT NULL, visibility VARCHAR(30) NOT NULL, attached_file VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, likes_counter INT NOT NULL, group_id_id INT DEFAULT NULL, author_id_id INT NOT NULL, INDEX IDX_885DBAFA2F68B530 (group_id_id), INDEX IDX_885DBAFA69CCBE9A (author_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE problem_solution (id INT AUTO_INCREMENT NOT NULL, problem_description LONGTEXT NOT NULL, group_solution LONGTEXT DEFAULT NULL, supervisor_solution LONGTEXT DEFAULT NULL, activity_id_id INT NOT NULL, INDEX IDX_56D92B9D6146A8E4 (activity_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, point DOUBLE PRECISION NOT NULL, quiz_id INT NOT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, passing_score DOUBLE PRECISION NOT NULL, max_attempts INT NOT NULL, questions_per_attempt INT DEFAULT NULL, time_limit INT DEFAULT 0 NOT NULL, course_id INT NOT NULL, chapter_id INT DEFAULT NULL, supervisor_id INT NOT NULL, INDEX IDX_A412FA92591CC992 (course_id), UNIQUE INDEX UNIQ_A412FA92579F4768 (chapter_id), INDEX IDX_A412FA9219E9AC5F (supervisor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE quiz_attempts (id INT AUTO_INCREMENT NOT NULL, attempt_nbr INT NOT NULL, score DOUBLE PRECISION NOT NULL, submitted_at DATETIME NOT NULL, started_at DATETIME DEFAULT NULL, status VARCHAR(20) DEFAULT \'IN_PROGRESS\' NOT NULL, answers_json JSON DEFAULT NULL, student_id INT NOT NULL, quiz_id INT NOT NULL, INDEX IDX_69031E21CB944F1A (student_id), INDEX IDX_69031E21853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reactions (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(25) NOT NULL, url VARCHAR(255) NOT NULL, posted_at DATETIME NOT NULL, user_id_id INT NOT NULL, post_id_id INT NOT NULL, INDEX IDX_38737FB39D86650F (user_id_id), INDEX IDX_38737FB3E85F12B8 (post_id_id), UNIQUE INDEX uniq_user_post_type (user_id_id, post_id_id, type), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE skill (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(35) NOT NULL, type VARCHAR(20) NOT NULL, level VARCHAR(30) NOT NULL, cv_id INT NOT NULL, INDEX IDX_5E3DE477CFE419E2 (cv_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE sponsor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, logo_url VARCHAR(255) NOT NULL, website_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, creator_id_id INT NOT NULL, INDEX IDX_818CC9D4F05788E9 (creator_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE sponsor_hackathon (id INT AUTO_INCREMENT NOT NULL, contribution_type VARCHAR(30) NOT NULL, contribution_value DOUBLE PRECISION DEFAULT NULL, sponsor_id INT NOT NULL, hackathon_id INT NOT NULL, INDEX IDX_AECDBCA112F7FB51 (sponsor_id), INDEX IDX_AECDBCA1996D90CF (hackathon_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE student_response (id INT AUTO_INCREMENT NOT NULL, is_correct TINYINT NOT NULL, points_earned DOUBLE PRECISION NOT NULL, attempt_id INT NOT NULL, question_id INT NOT NULL, selected_answer_id INT NOT NULL, INDEX IDX_8DF04760B191BE6B (attempt_id), INDEX IDX_8DF047601E27F6BF (question_id), INDEX IDX_8DF04760F24C5BEC (selected_answer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(30) NOT NULL, prenom VARCHAR(30) DEFAULT NULL, date_naissance DATE DEFAULT NULL, email VARCHAR(180) NOT NULL, ban TINYINT DEFAULT 0 NOT NULL, photo VARCHAR(255) DEFAULT NULL, passwd VARCHAR(255) NOT NULL, date_inscrit DATETIME NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, report_nbr INT DEFAULT 0 NOT NULL, previous_role VARCHAR(30) DEFAULT NULL, banned_until DATETIME DEFAULT NULL, archived TINYINT DEFAULT 0 NOT NULL, webauthn_credential_id VARCHAR(500) DEFAULT NULL, webauthn_public_key LONGTEXT DEFAULT NULL, face_descriptor LONGTEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, experience LONGTEXT DEFAULT NULL, education VARCHAR(255) DEFAULT NULL, skills LONGTEXT DEFAULT NULL, score_generale INT DEFAULT NULL, domaine VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095ABB636FB4 FOREIGN KEY (id_challenge_id) REFERENCES challenge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A2F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE certif ADD CONSTRAINT FK_EC509872CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE challenge ADD CONSTRAINT FK_D709895161220EA6 FOREIGN KEY (creator_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE challenge ADD CONSTRAINT FK_D7098951591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C469CCBE9A FOREIGN KEY (author_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C44B89032C FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB961220EA6 FOREIGN KEY (creator_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9618C39EB FOREIGN KEY (prerequisite_quiz_id) REFERENCES quiz (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE92A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cv_application ADD CONSTRAINT FK_BA25D93CCFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cv_application ADD CONSTRAINT FK_BA25D93C53C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED2CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE enrollement ADD CONSTRAINT FK_1B002285CB944F1A FOREIGN KEY (student_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE enrollement ADD CONSTRAINT FK_1B002285591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A5756146A8E4 FOREIGN KEY (activity_id_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C103CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C5EFE6DECF FOREIGN KEY (leader_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hackathon ADD CONSTRAINT FK_8B3AF64FF05788E9 FOREIGN KEY (creator_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE langue ADD CONSTRAINT FK_9357758ECFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lessons_learned ADD CONSTRAINT FK_A4F157B699D2AD61 FOREIGN KEY (id_activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE member_activity ADD CONSTRAINT FK_5BA9AAB099D2AD61 FOREIGN KEY (id_activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE member_activity ADD CONSTRAINT FK_5BA9AAB09D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD2859D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD2852F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F2F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notif ADD CONSTRAINT FK_C0730D6BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873EA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F996D90CF FOREIGN KEY (hackathon_id) REFERENCES hackathon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F2F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFA2F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFA69CCBE9A FOREIGN KEY (author_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE problem_solution ADD CONSTRAINT FK_56D92B9D6146A8E4 FOREIGN KEY (activity_id_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA9219E9AC5F FOREIGN KEY (supervisor_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_attempts ADD CONSTRAINT FK_69031E21CB944F1A FOREIGN KEY (student_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_attempts ADD CONSTRAINT FK_69031E21853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB39D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3E85F12B8 FOREIGN KEY (post_id_id) REFERENCES posts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE477CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sponsor ADD CONSTRAINT FK_818CC9D4F05788E9 FOREIGN KEY (creator_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sponsor_hackathon ADD CONSTRAINT FK_AECDBCA112F7FB51 FOREIGN KEY (sponsor_id) REFERENCES sponsor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sponsor_hackathon ADD CONSTRAINT FK_AECDBCA1996D90CF FOREIGN KEY (hackathon_id) REFERENCES hackathon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_response ADD CONSTRAINT FK_8DF04760B191BE6B FOREIGN KEY (attempt_id) REFERENCES quiz_attempts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_response ADD CONSTRAINT FK_8DF047601E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_response ADD CONSTRAINT FK_8DF04760F24C5BEC FOREIGN KEY (selected_answer_id) REFERENCES answer (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095ABB636FB4');
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095A2F68B530');
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE certif DROP FOREIGN KEY FK_EC509872CFE419E2');
        $this->addSql('ALTER TABLE challenge DROP FOREIGN KEY FK_D709895161220EA6');
        $this->addSql('ALTER TABLE challenge DROP FOREIGN KEY FK_D7098951591CC992');
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52E591CC992');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C469CCBE9A');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C44B89032C');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB961220EA6');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9618C39EB');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY FK_B66FFE92A76ED395');
        $this->addSql('ALTER TABLE cv_application DROP FOREIGN KEY FK_BA25D93CCFE419E2');
        $this->addSql('ALTER TABLE cv_application DROP FOREIGN KEY FK_BA25D93C53C674EE');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED2CFE419E2');
        $this->addSql('ALTER TABLE enrollement DROP FOREIGN KEY FK_1B002285CB944F1A');
        $this->addSql('ALTER TABLE enrollement DROP FOREIGN KEY FK_1B002285591CC992');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A5756146A8E4');
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY FK_590C103CFE419E2');
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C5EFE6DECF');
        $this->addSql('ALTER TABLE hackathon DROP FOREIGN KEY FK_8B3AF64FF05788E9');
        $this->addSql('ALTER TABLE langue DROP FOREIGN KEY FK_9357758ECFE419E2');
        $this->addSql('ALTER TABLE lessons_learned DROP FOREIGN KEY FK_A4F157B699D2AD61');
        $this->addSql('ALTER TABLE member_activity DROP FOREIGN KEY FK_5BA9AAB099D2AD61');
        $this->addSql('ALTER TABLE member_activity DROP FOREIGN KEY FK_5BA9AAB09D86650F');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD2859D86650F');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD2852F68B530');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F2F68B530');
        $this->addSql('ALTER TABLE notif DROP FOREIGN KEY FK_C0730D6BA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA7E3C61F9');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873EA4AEAFEA');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F996D90CF');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F2F68B530');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFA2F68B530');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFA69CCBE9A');
        $this->addSql('ALTER TABLE problem_solution DROP FOREIGN KEY FK_56D92B9D6146A8E4');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92591CC992');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92579F4768');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA9219E9AC5F');
        $this->addSql('ALTER TABLE quiz_attempts DROP FOREIGN KEY FK_69031E21CB944F1A');
        $this->addSql('ALTER TABLE quiz_attempts DROP FOREIGN KEY FK_69031E21853CD175');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB39D86650F');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3E85F12B8');
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE477CFE419E2');
        $this->addSql('ALTER TABLE sponsor DROP FOREIGN KEY FK_818CC9D4F05788E9');
        $this->addSql('ALTER TABLE sponsor_hackathon DROP FOREIGN KEY FK_AECDBCA112F7FB51');
        $this->addSql('ALTER TABLE sponsor_hackathon DROP FOREIGN KEY FK_AECDBCA1996D90CF');
        $this->addSql('ALTER TABLE student_response DROP FOREIGN KEY FK_8DF04760B191BE6B');
        $this->addSql('ALTER TABLE student_response DROP FOREIGN KEY FK_8DF047601E27F6BF');
        $this->addSql('ALTER TABLE student_response DROP FOREIGN KEY FK_8DF04760F24C5BEC');
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE certif');
        $this->addSql('DROP TABLE challenge');
        $this->addSql('DROP TABLE chapter');
        $this->addSql('DROP TABLE commentaires');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE cv');
        $this->addSql('DROP TABLE cv_application');
        $this->addSql('DROP TABLE education');
        $this->addSql('DROP TABLE email_verification');
        $this->addSql('DROP TABLE enrollement');
        $this->addSql('DROP TABLE evaluation');
        $this->addSql('DROP TABLE experience');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE hackathon');
        $this->addSql('DROP TABLE langue');
        $this->addSql('DROP TABLE lessons_learned');
        $this->addSql('DROP TABLE member_activity');
        $this->addSql('DROP TABLE membership');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notif');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE offer');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE posts');
        $this->addSql('DROP TABLE problem_solution');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE quiz_attempts');
        $this->addSql('DROP TABLE reactions');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE sponsor');
        $this->addSql('DROP TABLE sponsor_hackathon');
        $this->addSql('DROP TABLE student_response');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
