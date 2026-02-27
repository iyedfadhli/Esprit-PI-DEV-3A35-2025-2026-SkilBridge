<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227051204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE email_verification (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, code VARCHAR(6) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE notif (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_C0730D6BA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE student_response (id INT AUTO_INCREMENT NOT NULL, is_correct TINYINT NOT NULL, points_earned DOUBLE PRECISION NOT NULL, attempt_id INT NOT NULL, question_id INT NOT NULL, selected_answer_id INT NOT NULL, INDEX IDX_8DF04760B191BE6B (attempt_id), INDEX IDX_8DF047601E27F6BF (question_id), INDEX IDX_8DF04760F24C5BEC (selected_answer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE notif ADD CONSTRAINT FK_C0730D6BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_response ADD CONSTRAINT FK_8DF04760B191BE6B FOREIGN KEY (attempt_id) REFERENCES quiz_attempts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_response ADD CONSTRAINT FK_8DF047601E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_response ADD CONSTRAINT FK_8DF04760F24C5BEC FOREIGN KEY (selected_answer_id) REFERENCES answer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity DROP INDEX UNIQ_AC74095A2F68B530, ADD INDEX IDX_AC74095A2F68B530 (group_id_id)');
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY `FK_AC74095ABB636FB4`');
        $this->addSql('ALTER TABLE activity ADD status VARCHAR(30) DEFAULT NULL, CHANGE submission_file submission_file VARCHAR(255) DEFAULT NULL, CHANGE submission_date submission_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095ABB636FB4 FOREIGN KEY (id_challenge_id) REFERENCES challenge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY `FK_DADD4A251E27F6BF`');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE certif DROP FOREIGN KEY `FK_EC509872CFE419E2`');
        $this->addSql('ALTER TABLE certif ADD CONSTRAINT FK_EC509872CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE challenge DROP FOREIGN KEY `FK_D709895161220EA6`');
        $this->addSql('ALTER TABLE challenge ADD course_id INT NOT NULL');
        $this->addSql('ALTER TABLE challenge ADD CONSTRAINT FK_D709895161220EA6 FOREIGN KEY (creator_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE challenge ADD CONSTRAINT FK_D7098951591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D7098951591CC992 ON challenge (course_id)');
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY `FK_F981B52E591CC992`');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY `FK_D9BEC0C469CCBE9A`');
        $this->addSql('ALTER TABLE commentaires ADD post_id INT NOT NULL');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C469CCBE9A FOREIGN KEY (author_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C44B89032C FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D9BEC0C44B89032C ON commentaires (post_id)');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY `FK_169E6FB961220EA6`');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY `FK_169E6FB9618C39EB`');
        $this->addSql('ALTER TABLE course ADD difficulty VARCHAR(20) DEFAULT \'BEGINNER\' NOT NULL, ADD material VARCHAR(255) DEFAULT NULL, DROP display_order, CHANGE is_active is_active TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB961220EA6 FOREIGN KEY (creator_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9618C39EB FOREIGN KEY (prerequisite_quiz_id) REFERENCES quiz (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cv_application DROP FOREIGN KEY `FK_BA25D93C53C674EE`');
        $this->addSql('ALTER TABLE cv_application DROP FOREIGN KEY `FK_BA25D93CCFE419E2`');
        $this->addSql('ALTER TABLE cv_application ADD ats_score INT DEFAULT NULL, ADD skills_score INT DEFAULT NULL, ADD experience_score INT DEFAULT NULL, ADD education_score INT DEFAULT NULL, ADD matched_skills JSON DEFAULT NULL, ADD missing_skills JSON DEFAULT NULL, ADD ai_score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cv_application ADD CONSTRAINT FK_BA25D93C53C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cv_application ADD CONSTRAINT FK_BA25D93CCFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY `FK_DB0A5ED2CFE419E2`');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT FK_DB0A5ED2CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE enrollement DROP FOREIGN KEY `FK_1B002285591CC992`');
        $this->addSql('ALTER TABLE enrollement DROP FOREIGN KEY `FK_1B002285CB944F1A`');
        $this->addSql('ALTER TABLE enrollement ADD CONSTRAINT FK_1B002285591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE enrollement ADD CONSTRAINT FK_1B002285CB944F1A FOREIGN KEY (student_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY `FK_590C103CFE419E2`');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C103CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY `FK_6DC044C5EFE6DECF`');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C5EFE6DECF FOREIGN KEY (leader_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hackathon DROP FOREIGN KEY `FK_8B3AF64FF05788E9`');
        $this->addSql('ALTER TABLE hackathon ADD CONSTRAINT FK_8B3AF64FF05788E9 FOREIGN KEY (creator_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE langue DROP FOREIGN KEY `FK_9357758ECFE419E2`');
        $this->addSql('ALTER TABLE langue ADD CONSTRAINT FK_9357758ECFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lessons_learned DROP FOREIGN KEY `FK_A4F157B699D2AD61`');
        $this->addSql('ALTER TABLE lessons_learned ADD CONSTRAINT FK_A4F157B699D2AD61 FOREIGN KEY (id_activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE member_activity DROP FOREIGN KEY `FK_5BA9AAB099D2AD61`');
        $this->addSql('ALTER TABLE member_activity DROP FOREIGN KEY `FK_5BA9AAB09D86650F`');
        $this->addSql('ALTER TABLE member_activity CHANGE indiv_score indiv_score DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE member_activity ADD CONSTRAINT FK_5BA9AAB099D2AD61 FOREIGN KEY (id_activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE member_activity ADD CONSTRAINT FK_5BA9AAB09D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY `FK_86FFD2852F68B530`');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY `FK_86FFD2859D86650F`');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD2852F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD2859D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_B6BD307F2F68B530`');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_B6BD307FF624B39D`');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F2F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY `FK_29D6873EA4AEAFEA`');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873EA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY `FK_AB55E24F2F68B530`');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY `FK_AB55E24F996D90CF`');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F2F68B530 FOREIGN KEY (group_id_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F996D90CF FOREIGN KEY (hackathon_id) REFERENCES hackathon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY `FK_885DBAFA69CCBE9A`');
        $this->addSql('ALTER TABLE posts CHANGE group_id_id group_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFA69CCBE9A FOREIGN KEY (author_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE problem_solution DROP FOREIGN KEY `FK_56D92B9D6146A8E4`');
        $this->addSql('ALTER TABLE problem_solution CHANGE supervisor_solution supervisor_solution LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE problem_solution ADD CONSTRAINT FK_56D92B9D6146A8E4 FOREIGN KEY (activity_id_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY `FK_B6F7494E853CD175`');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz DROP INDEX UNIQ_A412FA92591CC992, ADD INDEX IDX_A412FA92591CC992 (course_id)');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY `FK_A412FA9219E9AC5F`');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY `FK_A412FA92579F4768`');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY `FK_A412FA92591CC992`');
        $this->addSql('ALTER TABLE quiz ADD time_limit INT DEFAULT 0 NOT NULL, CHANGE chapter_id chapter_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA9219E9AC5F FOREIGN KEY (supervisor_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_attempts DROP FOREIGN KEY `FK_69031E21853CD175`');
        $this->addSql('ALTER TABLE quiz_attempts DROP FOREIGN KEY `FK_69031E21CB944F1A`');
        $this->addSql('ALTER TABLE quiz_attempts ADD started_at DATETIME DEFAULT NULL, ADD status VARCHAR(20) DEFAULT \'IN_PROGRESS\' NOT NULL, ADD answers_json JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz_attempts ADD CONSTRAINT FK_69031E21853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_attempts ADD CONSTRAINT FK_69031E21CB944F1A FOREIGN KEY (student_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reactions DROP INDEX UNIQ_38737FB39D86650F, ADD INDEX IDX_38737FB39D86650F (user_id_id)');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY `FK_38737FB3E85F12B8`');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3E85F12B8 FOREIGN KEY (post_id_id) REFERENCES posts (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_post_type ON reactions (user_id_id, post_id_id, type)');
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY `FK_5E3DE477CFE419E2`');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE477CFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sponsor DROP FOREIGN KEY `FK_818CC9D4F05788E9`');
        $this->addSql('ALTER TABLE sponsor ADD CONSTRAINT FK_818CC9D4F05788E9 FOREIGN KEY (creator_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sponsor_hackathon DROP FOREIGN KEY `FK_AECDBCA112F7FB51`');
        $this->addSql('ALTER TABLE sponsor_hackathon DROP FOREIGN KEY `FK_AECDBCA1996D90CF`');
        $this->addSql('ALTER TABLE sponsor_hackathon ADD CONSTRAINT FK_AECDBCA112F7FB51 FOREIGN KEY (sponsor_id) REFERENCES sponsor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sponsor_hackathon ADD CONSTRAINT FK_AECDBCA1996D90CF FOREIGN KEY (hackathon_id) REFERENCES hackathon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD report_nbr INT DEFAULT 0 NOT NULL, ADD previous_role VARCHAR(30) DEFAULT NULL, ADD banned_until DATETIME DEFAULT NULL, ADD archived TINYINT DEFAULT 0 NOT NULL, ADD webauthn_credential_id VARCHAR(500) DEFAULT NULL, ADD webauthn_public_key LONGTEXT DEFAULT NULL, ADD face_descriptor LONGTEXT DEFAULT NULL, CHANGE prenom prenom VARCHAR(30) DEFAULT NULL, CHANGE date_naissance date_naissance DATE DEFAULT NULL, CHANGE email email VARCHAR(180) NOT NULL, CHANGE ban ban TINYINT DEFAULT 0 NOT NULL, CHANGE is_active is_active TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notif DROP FOREIGN KEY FK_C0730D6BA76ED395');
        $this->addSql('ALTER TABLE student_response DROP FOREIGN KEY FK_8DF04760B191BE6B');
        $this->addSql('ALTER TABLE student_response DROP FOREIGN KEY FK_8DF047601E27F6BF');
        $this->addSql('ALTER TABLE student_response DROP FOREIGN KEY FK_8DF04760F24C5BEC');
        $this->addSql('DROP TABLE email_verification');
        $this->addSql('DROP TABLE notif');
        $this->addSql('DROP TABLE student_response');
        $this->addSql('ALTER TABLE activity DROP INDEX IDX_AC74095A2F68B530, ADD UNIQUE INDEX UNIQ_AC74095A2F68B530 (group_id_id)');
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095ABB636FB4');
        $this->addSql('ALTER TABLE activity DROP status, CHANGE submission_file submission_file VARCHAR(255) NOT NULL, CHANGE submission_date submission_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT `FK_AC74095ABB636FB4` FOREIGN KEY (id_challenge_id) REFERENCES challenge (id)');
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT `FK_DADD4A251E27F6BF` FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE certif DROP FOREIGN KEY FK_EC509872CFE419E2');
        $this->addSql('ALTER TABLE certif ADD CONSTRAINT `FK_EC509872CFE419E2` FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE challenge DROP FOREIGN KEY FK_D709895161220EA6');
        $this->addSql('ALTER TABLE challenge DROP FOREIGN KEY FK_D7098951591CC992');
        $this->addSql('DROP INDEX IDX_D7098951591CC992 ON challenge');
        $this->addSql('ALTER TABLE challenge DROP course_id');
        $this->addSql('ALTER TABLE challenge ADD CONSTRAINT `FK_D709895161220EA6` FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52E591CC992');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT `FK_F981B52E591CC992` FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C469CCBE9A');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C44B89032C');
        $this->addSql('DROP INDEX IDX_D9BEC0C44B89032C ON commentaires');
        $this->addSql('ALTER TABLE commentaires DROP post_id');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT `FK_D9BEC0C469CCBE9A` FOREIGN KEY (author_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB961220EA6');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9618C39EB');
        $this->addSql('ALTER TABLE course ADD display_order INT NOT NULL, DROP difficulty, DROP material, CHANGE is_active is_active TINYINT NOT NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT `FK_169E6FB961220EA6` FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT `FK_169E6FB9618C39EB` FOREIGN KEY (prerequisite_quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE cv_application DROP FOREIGN KEY FK_BA25D93CCFE419E2');
        $this->addSql('ALTER TABLE cv_application DROP FOREIGN KEY FK_BA25D93C53C674EE');
        $this->addSql('ALTER TABLE cv_application DROP ats_score, DROP skills_score, DROP experience_score, DROP education_score, DROP matched_skills, DROP missing_skills, DROP ai_score');
        $this->addSql('ALTER TABLE cv_application ADD CONSTRAINT `FK_BA25D93CCFE419E2` FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE cv_application ADD CONSTRAINT `FK_BA25D93C53C674EE` FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE education DROP FOREIGN KEY FK_DB0A5ED2CFE419E2');
        $this->addSql('ALTER TABLE education ADD CONSTRAINT `FK_DB0A5ED2CFE419E2` FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE enrollement DROP FOREIGN KEY FK_1B002285CB944F1A');
        $this->addSql('ALTER TABLE enrollement DROP FOREIGN KEY FK_1B002285591CC992');
        $this->addSql('ALTER TABLE enrollement ADD CONSTRAINT `FK_1B002285CB944F1A` FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE enrollement ADD CONSTRAINT `FK_1B002285591CC992` FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY FK_590C103CFE419E2');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT `FK_590C103CFE419E2` FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C5EFE6DECF');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT `FK_6DC044C5EFE6DECF` FOREIGN KEY (leader_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE hackathon DROP FOREIGN KEY FK_8B3AF64FF05788E9');
        $this->addSql('ALTER TABLE hackathon ADD CONSTRAINT `FK_8B3AF64FF05788E9` FOREIGN KEY (creator_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE langue DROP FOREIGN KEY FK_9357758ECFE419E2');
        $this->addSql('ALTER TABLE langue ADD CONSTRAINT `FK_9357758ECFE419E2` FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE lessons_learned DROP FOREIGN KEY FK_A4F157B699D2AD61');
        $this->addSql('ALTER TABLE lessons_learned ADD CONSTRAINT `FK_A4F157B699D2AD61` FOREIGN KEY (id_activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD2859D86650F');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD2852F68B530');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT `FK_86FFD2859D86650F` FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT `FK_86FFD2852F68B530` FOREIGN KEY (group_id_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE member_activity DROP FOREIGN KEY FK_5BA9AAB099D2AD61');
        $this->addSql('ALTER TABLE member_activity DROP FOREIGN KEY FK_5BA9AAB09D86650F');
        $this->addSql('ALTER TABLE member_activity CHANGE indiv_score indiv_score DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE member_activity ADD CONSTRAINT `FK_5BA9AAB099D2AD61` FOREIGN KEY (id_activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE member_activity ADD CONSTRAINT `FK_5BA9AAB09D86650F` FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F2F68B530');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_B6BD307FF624B39D` FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_B6BD307F2F68B530` FOREIGN KEY (group_id_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873EA4AEAFEA');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT `FK_29D6873EA4AEAFEA` FOREIGN KEY (entreprise_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F996D90CF');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F2F68B530');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT `FK_AB55E24F996D90CF` FOREIGN KEY (hackathon_id) REFERENCES hackathon (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT `FK_AB55E24F2F68B530` FOREIGN KEY (group_id_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFA69CCBE9A');
        $this->addSql('ALTER TABLE posts CHANGE group_id_id group_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT `FK_885DBAFA69CCBE9A` FOREIGN KEY (author_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE problem_solution DROP FOREIGN KEY FK_56D92B9D6146A8E4');
        $this->addSql('ALTER TABLE problem_solution CHANGE supervisor_solution supervisor_solution LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE problem_solution ADD CONSTRAINT `FK_56D92B9D6146A8E4` FOREIGN KEY (activity_id_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT `FK_B6F7494E853CD175` FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz DROP INDEX IDX_A412FA92591CC992, ADD UNIQUE INDEX UNIQ_A412FA92591CC992 (course_id)');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92591CC992');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92579F4768');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA9219E9AC5F');
        $this->addSql('ALTER TABLE quiz DROP time_limit, CHANGE chapter_id chapter_id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT `FK_A412FA92591CC992` FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT `FK_A412FA92579F4768` FOREIGN KEY (chapter_id) REFERENCES chapter (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT `FK_A412FA9219E9AC5F` FOREIGN KEY (supervisor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_attempts DROP FOREIGN KEY FK_69031E21CB944F1A');
        $this->addSql('ALTER TABLE quiz_attempts DROP FOREIGN KEY FK_69031E21853CD175');
        $this->addSql('ALTER TABLE quiz_attempts DROP started_at, DROP status, DROP answers_json');
        $this->addSql('ALTER TABLE quiz_attempts ADD CONSTRAINT `FK_69031E21CB944F1A` FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_attempts ADD CONSTRAINT `FK_69031E21853CD175` FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE reactions DROP INDEX IDX_38737FB39D86650F, ADD UNIQUE INDEX UNIQ_38737FB39D86650F (user_id_id)');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3E85F12B8');
        $this->addSql('DROP INDEX uniq_user_post_type ON reactions');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT `FK_38737FB3E85F12B8` FOREIGN KEY (post_id_id) REFERENCES posts (id)');
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE477CFE419E2');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT `FK_5E3DE477CFE419E2` FOREIGN KEY (cv_id) REFERENCES cv (id)');
        $this->addSql('ALTER TABLE sponsor DROP FOREIGN KEY FK_818CC9D4F05788E9');
        $this->addSql('ALTER TABLE sponsor ADD CONSTRAINT `FK_818CC9D4F05788E9` FOREIGN KEY (creator_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sponsor_hackathon DROP FOREIGN KEY FK_AECDBCA112F7FB51');
        $this->addSql('ALTER TABLE sponsor_hackathon DROP FOREIGN KEY FK_AECDBCA1996D90CF');
        $this->addSql('ALTER TABLE sponsor_hackathon ADD CONSTRAINT `FK_AECDBCA112F7FB51` FOREIGN KEY (sponsor_id) REFERENCES sponsor (id)');
        $this->addSql('ALTER TABLE sponsor_hackathon ADD CONSTRAINT `FK_AECDBCA1996D90CF` FOREIGN KEY (hackathon_id) REFERENCES hackathon (id)');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user DROP report_nbr, DROP previous_role, DROP banned_until, DROP archived, DROP webauthn_credential_id, DROP webauthn_public_key, DROP face_descriptor, CHANGE prenom prenom VARCHAR(30) NOT NULL, CHANGE date_naissance date_naissance DATE NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE ban ban TINYINT NOT NULL, CHANGE is_active is_active TINYINT NOT NULL');
    }
}
