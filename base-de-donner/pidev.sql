-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 09, 2026 at 05:36 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pidev`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity`
--

CREATE TABLE `activity` (
  `id` int(11) NOT NULL,
  `submission_file` varchar(255) NOT NULL,
  `submission_date` datetime NOT NULL,
  `id_challenge_id` int(11) NOT NULL,
  `group_id_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `answer`
--

CREATE TABLE `answer` (
  `id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `is_correct` tinyint(4) NOT NULL,
  `question_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certif`
--

CREATE TABLE `certif` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `issued_by` varchar(30) NOT NULL,
  `issue_date` date NOT NULL,
  `exp_date` date NOT NULL,
  `cv_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `challenge`
--

CREATE TABLE `challenge` (
  `id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `description` longtext NOT NULL,
  `target_skill` varchar(30) NOT NULL,
  `difficulty` varchar(30) NOT NULL,
  `min_group_nbr` int(11) NOT NULL,
  `max_group_nbr` int(11) NOT NULL,
  `dead_line` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `creator_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapter`
--

CREATE TABLE `chapter` (
  `id` int(11) NOT NULL,
  `chapter_order` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `min_score` double NOT NULL,
  `content` varchar(255) NOT NULL,
  `title` varchar(30) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commentaires`
--

CREATE TABLE `commentaires` (
  `id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `likes` int(11) NOT NULL,
  `author_id_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `description` longtext NOT NULL,
  `duration` int(11) NOT NULL,
  `validation_score` double NOT NULL,
  `content` varchar(255) NOT NULL,
  `creator_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cv`
--

CREATE TABLE `cv` (
  `id` int(11) NOT NULL,
  `nom_cv` varchar(30) NOT NULL,
  `langue` varchar(30) NOT NULL,
  `id_template` int(11) DEFAULT NULL,
  `progression` int(11) DEFAULT NULL,
  `creation_date` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `summary` longtext DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cv_application`
--

CREATE TABLE `cv_application` (
  `id` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `applied_at` datetime NOT NULL,
  `cv_id` int(11) NOT NULL,
  `offer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260209120359', '2026-02-09 12:04:18', 5922);

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

CREATE TABLE `education` (
  `id` int(11) NOT NULL,
  `degree` varchar(30) NOT NULL,
  `field_of_study` varchar(40) DEFAULT NULL,
  `school` varchar(30) NOT NULL,
  `city` varchar(40) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` longtext DEFAULT NULL,
  `cv_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollement`
--

CREATE TABLE `enrollement` (
  `id` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `progress` int(11) NOT NULL,
  `score` double DEFAULT NULL,
  `completed_at` date NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evaluation`
--

CREATE TABLE `evaluation` (
  `id` int(11) NOT NULL,
  `group_score` double DEFAULT NULL,
  `feedback` varchar(255) DEFAULT NULL,
  `pre_feedback` varchar(255) DEFAULT NULL,
  `activity_id_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `experience`
--

CREATE TABLE `experience` (
  `id` int(11) NOT NULL,
  `job_title` varchar(30) NOT NULL,
  `company` varchar(30) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `currently_working` tinyint(4) NOT NULL,
  `description` longtext NOT NULL,
  `cv_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group`
--

CREATE TABLE `group` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `description` longtext NOT NULL,
  `creation_date` datetime NOT NULL,
  `type` varchar(20) NOT NULL,
  `level` varchar(20) NOT NULL,
  `max_members` int(11) NOT NULL,
  `rating_score` double NOT NULL,
  `icon` varchar(255) NOT NULL,
  `leader_id_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hackathon`
--

CREATE TABLE `hackathon` (
  `id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `theme` varchar(30) NOT NULL,
  `description` longtext NOT NULL,
  `rules` longtext NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `registration_open_at` datetime NOT NULL,
  `registration_close_at` datetime NOT NULL,
  `fee` double NOT NULL,
  `max_teams` int(11) NOT NULL,
  `team_size_max` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `cover_url` varchar(255) NOT NULL,
  `status` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `creator_id_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hackathon`
--

INSERT INTO `hackathon` (`id`, `title`, `theme`, `description`, `rules`, `start_at`, `end_at`, `registration_open_at`, `registration_close_at`, `fee`, `max_teams`, `team_size_max`, `location`, `cover_url`, `status`, `created_at`, `creator_id_id`) VALUES
(1, 'AI Revolution 2027', 'Artificial Intelligence', 'Build the next generation of AI agents.', 'Teams of 3-5. No pre-built code.', '2026-05-01 09:00:00', '2026-05-03 18:00:00', '2026-01-01 00:00:00', '2026-04-30 23:59:00', 50, 20, 5, 'Tunis', '/uploads/hackathons/698a0a8b91e0f.png', 'pending', '2026-02-09 15:11:58', 1),
(2, 'Green Tech Summit', 'Sustainability', 'Solve environmental challenges with tech.', 'Project must be open source.', '2026-06-15 09:00:00', '2026-06-17 17:00:00', '2026-02-01 00:00:00', '2026-06-10 23:59:59', 20, 15, 4, 'Sousse', 'https://images.unsplash.com/photo-1542601906990-24ccd08d7450?ixlib=rb-1.2.1&auto=format&fit=crop&w=1268&q=80', 'Open', '2026-02-09 15:11:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `langue`
--

CREATE TABLE `langue` (
  `id` int(11) NOT NULL,
  `nom` varchar(30) NOT NULL,
  `niveau` varchar(30) NOT NULL,
  `cv_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons_learned`
--

CREATE TABLE `lessons_learned` (
  `id` int(11) NOT NULL,
  `lesson_description` longtext NOT NULL,
  `id_activity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership`
--

CREATE TABLE `membership` (
  `id` int(11) NOT NULL,
  `role` varchar(20) NOT NULL,
  `contribution_score` double NOT NULL,
  `achievement_unlocked` varchar(255) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL,
  `user_id_id` int(11) NOT NULL,
  `group_id_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_activity`
--

CREATE TABLE `member_activity` (
  `id` int(11) NOT NULL,
  `activity_description` longtext NOT NULL,
  `indiv_score` double NOT NULL,
  `id_activity_id` int(11) NOT NULL,
  `user_id_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `sender_id` int(11) NOT NULL,
  `group_id_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offer`
--

CREATE TABLE `offer` (
  `id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `description` longtext NOT NULL,
  `offer_type` varchar(30) NOT NULL,
  `field` varchar(30) NOT NULL,
  `required_level` varchar(30) NOT NULL,
  `required_skills` longtext NOT NULL,
  `location` varchar(40) NOT NULL,
  `contract_type` varchar(40) NOT NULL,
  `duration` int(11) DEFAULT NULL,
  `salary_range` double DEFAULT NULL,
  `status` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `entreprise_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `participation`
--

CREATE TABLE `participation` (
  `id` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `payment_status` varchar(30) NOT NULL,
  `payment_ref` varchar(30) NOT NULL,
  `registred_at` datetime NOT NULL,
  `hackathon_id` int(11) NOT NULL,
  `group_id_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `description` longtext NOT NULL,
  `titre` varchar(30) NOT NULL,
  `status` varchar(30) NOT NULL,
  `visibility` varchar(30) NOT NULL,
  `attached_file` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `likes_counter` int(11) NOT NULL,
  `group_id_id` int(11) NOT NULL,
  `author_id_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `problem_solution`
--

CREATE TABLE `problem_solution` (
  `id` int(11) NOT NULL,
  `problem_description` longtext NOT NULL,
  `group_solution` longtext DEFAULT NULL,
  `supervisor_solution` longtext NOT NULL,
  `activity_id_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `type` varchar(20) NOT NULL,
  `point` double NOT NULL,
  `quiz_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `passing_score` double NOT NULL,
  `max_attempts` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `chapter_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `attempt_nbr` int(11) NOT NULL,
  `score` double NOT NULL,
  `submitted_at` datetime NOT NULL,
  `student_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reactions`
--

CREATE TABLE `reactions` (
  `id` int(11) NOT NULL,
  `type` varchar(25) NOT NULL,
  `url` varchar(255) NOT NULL,
  `posted_at` datetime NOT NULL,
  `user_id_id` int(11) NOT NULL,
  `post_id_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skill`
--

CREATE TABLE `skill` (
  `id` int(11) NOT NULL,
  `nom` varchar(35) NOT NULL,
  `type` varchar(20) NOT NULL,
  `level` varchar(30) NOT NULL,
  `cv_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sponsor`
--

CREATE TABLE `sponsor` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `description` longtext NOT NULL,
  `logo_url` varchar(255) NOT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `creator_id_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sponsor`
--

INSERT INTO `sponsor` (`id`, `name`, `description`, `logo_url`, `website_url`, `created_at`, `creator_id_id`) VALUES
(1, 'TechCorp', 'Leading tech solutions provider.', 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Google_2015_logo.svg/1200px-Google_2015_logo.svg.png', 'https://google.com', '2026-02-09 15:11:58', 1),
(2, 'InnovateX', 'Driving innovation forward.', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Amazon_logo.svg/2560px-Amazon_logo.svg.png', 'https://amazon.com', '2026-02-09 15:11:58', 1),
(3, 'CodeMasters', 'We love clean code.', 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/91/Octicons-mark-github.svg/2048px-Octicons-mark-github.svg.png', 'https://github.com', '2026-02-09 15:11:58', 1),
(4, 'CloudNine', 'Cloud infrastructure experts.', 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/96/Microsoft_logo_%282012%29.svg/1280px-Microsoft_logo_%282012%29.svg.png', 'https://microsoft.com', '2026-02-09 15:11:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sponsor_hackathon`
--

CREATE TABLE `sponsor_hackathon` (
  `id` int(11) NOT NULL,
  `contribution_type` varchar(30) NOT NULL,
  `contribution_value` double DEFAULT NULL,
  `sponsor_id` int(11) NOT NULL,
  `hackathon_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sponsor_hackathon`
--

INSERT INTO `sponsor_hackathon` (`id`, `contribution_type`, `contribution_value`, `sponsor_id`, `hackathon_id`) VALUES
(2, 'Cloud Credits', 2000, 2, 1),
(3, 'Mentorship', 1000, 3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nom` varchar(30) NOT NULL,
  `prenom` varchar(30) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `email` varchar(180) NOT NULL,
  `ban` tinyint(4) NOT NULL DEFAULT 0,
  `photo` varchar(255) DEFAULT NULL,
  `passwd` varchar(255) NOT NULL,
  `date_inscrit` datetime NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `report_nbr` int(11) NOT NULL DEFAULT 0,
  `previous_role` varchar(30) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `experience` longtext DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `skills` longtext DEFAULT NULL,
  `score_generale` int(11) DEFAULT NULL,
  `domaine` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `nom`, `prenom`, `date_naissance`, `email`, `ban`, `photo`, `passwd`, `date_inscrit`, `is_active`, `report_nbr`, `previous_role`, `type`, `experience`, `education`, `skills`, `score_generale`, `domaine`) VALUES
(1, 'Admin', 'Root', '1999-01-01', 'admin@gmail.com', 0, 'admin.png', '$2y$10$hashedpasswordhere', '2026-02-09 13:05:13', 1, 0, NULL, 'admin', NULL, NULL, NULL, 0, 'system'),
(2, 'kd', 'kdskds', '2001-06-05', 'moemen.admin@example.com', 0, NULL, '$2y$13$RvxToUlt7VHgN4Dgu1Nauellbg.vAkOi6MN/i.Z2iaqDQaxVV3hFa', '2026-02-09 13:05:37', 1, 0, NULL, 'admin', NULL, NULL, NULL, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity`
--
ALTER TABLE `activity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_AC74095A2F68B530` (`group_id_id`),
  ADD KEY `IDX_AC74095ABB636FB4` (`id_challenge_id`);

--
-- Indexes for table `answer`
--
ALTER TABLE `answer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_DADD4A251E27F6BF` (`question_id`);

--
-- Indexes for table `certif`
--
ALTER TABLE `certif`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_EC509872CFE419E2` (`cv_id`);

--
-- Indexes for table `challenge`
--
ALTER TABLE `challenge`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_D709895161220EA6` (`creator_id`),
  ADD KEY `IDX_D7098951591CC992` (`course_id`);

--
-- Indexes for table `chapter`
--
ALTER TABLE `chapter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_F981B52E591CC992` (`course_id`);

--
-- Indexes for table `commentaires`
--
ALTER TABLE `commentaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_D9BEC0C469CCBE9A` (`author_id_id`),
  ADD KEY `IDX_D9BEC0C44B89032C` (`post_id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_169E6FB961220EA6` (`creator_id`);

--
-- Indexes for table `cv`
--
ALTER TABLE `cv`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B66FFE92A76ED395` (`user_id`);

--
-- Indexes for table `cv_application`
--
ALTER TABLE `cv_application`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BA25D93CCFE419E2` (`cv_id`),
  ADD KEY `IDX_BA25D93C53C674EE` (`offer_id`);

--
-- Indexes for table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_DB0A5ED2CFE419E2` (`cv_id`);

--
-- Indexes for table `enrollement`
--
ALTER TABLE `enrollement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_1B002285CB944F1A` (`student_id`),
  ADD KEY `IDX_1B002285591CC992` (`course_id`);

--
-- Indexes for table `evaluation`
--
ALTER TABLE `evaluation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_1323A5756146A8E4` (`activity_id_id`);

--
-- Indexes for table `experience`
--
ALTER TABLE `experience`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_590C103CFE419E2` (`cv_id`);

--
-- Indexes for table `group`
--
ALTER TABLE `group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6DC044C5EFE6DECF` (`leader_id_id`);

--
-- Indexes for table `hackathon`
--
ALTER TABLE `hackathon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8B3AF64FF05788E9` (`creator_id_id`);

--
-- Indexes for table `langue`
--
ALTER TABLE `langue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_9357758ECFE419E2` (`cv_id`);

--
-- Indexes for table `lessons_learned`
--
ALTER TABLE `lessons_learned`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A4F157B699D2AD61` (`id_activity_id`);

--
-- Indexes for table `membership`
--
ALTER TABLE `membership`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_86FFD2859D86650F` (`user_id_id`),
  ADD KEY `IDX_86FFD2852F68B530` (`group_id_id`);

--
-- Indexes for table `member_activity`
--
ALTER TABLE `member_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5BA9AAB099D2AD61` (`id_activity_id`),
  ADD KEY `IDX_5BA9AAB09D86650F` (`user_id_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B6BD307FF624B39D` (`sender_id`),
  ADD KEY `IDX_B6BD307F2F68B530` (`group_id_id`);

--
-- Indexes for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`);

--
-- Indexes for table `offer`
--
ALTER TABLE `offer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_29D6873EA4AEAFEA` (`entreprise_id`);

--
-- Indexes for table `participation`
--
ALTER TABLE `participation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_AB55E24F996D90CF` (`hackathon_id`),
  ADD KEY `IDX_AB55E24F2F68B530` (`group_id_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_885DBAFA2F68B530` (`group_id_id`),
  ADD KEY `IDX_885DBAFA69CCBE9A` (`author_id_id`);

--
-- Indexes for table `problem_solution`
--
ALTER TABLE `problem_solution`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_56D92B9D6146A8E4` (`activity_id_id`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B6F7494E853CD175` (`quiz_id`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_A412FA92591CC992` (`course_id`),
  ADD UNIQUE KEY `UNIQ_A412FA92579F4768` (`chapter_id`),
  ADD KEY `IDX_A412FA9219E9AC5F` (`supervisor_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_69031E21CB944F1A` (`student_id`),
  ADD KEY `IDX_69031E21853CD175` (`quiz_id`);

--
-- Indexes for table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_38737FB39D86650F` (`user_id_id`),
  ADD KEY `IDX_38737FB3E85F12B8` (`post_id_id`);

--
-- Indexes for table `skill`
--
ALTER TABLE `skill`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5E3DE477CFE419E2` (`cv_id`);

--
-- Indexes for table `sponsor`
--
ALTER TABLE `sponsor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_818CC9D4F05788E9` (`creator_id_id`);

--
-- Indexes for table `sponsor_hackathon`
--
ALTER TABLE `sponsor_hackathon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_AECDBCA112F7FB51` (`sponsor_id`),
  ADD KEY `IDX_AECDBCA1996D90CF` (`hackathon_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity`
--
ALTER TABLE `activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `answer`
--
ALTER TABLE `answer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certif`
--
ALTER TABLE `certif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `challenge`
--
ALTER TABLE `challenge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chapter`
--
ALTER TABLE `chapter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commentaires`
--
ALTER TABLE `commentaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cv`
--
ALTER TABLE `cv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cv_application`
--
ALTER TABLE `cv_application`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `education`
--
ALTER TABLE `education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollement`
--
ALTER TABLE `enrollement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evaluation`
--
ALTER TABLE `evaluation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `experience`
--
ALTER TABLE `experience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `group`
--
ALTER TABLE `group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hackathon`
--
ALTER TABLE `hackathon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `langue`
--
ALTER TABLE `langue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lessons_learned`
--
ALTER TABLE `lessons_learned`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membership`
--
ALTER TABLE `membership`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `member_activity`
--
ALTER TABLE `member_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offer`
--
ALTER TABLE `offer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `participation`
--
ALTER TABLE `participation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `problem_solution`
--
ALTER TABLE `problem_solution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skill`
--
ALTER TABLE `skill`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sponsor`
--
ALTER TABLE `sponsor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sponsor_hackathon`
--
ALTER TABLE `sponsor_hackathon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity`
--
ALTER TABLE `activity`
  ADD CONSTRAINT `FK_AC74095A2F68B530` FOREIGN KEY (`group_id_id`) REFERENCES `group` (`id`),
  ADD CONSTRAINT `FK_AC74095ABB636FB4` FOREIGN KEY (`id_challenge_id`) REFERENCES `challenge` (`id`);

--
-- Constraints for table `answer`
--
ALTER TABLE `answer`
  ADD CONSTRAINT `FK_DADD4A251E27F6BF` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`);

--
-- Constraints for table `certif`
--
ALTER TABLE `certif`
  ADD CONSTRAINT `FK_EC509872CFE419E2` FOREIGN KEY (`cv_id`) REFERENCES `cv` (`id`);

--
-- Constraints for table `challenge`
--
ALTER TABLE `challenge`
  ADD CONSTRAINT `FK_D7098951591CC992` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`),
  ADD CONSTRAINT `FK_D709895161220EA6` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `chapter`
--
ALTER TABLE `chapter`
  ADD CONSTRAINT `FK_F981B52E591CC992` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`);

--
-- Constraints for table `commentaires`
--
ALTER TABLE `commentaires`
  ADD CONSTRAINT `FK_D9BEC0C44B89032C` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  ADD CONSTRAINT `FK_D9BEC0C469CCBE9A` FOREIGN KEY (`author_id_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `FK_169E6FB961220EA6` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `cv`
--
ALTER TABLE `cv`
  ADD CONSTRAINT `FK_B66FFE92A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `cv_application`
--
ALTER TABLE `cv_application`
  ADD CONSTRAINT `FK_BA25D93C53C674EE` FOREIGN KEY (`offer_id`) REFERENCES `offer` (`id`),
  ADD CONSTRAINT `FK_BA25D93CCFE419E2` FOREIGN KEY (`cv_id`) REFERENCES `cv` (`id`);

--
-- Constraints for table `education`
--
ALTER TABLE `education`
  ADD CONSTRAINT `FK_DB0A5ED2CFE419E2` FOREIGN KEY (`cv_id`) REFERENCES `cv` (`id`);

--
-- Constraints for table `enrollement`
--
ALTER TABLE `enrollement`
  ADD CONSTRAINT `FK_1B002285591CC992` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`),
  ADD CONSTRAINT `FK_1B002285CB944F1A` FOREIGN KEY (`student_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `evaluation`
--
ALTER TABLE `evaluation`
  ADD CONSTRAINT `FK_1323A5756146A8E4` FOREIGN KEY (`activity_id_id`) REFERENCES `activity` (`id`);

--
-- Constraints for table `experience`
--
ALTER TABLE `experience`
  ADD CONSTRAINT `FK_590C103CFE419E2` FOREIGN KEY (`cv_id`) REFERENCES `cv` (`id`);

--
-- Constraints for table `group`
--
ALTER TABLE `group`
  ADD CONSTRAINT `FK_6DC044C5EFE6DECF` FOREIGN KEY (`leader_id_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `hackathon`
--
ALTER TABLE `hackathon`
  ADD CONSTRAINT `FK_8B3AF64FF05788E9` FOREIGN KEY (`creator_id_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `langue`
--
ALTER TABLE `langue`
  ADD CONSTRAINT `FK_9357758ECFE419E2` FOREIGN KEY (`cv_id`) REFERENCES `cv` (`id`);

--
-- Constraints for table `lessons_learned`
--
ALTER TABLE `lessons_learned`
  ADD CONSTRAINT `FK_A4F157B699D2AD61` FOREIGN KEY (`id_activity_id`) REFERENCES `activity` (`id`);

--
-- Constraints for table `membership`
--
ALTER TABLE `membership`
  ADD CONSTRAINT `FK_86FFD2852F68B530` FOREIGN KEY (`group_id_id`) REFERENCES `group` (`id`),
  ADD CONSTRAINT `FK_86FFD2859D86650F` FOREIGN KEY (`user_id_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `member_activity`
--
ALTER TABLE `member_activity`
  ADD CONSTRAINT `FK_5BA9AAB099D2AD61` FOREIGN KEY (`id_activity_id`) REFERENCES `activity` (`id`),
  ADD CONSTRAINT `FK_5BA9AAB09D86650F` FOREIGN KEY (`user_id_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `FK_B6BD307F2F68B530` FOREIGN KEY (`group_id_id`) REFERENCES `group` (`id`),
  ADD CONSTRAINT `FK_B6BD307FF624B39D` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `offer`
--
ALTER TABLE `offer`
  ADD CONSTRAINT `FK_29D6873EA4AEAFEA` FOREIGN KEY (`entreprise_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `participation`
--
ALTER TABLE `participation`
  ADD CONSTRAINT `FK_AB55E24F2F68B530` FOREIGN KEY (`group_id_id`) REFERENCES `group` (`id`),
  ADD CONSTRAINT `FK_AB55E24F996D90CF` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathon` (`id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `FK_885DBAFA2F68B530` FOREIGN KEY (`group_id_id`) REFERENCES `group` (`id`),
  ADD CONSTRAINT `FK_885DBAFA69CCBE9A` FOREIGN KEY (`author_id_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `problem_solution`
--
ALTER TABLE `problem_solution`
  ADD CONSTRAINT `FK_56D92B9D6146A8E4` FOREIGN KEY (`activity_id_id`) REFERENCES `activity` (`id`);

--
-- Constraints for table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `FK_B6F7494E853CD175` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`);

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `FK_A412FA9219E9AC5F` FOREIGN KEY (`supervisor_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_A412FA92579F4768` FOREIGN KEY (`chapter_id`) REFERENCES `chapter` (`id`),
  ADD CONSTRAINT `FK_A412FA92591CC992` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`);

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `FK_69031E21853CD175` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`),
  ADD CONSTRAINT `FK_69031E21CB944F1A` FOREIGN KEY (`student_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `reactions`
--
ALTER TABLE `reactions`
  ADD CONSTRAINT `FK_38737FB39D86650F` FOREIGN KEY (`user_id_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_38737FB3E85F12B8` FOREIGN KEY (`post_id_id`) REFERENCES `posts` (`id`);

--
-- Constraints for table `skill`
--
ALTER TABLE `skill`
  ADD CONSTRAINT `FK_5E3DE477CFE419E2` FOREIGN KEY (`cv_id`) REFERENCES `cv` (`id`);

--
-- Constraints for table `sponsor`
--
ALTER TABLE `sponsor`
  ADD CONSTRAINT `FK_818CC9D4F05788E9` FOREIGN KEY (`creator_id_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `sponsor_hackathon`
--
ALTER TABLE `sponsor_hackathon`
  ADD CONSTRAINT `FK_AECDBCA112F7FB51` FOREIGN KEY (`sponsor_id`) REFERENCES `sponsor` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_AECDBCA1996D90CF` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathon` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
