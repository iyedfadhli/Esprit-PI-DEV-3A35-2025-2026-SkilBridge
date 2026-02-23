<?php
/**
 * Seed script : Base de données de cours Soft Skills
 *
 * Crée 6 cours de soft skills avec chapitres, quizzes, questions/réponses
 * et chaîne de prérequis (dépendances entre cours).
 *
 * Arbre de dépendances :
 *
 *   Communication Efficace (BEGINNER)
 *       └──► Intelligence Émotionnelle (BEGINNER)
 *                └──► Travail d'Équipe & Collaboration (INTERMEDIATE)
 *                         └──► Leadership & Influence (INTERMEDIATE)
 *                                  └──► Gestion du Temps & Productivité (ADVANCED)
 *                                           └──► Résolution de Conflits & Négociation (ADVANCED)
 *
 * Usage : php scripts/seed_softskills.php
 */

use App\Entity\Chapter;
use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\Answer;
use App\Entity\Course;

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

if (class_exists(Dotenv::class)) {
    (new Dotenv())->bootEnv(__DIR__ . '/../.env');
}

$kernel = new \App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

// ── Récupérer un utilisateur créateur ──
$userRepo = $em->getRepository(\App\Entity\User::class);
$creator = $userRepo->findOneBy([]);
if (!$creator) {
    $creator = new \App\Entity\Admin();
    $creator->setNom('Admin');
    $creator->setPrenom('SoftSkills');
    $creator->setDateNaissance(new \DateTime('1990-01-01'));
    $creator->setEmail('admin-softskills@example.test');
    $creator->setBan(false);
    $creator->setPasswd('password');
    $creator->setDateInscrit(new \DateTime());
    $creator->setIsActive(true);
    $em->persist($creator);
    $em->flush();
}

echo "=== Seed Soft Skills : Démarrage ===\n\n";

// ════════════════════════════════════════════════════════════════════
// Données des cours
// ════════════════════════════════════════════════════════════════════

$coursesData = [

    // ─── COURS 1 : Communication Efficace (BEGINNER) ───
    [
        'title'       => 'Communication Efficace',
        'description' => 'Maîtrisez les fondamentaux de la communication interpersonnelle : écoute active, expression claire, langage non-verbal et feedback constructif.',
        'duration'    => 8,
        'difficulty'  => 'BEGINNER',
        'validation'  => 60,
        'content'     => 'Ce cours couvre les bases essentielles de la communication en milieu professionnel.',
        'sections'    => ['Écoute active', 'Expression orale', 'Communication non-verbale', 'Feedback'],
        'chapters'    => [
            [
                'title'   => 'Écoute Active',
                'content' => 'L\'écoute active consiste à se concentrer pleinement sur l\'interlocuteur, reformuler ses propos, poser des questions ouvertes et éviter les interruptions. Elle repose sur 4 piliers : attention, reformulation, empathie, questionnement.',
                'order'   => 1,
                'quiz'    => [
                    'title'    => 'Quiz Écoute Active',
                    'passing'  => 60,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Quel est le premier pilier de l\'écoute active ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'L\'attention totale envers l\'interlocuteur', 'correct' => true],
                                ['content' => 'Préparer sa réponse pendant que l\'autre parle', 'correct' => false],
                                ['content' => 'Prendre des notes détaillées', 'correct' => false],
                                ['content' => 'Couper la parole pour clarifier', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La reformulation en écoute active sert à :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Répéter mot pour mot ce que l\'autre a dit', 'correct' => false],
                                ['content' => 'Montrer qu\'on a compris et vérifier sa compréhension', 'correct' => true],
                                ['content' => 'Résumer pour gagner du temps', 'correct' => false],
                                ['content' => 'Corriger les erreurs de l\'interlocuteur', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Parmi ces comportements, lequel nuit à l\'écoute active ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Maintenir un contact visuel', 'correct' => false],
                                ['content' => 'Hocher la tête pour montrer son attention', 'correct' => false],
                                ['content' => 'Consulter son téléphone pendant la conversation', 'correct' => true],
                                ['content' => 'Poser des questions de clarification', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Les questions ouvertes commencent généralement par :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Est-ce que...', 'correct' => false],
                                ['content' => 'Comment, Pourquoi, Qu\'est-ce que...', 'correct' => true],
                                ['content' => 'Oui ou non ?', 'correct' => false],
                                ['content' => 'N\'est-ce pas ?', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'L\'empathie dans l\'écoute active signifie :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Être d\'accord avec tout ce que dit l\'autre', 'correct' => false],
                                ['content' => 'Se mettre à la place de l\'autre pour comprendre ses émotions', 'correct' => true],
                                ['content' => 'Montrer de la pitié', 'correct' => false],
                                ['content' => 'Donner des conseils immédiatement', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'   => 'Expression Claire & Assertive',
                'content' => 'L\'assertivité est la capacité à exprimer ses idées, besoins et limites de manière claire, directe et respectueuse. Elle se situe entre la passivité et l\'agressivité. La méthode DESC (Décrire, Exprimer, Spécifier, Conclure) est un outil puissant.',
                'order'   => 2,
                'quiz'    => [
                    'title'    => 'Quiz Expression Assertive',
                    'passing'  => 60,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'L\'assertivité se situe entre :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'La passivité et l\'agressivité', 'correct' => true],
                                ['content' => 'La timidité et l\'arrogance', 'correct' => false],
                                ['content' => 'Le silence et le cri', 'correct' => false],
                                ['content' => 'La soumission et la domination', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Que signifie le « D » dans la méthode DESC ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Demander', 'correct' => false],
                                ['content' => 'Décrire la situation de manière factuelle', 'correct' => true],
                                ['content' => 'Décider de la solution', 'correct' => false],
                                ['content' => 'Donner son avis', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Un message « Je » est préférable à un message « Tu » car :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Il est plus court', 'correct' => false],
                                ['content' => 'Il exprime son ressenti sans accuser l\'autre', 'correct' => true],
                                ['content' => 'Il montre qu\'on a raison', 'correct' => false],
                                ['content' => 'Il est plus formel', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Quelle phrase est un exemple d\'assertivité ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => '« Fais comme tu veux, je m\'en fiche »', 'correct' => false],
                                ['content' => '« Tu as tort et tu le sais ! »', 'correct' => false],
                                ['content' => '« Je comprends ton point de vue, et voici ma perspective... »', 'correct' => true],
                                ['content' => '« Bon, d\'accord... si tu insistes »', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le « C » de DESC signifie :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Critiquer', 'correct' => false],
                                ['content' => 'Convaincre', 'correct' => false],
                                ['content' => 'Conclure positivement, trouver un accord', 'correct' => true],
                                ['content' => 'Contredire', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'   => 'Communication Non-Verbale',
                'content' => 'Plus de 55% de la communication passe par le non-verbal : posture, gestes, expressions faciales, contact visuel et proxémie. La congruence entre verbal et non-verbal renforce la crédibilité du message.',
                'order'   => 3,
                'quiz'    => [
                    'title'    => 'Quiz Communication Non-Verb.',
                    'passing'  => 60,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Selon les études, quel pourcentage de la communication est non-verbal ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Environ 10%', 'correct' => false],
                                ['content' => 'Environ 30%', 'correct' => false],
                                ['content' => 'Plus de 55%', 'correct' => true],
                                ['content' => 'Environ 90%', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La proxémie étudie :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Le ton de la voix', 'correct' => false],
                                ['content' => 'La distance physique entre les interlocuteurs', 'correct' => true],
                                ['content' => 'Les gestes des mains', 'correct' => false],
                                ['content' => 'Les expressions du visage', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La congruence en communication signifie :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Parler fort et clairement', 'correct' => false],
                                ['content' => 'L\'alignement entre le message verbal et non-verbal', 'correct' => true],
                                ['content' => 'Utiliser des gestes amples', 'correct' => false],
                                ['content' => 'Éviter tout contact visuel', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Les bras croisés pendant une conversation indiquent souvent :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'L\'ouverture et l\'intérêt', 'correct' => false],
                                ['content' => 'La détente et le confort', 'correct' => false],
                                ['content' => 'La fermeture, la défense ou le désaccord', 'correct' => true],
                                ['content' => 'La concentration intense', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Un sourire sincère se distingue d\'un sourire forcé par :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'La largeur du sourire', 'correct' => false],
                                ['content' => 'L\'implication des muscles autour des yeux (sourire de Duchenne)', 'correct' => true],
                                ['content' => 'La durée du sourire', 'correct' => false],
                                ['content' => 'Le nombre de dents visibles', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    // ─── COURS 2 : Intelligence Émotionnelle (BEGINNER) ───
    [
        'title'       => 'Intelligence Émotionnelle',
        'description' => 'Développez votre intelligence émotionnelle : conscience de soi, régulation émotionnelle, motivation intrinsèque, empathie et compétences sociales selon le modèle de Goleman.',
        'duration'    => 10,
        'difficulty'  => 'BEGINNER',
        'validation'  => 60,
        'content'     => 'Ce cours explore les 5 composantes de l\'intelligence émotionnelle de Daniel Goleman.',
        'sections'    => ['Conscience de soi', 'Autorégulation', 'Empathie', 'Compétences sociales'],
        'chapters'    => [
            [
                'title'   => 'Conscience de Soi',
                'content' => 'La conscience de soi est la capacité à reconnaître et comprendre ses propres émotions, forces, faiblesses et valeurs. Elle inclut la conscience émotionnelle, l\'auto-évaluation précise et la confiance en soi.',
                'order'   => 1,
                'quiz'    => [
                    'title'    => 'Quiz Conscience de Soi',
                    'passing'  => 60,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'La conscience de soi émotionnelle consiste à :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Contrôler ses émotions en toute circonstance', 'correct' => false],
                                ['content' => 'Reconnaître ses émotions et leur impact sur soi', 'correct' => true],
                                ['content' => 'Ignorer ses émotions négatives', 'correct' => false],
                                ['content' => 'Exprimer toutes ses émotions librement', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'L\'auto-évaluation précise permet de :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Se comparer aux autres', 'correct' => false],
                                ['content' => 'Connaître ses forces et ses limites de manière réaliste', 'correct' => true],
                                ['content' => 'Toujours se voir positivement', 'correct' => false],
                                ['content' => 'Éviter toute critique', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Quel outil aide à développer la conscience de soi ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Le journal émotionnel (tenir un carnet de ses émotions)', 'correct' => true],
                                ['content' => 'Éviter de réfléchir à ses émotions', 'correct' => false],
                                ['content' => 'Demander aux autres de gérer ses émotions', 'correct' => false],
                                ['content' => 'Ne jamais montrer ses émotions', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La confiance en soi dans le modèle de Goleman est liée à :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'La certitude d\'avoir toujours raison', 'correct' => false],
                                ['content' => 'La connaissance de sa valeur et de ses capacités', 'correct' => true],
                                ['content' => 'L\'absence de doute', 'correct' => false],
                                ['content' => 'Le fait de ne jamais demander d\'aide', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Combien de composantes principales comporte le modèle d\'IE de Goleman ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => '3', 'correct' => false],
                                ['content' => '4', 'correct' => false],
                                ['content' => '5', 'correct' => true],
                                ['content' => '7', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'   => 'Régulation Émotionnelle',
                'content' => 'La régulation émotionnelle (autorégulation) est la capacité à gérer ses impulsions et émotions perturbatrices. Elle comprend la maîtrise de soi, la fiabilité, l\'adaptabilité et l\'innovation. Techniques : respiration, recadrage cognitif, pause.',
                'order'   => 2,
                'quiz'    => [
                    'title'    => 'Quiz Régulation Émotion.',
                    'passing'  => 60,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'L\'autorégulation émotionnelle consiste à :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Supprimer toutes ses émotions', 'correct' => false],
                                ['content' => 'Gérer ses impulsions et émotions de manière constructive', 'correct' => true],
                                ['content' => 'Toujours rester calme sans exception', 'correct' => false],
                                ['content' => 'Exprimer sa colère immédiatement', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le recadrage cognitif est une technique qui consiste à :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Ignorer la situation stressante', 'correct' => false],
                                ['content' => 'Changer sa perspective sur une situation pour modifier sa réaction émotionnelle', 'correct' => true],
                                ['content' => 'Accuser les autres de la situation', 'correct' => false],
                                ['content' => 'Fuir la situation', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La technique de la « pause » avant de réagir permet de :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Gagner du temps pour manipuler l\'autre', 'correct' => false],
                                ['content' => 'Éviter de réagir de manière impulsive', 'correct' => true],
                                ['content' => 'Montrer son indifférence', 'correct' => false],
                                ['content' => 'Dominer la conversation', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La respiration diaphragmatique aide à la régulation car :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Elle fatigue le cerveau', 'correct' => false],
                                ['content' => 'Elle active le système nerveux parasympathique (apaisement)', 'correct' => true],
                                ['content' => 'Elle empêche de parler', 'correct' => false],
                                ['content' => 'Elle augmente l\'adrénaline', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'L\'adaptabilité émotionnelle est la capacité à :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Ne jamais changer d\'avis', 'correct' => false],
                                ['content' => 'S\'ajuster face au changement et à l\'incertitude', 'correct' => true],
                                ['content' => 'Suivre toujours la majorité', 'correct' => false],
                                ['content' => 'Éviter toute situation nouvelle', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'   => 'Empathie & Compétences Soc.',
                'content' => 'L\'empathie est la capacité à percevoir et comprendre les émotions des autres. Les compétences sociales incluent l\'influence, la communication, la gestion des conflits, le leadership, la catalyse du changement et la collaboration.',
                'order'   => 3,
                'quiz'    => [
                    'title'    => 'Quiz Empathie & Social',
                    'passing'  => 60,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'La différence entre empathie et sympathie est :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Il n\'y a aucune différence', 'correct' => false],
                                ['content' => 'L\'empathie = comprendre le ressenti ; la sympathie = partager le ressenti', 'correct' => true],
                                ['content' => 'La sympathie est plus profonde que l\'empathie', 'correct' => false],
                                ['content' => 'L\'empathie est réservée aux professionnels de santé', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'L\'empathie cognitive consiste à :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Ressentir les mêmes émotions que l\'autre', 'correct' => false],
                                ['content' => 'Comprendre intellectuellement la perspective de l\'autre', 'correct' => true],
                                ['content' => 'Juger les émotions de l\'autre', 'correct' => false],
                                ['content' => 'Ignorer les émotions de l\'autre', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Les compétences sociales dans le modèle de Goleman incluent :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Uniquement la capacité à parler en public', 'correct' => false],
                                ['content' => 'Influence, gestion des conflits, leadership, collaboration', 'correct' => true],
                                ['content' => 'Seulement le réseautage professionnel', 'correct' => false],
                                ['content' => 'La capacité à manipuler les autres', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La « catalyse du changement » est une compétence sociale qui permet de :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Résister au changement', 'correct' => false],
                                ['content' => 'Initier ou gérer le changement dans un groupe', 'correct' => true],
                                ['content' => 'Imposer ses idées de force', 'correct' => false],
                                ['content' => 'Ignorer les résistances des autres', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Écouter sans juger est un signe de :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Faiblesse', 'correct' => false],
                                ['content' => 'Désintérêt', 'correct' => false],
                                ['content' => 'Maturité émotionnelle et empathie', 'correct' => true],
                                ['content' => 'Passivité', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    // ─── COURS 3 : Travail d'Équipe & Collaboration (INTERMEDIATE) ───
    [
        'title'       => 'Travail d\'Équipe & Collab.',
        'description' => 'Apprenez les dynamiques de groupe, les rôles en équipe (modèle Belbin), la synergie collective, la prise de décision collaborative et la gestion de la diversité.',
        'duration'    => 12,
        'difficulty'  => 'INTERMEDIATE',
        'validation'  => 65,
        'content'     => 'Ce cours explore les mécanismes du travail collaboratif efficace en entreprise.',
        'sections'    => ['Dynamiques de groupe', 'Rôles Belbin', 'Décision collaborative', 'Diversité'],
        'chapters'    => [
            [
                'title'   => 'Dynamiques de Groupe',
                'content' => 'Les dynamiques de groupe suivent le modèle de Tuckman : Forming (formation), Storming (confrontation), Norming (normalisation), Performing (performance) et Adjourning (dissolution). Chaque phase nécessite un leadership adapté.',
                'order'   => 1,
                'quiz'    => [
                    'title'    => 'Quiz Dynamiques de Groupe',
                    'passing'  => 65,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Quel modèle décrit les phases de développement d\'un groupe ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Le modèle de Maslow', 'correct' => false],
                                ['content' => 'Le modèle de Tuckman', 'correct' => true],
                                ['content' => 'Le modèle de Porter', 'correct' => false],
                                ['content' => 'Le modèle de Herzberg', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La phase « Storming » est caractérisée par :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'L\'harmonie et la productivité', 'correct' => false],
                                ['content' => 'Les conflits, les tensions et la compétition', 'correct' => true],
                                ['content' => 'La dissolution du groupe', 'correct' => false],
                                ['content' => 'La découverte des membres', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Dans quelle phase le groupe atteint-il sa productivité maximale ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Forming', 'correct' => false],
                                ['content' => 'Norming', 'correct' => false],
                                ['content' => 'Performing', 'correct' => true],
                                ['content' => 'Storming', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La phase « Norming » est celle où :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Les règles et normes de fonctionnement sont établies', 'correct' => true],
                                ['content' => 'Le groupe se dissout', 'correct' => false],
                                ['content' => 'Les membres se rencontrent pour la première fois', 'correct' => false],
                                ['content' => 'Les conflits sont à leur apogée', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le rôle du leader pendant la phase Storming est de :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Ignorer les conflits', 'correct' => false],
                                ['content' => 'Faciliter la résolution des tensions et encourager le dialogue', 'correct' => true],
                                ['content' => 'Imposer sa vision sans discussion', 'correct' => false],
                                ['content' => 'Dissoudre le groupe', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'   => 'Rôles en Équipe (Belbin)',
                'content' => 'Le modèle de Belbin identifie 9 rôles d\'équipe répartis en 3 catégories : orientés action (Propulseur, Organisateur, Perfectionneur), orientés réflexion (Concepteur, Expert, Priseur), orientés relations (Coordinateur, Promoteur, Soutien).',
                'order'   => 2,
                'quiz'    => [
                    'title'    => 'Quiz Rôles Belbin',
                    'passing'  => 65,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Combien de rôles d\'équipe le modèle de Belbin identifie-t-il ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => '5', 'correct' => false],
                                ['content' => '7', 'correct' => false],
                                ['content' => '9', 'correct' => true],
                                ['content' => '12', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le rôle « Coordinateur » est orienté :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Action', 'correct' => false],
                                ['content' => 'Réflexion', 'correct' => false],
                                ['content' => 'Relations', 'correct' => true],
                                ['content' => 'Technique', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le « Propulseur » (Shaper) dans une équipe :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Analyse les idées en profondeur', 'correct' => false],
                                ['content' => 'Pousse le groupe à avancer et à surmonter les obstacles', 'correct' => true],
                                ['content' => 'Maintient l\'harmonie dans le groupe', 'correct' => false],
                                ['content' => 'Apporte une expertise technique spécifique', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le « Concepteur » (Plant) est connu pour :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Sa capacité à organiser et planifier', 'correct' => false],
                                ['content' => 'Sa créativité et ses idées innovantes', 'correct' => true],
                                ['content' => 'Sa rigueur dans l\'exécution', 'correct' => false],
                                ['content' => 'Ses compétences en networking', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Pour qu\'une équipe soit efficace selon Belbin, il faut :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Que tous les membres aient le même rôle', 'correct' => false],
                                ['content' => 'Un équilibre entre les différents rôles', 'correct' => true],
                                ['content' => 'Au moins 3 leaders', 'correct' => false],
                                ['content' => 'Uniquement des experts techniques', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'   => 'Décision Collaborative',
                'content' => 'La prise de décision collaborative utilise des méthodes comme le brainstorming, la technique du groupe nominal, le vote pondéré, le consensus et le consentement. L\'objectif est d\'impliquer tous les membres pour une meilleure adhésion.',
                'order'   => 3,
                'quiz'    => [
                    'title'    => 'Quiz Décision Collaborat.',
                    'passing'  => 65,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Le brainstorming efficace repose sur le principe de :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Critiquer chaque idée immédiatement', 'correct' => false],
                                ['content' => 'Générer un maximum d\'idées sans jugement', 'correct' => true],
                                ['content' => 'Ne retenir que l\'idée du leader', 'correct' => false],
                                ['content' => 'Limiter le nombre de participants à 2', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La différence entre consensus et consentement est :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Consensus = tous d\'accord ; Consentement = personne ne s\'oppose fortement', 'correct' => true],
                                ['content' => 'Il n\'y a aucune différence', 'correct' => false],
                                ['content' => 'Consentement = vote majoritaire', 'correct' => false],
                                ['content' => 'Consensus = le chef décide seul', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le vote pondéré permet de :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Donner le même poids à chaque option', 'correct' => false],
                                ['content' => 'Répartir des points entre plusieurs options selon ses préférences', 'correct' => true],
                                ['content' => 'Éliminer toute discussion', 'correct' => false],
                                ['content' => 'Laisser le manager décider', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La pensée de groupe (groupthink) est un risque qui survient quand :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Les membres expriment trop de désaccords', 'correct' => false],
                                ['content' => 'Le groupe privilégie la cohésion au détriment de l\'analyse critique', 'correct' => true],
                                ['content' => 'Le leader est absent', 'correct' => false],
                                ['content' => 'Les décisions sont prises trop lentement', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Pour éviter le groupthink, il est recommandé de :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Interdire tout débat', 'correct' => false],
                                ['content' => 'Nommer un « avocat du diable » pour challenger les idées', 'correct' => true],
                                ['content' => 'Réduire le groupe à 2 personnes', 'correct' => false],
                                ['content' => 'Voter à main levée uniquement', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    // ─── COURS 4 : Leadership & Influence (INTERMEDIATE) ───
    [
        'title'       => 'Leadership & Influence',
        'description' => 'Explorez les styles de leadership (Goleman), l\'influence positive, la motivation d\'équipe, la délégation efficace et le développement d\'une vision inspirante.',
        'duration'    => 14,
        'difficulty'  => 'INTERMEDIATE',
        'validation'  => 70,
        'content'     => 'Ce cours aborde les compétences de leadership nécessaires pour inspirer et guider une équipe.',
        'sections'    => ['Styles de leadership', 'Influence positive', 'Délégation', 'Vision'],
        'chapters'    => [
            [
                'title'   => 'Styles de Leadership',
                'content' => 'Goleman identifie 6 styles de leadership : Directif (coercive), Chef de file (pacesetting), Visionnaire (authoritative), Collaboratif (affiliative), Participatif (democratic), Coach. Les meilleurs leaders alternent entre plusieurs styles selon le contexte.',
                'order'   => 1,
                'quiz'    => [
                    'title'    => 'Quiz Styles de Leadership',
                    'passing'  => 70,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Combien de styles de leadership Goleman identifie-t-il ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => '3', 'correct' => false],
                                ['content' => '4', 'correct' => false],
                                ['content' => '6', 'correct' => true],
                                ['content' => '8', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le style « Visionnaire » est le plus efficace quand :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'L\'équipe a besoin d\'une direction et d\'un sens clair', 'correct' => true],
                                ['content' => 'Il faut des résultats immédiats à court terme', 'correct' => false],
                                ['content' => 'Les membres sont tous des experts autonomes', 'correct' => false],
                                ['content' => 'Il y a une crise urgente', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le style « Coach » se concentre sur :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'L\'exécution rapide des tâches', 'correct' => false],
                                ['content' => 'Le développement à long terme des compétences individuelles', 'correct' => true],
                                ['content' => 'La prise de décision unilatérale', 'correct' => false],
                                ['content' => 'La création de liens affectifs', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le style « Directif » est à utiliser :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'En situation de crise ou d\'urgence', 'correct' => true],
                                ['content' => 'Au quotidien pour maintenir le contrôle', 'correct' => false],
                                ['content' => 'Quand l\'équipe est très autonome', 'correct' => false],
                                ['content' => 'Pour favoriser la créativité', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Les meilleurs leaders selon Goleman :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Utilisent toujours le même style', 'correct' => false],
                                ['content' => 'Alternent entre plusieurs styles selon le contexte', 'correct' => true],
                                ['content' => 'N\'utilisent que le style directif', 'correct' => false],
                                ['content' => 'Évitent le style coach', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'   => 'Influence & Motivation',
                'content' => 'L\'influence positive repose sur l\'expertise, la confiance, la réciprocité et la communication persuasive (modèle de Cialdini). La motivation intrinsèque (autonomie, maîtrise, sens - Daniel Pink) est plus durable que les récompenses externes.',
                'order'   => 2,
                'quiz'    => [
                    'title'    => 'Quiz Influence & Motivation',
                    'passing'  => 70,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Les 3 piliers de la motivation intrinsèque selon Daniel Pink sont :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Salaire, promotion, bonus', 'correct' => false],
                                ['content' => 'Autonomie, maîtrise, sens (purpose)', 'correct' => true],
                                ['content' => 'Peur, récompense, compétition', 'correct' => false],
                                ['content' => 'Pouvoir, statut, argent', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le principe de réciprocité (Cialdini) signifie :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Manipuler les autres par des cadeaux', 'correct' => false],
                                ['content' => 'Les gens ont tendance à rendre ce qu\'on leur donne', 'correct' => true],
                                ['content' => 'Toujours demander un retour immédiat', 'correct' => false],
                                ['content' => 'Ne jamais aider les autres gratuitement', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'L\'autorité dans le modèle de Cialdini repose sur :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'La position hiérarchique uniquement', 'correct' => false],
                                ['content' => 'L\'expertise, la crédibilité et la compétence perçue', 'correct' => true],
                                ['content' => 'La force physique', 'correct' => false],
                                ['content' => 'L\'ancienneté', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La motivation extrinsèque peut être contre-productive car :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Elle coûte trop cher', 'correct' => false],
                                ['content' => 'Elle peut diminuer la motivation intrinsèque (effet de surjustification)', 'correct' => true],
                                ['content' => 'Elle est toujours inefficace', 'correct' => false],
                                ['content' => 'Les employés la refusent', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le « sens » (purpose) en motivation intrinsèque signifie :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Avoir un bon salaire', 'correct' => false],
                                ['content' => 'Sentir que son travail contribue à quelque chose de plus grand', 'correct' => true],
                                ['content' => 'Avoir une description de poste claire', 'correct' => false],
                                ['content' => 'Travailler seul sans interférence', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'   => 'Délégation & Vision',
                'content' => 'La délégation efficace suit le modèle SMART : ce n\'est pas se débarrasser d\'une tâche mais développer les compétences d\'un collaborateur. La vision inspirante donne un cap, du sens et de l\'énergie à l\'équipe. Elle doit être claire, ambitieuse et porteuse de valeurs.',
                'order'   => 3,
                'quiz'    => [
                    'title'    => 'Quiz Délégation & Vision',
                    'passing'  => 70,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Déléguer efficacement signifie :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Se débarrasser des tâches qu\'on n\'aime pas', 'correct' => false],
                                ['content' => 'Confier une responsabilité avec les moyens et l\'autonomie nécessaires', 'correct' => true],
                                ['content' => 'Micro-manager chaque étape', 'correct' => false],
                                ['content' => 'Ne jamais vérifier le résultat', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Une vision d\'équipe efficace doit être :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Vague pour laisser de la liberté', 'correct' => false],
                                ['content' => 'Claire, ambitieuse et porteuse de sens', 'correct' => true],
                                ['content' => 'Réaliste à court terme uniquement', 'correct' => false],
                                ['content' => 'Réservée au top management', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le principal risque du micro-management est :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Améliorer la qualité du travail', 'correct' => false],
                                ['content' => 'Démotiver et déresponsabiliser les collaborateurs', 'correct' => true],
                                ['content' => 'Gagner du temps', 'correct' => false],
                                ['content' => 'Renforcer la confiance', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Avant de déléguer, il faut s\'assurer que :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'La personne a toutes les compétences sans formation', 'correct' => false],
                                ['content' => 'Les objectifs, moyens et délais sont clairement définis', 'correct' => true],
                                ['content' => 'On ne peut pas faire la tâche soi-même', 'correct' => false],
                                ['content' => 'La tâche est insignifiante', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le suivi après délégation doit être :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Inexistant pour montrer la confiance', 'correct' => false],
                                ['content' => 'Constant et quotidien', 'correct' => false],
                                ['content' => 'Adapté au niveau d\'autonomie du collaborateur', 'correct' => true],
                                ['content' => 'Fait uniquement à la fin du projet', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    // ─── COURS 5 : Gestion du Temps & Productivité (ADVANCED) ───
    [
        'title'       => 'Gestion du Temps & Product.',
        'description' => 'Optimisez votre productivité avec la matrice d\'Eisenhower, la technique Pomodoro, le time-blocking, la gestion de l\'énergie et la lutte contre la procrastination.',
        'duration'    => 10,
        'difficulty'  => 'ADVANCED',
        'validation'  => 75,
        'content'     => 'Ce cours avancé vous donne les outils pour maximiser votre efficacité personnelle et professionnelle.',
        'sections'    => ['Matrice Eisenhower', 'Pomodoro', 'Time-blocking', 'Procrastination'],
        'chapters'    => [
            [
                'title'   => 'Priorisation & Eisenhower',
                'content' => 'La matrice d\'Eisenhower classe les tâches selon 2 axes : urgence et importance. Quadrant 1 (urgent+important) = faire immédiatement. Q2 (important, pas urgent) = planifier. Q3 (urgent, pas important) = déléguer. Q4 (ni l\'un ni l\'autre) = éliminer.',
                'order'   => 1,
                'quiz'    => [
                    'title'    => 'Quiz Matrice Eisenhower',
                    'passing'  => 75,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Les 2 axes de la matrice d\'Eisenhower sont :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Difficulté et durée', 'correct' => false],
                                ['content' => 'Urgence et importance', 'correct' => true],
                                ['content' => 'Coût et bénéfice', 'correct' => false],
                                ['content' => 'Plaisir et obligation', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Une tâche importante mais non urgente (Quadrant 2) doit être :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Ignorée', 'correct' => false],
                                ['content' => 'Planifiée et programmée dans l\'agenda', 'correct' => true],
                                ['content' => 'Faite immédiatement', 'correct' => false],
                                ['content' => 'Déléguée à quelqu\'un d\'autre', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le quadrant le plus productif à long terme est :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Q1 (urgent + important)', 'correct' => false],
                                ['content' => 'Q2 (important, pas urgent)', 'correct' => true],
                                ['content' => 'Q3 (urgent, pas important)', 'correct' => false],
                                ['content' => 'Q4 (ni urgent ni important)', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Répondre à des emails non importants mais urgents relève du :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Quadrant 1', 'correct' => false],
                                ['content' => 'Quadrant 2', 'correct' => false],
                                ['content' => 'Quadrant 3', 'correct' => true],
                                ['content' => 'Quadrant 4', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La loi de Pareto (80/20) appliquée au temps signifie :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => '80% du temps est perdu', 'correct' => false],
                                ['content' => '20% des efforts produisent 80% des résultats', 'correct' => true],
                                ['content' => 'Il faut travailler 80 heures par semaine', 'correct' => false],
                                ['content' => '80% des tâches sont urgentes', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'   => 'Pomodoro & Time-Blocking',
                'content' => 'La technique Pomodoro alterne 25 minutes de travail concentré et 5 minutes de pause (4 cycles puis pause longue). Le time-blocking consiste à bloquer des créneaux dans son agenda pour les tâches importantes. Ces méthodes combattent le multitâche.',
                'order'   => 2,
                'quiz'    => [
                    'title'    => 'Quiz Pomodoro & Blocking',
                    'passing'  => 75,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Un « Pomodoro » standard dure :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => '15 minutes', 'correct' => false],
                                ['content' => '25 minutes', 'correct' => true],
                                ['content' => '45 minutes', 'correct' => false],
                                ['content' => '60 minutes', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Après combien de Pomodoros prend-on une pause longue ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => '2', 'correct' => false],
                                ['content' => '3', 'correct' => false],
                                ['content' => '4', 'correct' => true],
                                ['content' => '6', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le time-blocking consiste à :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Bloquer l\'accès à Internet', 'correct' => false],
                                ['content' => 'Réserver des créneaux spécifiques dans l\'agenda pour chaque tâche', 'correct' => true],
                                ['content' => 'Travailler sans pause toute la journée', 'correct' => false],
                                ['content' => 'Faire plusieurs tâches en même temps', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le multitâche est problématique car :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Il est trop facile', 'correct' => false],
                                ['content' => 'Le coût de « context switching » réduit la productivité de 20 à 40%', 'correct' => true],
                                ['content' => 'Il fatigue les yeux', 'correct' => false],
                                ['content' => 'Il est interdit en entreprise', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Pendant un Pomodoro, si une interruption arrive, il faut :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Abandonner le Pomodoro en cours', 'correct' => false],
                                ['content' => 'La noter et y revenir après le Pomodoro', 'correct' => true],
                                ['content' => 'L\'ignorer complètement', 'correct' => false],
                                ['content' => 'Ajouter 25 minutes au Pomodoro', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'   => 'Anti-Procrastination',
                'content' => 'La procrastination est souvent liée à la peur de l\'échec, le perfectionnisme ou le manque de clarté. Stratégies : la règle des 2 minutes, fractionner les grandes tâches, « eat the frog » (commencer par le plus difficile), l\'engagement public et la visualisation.',
                'order'   => 3,
                'quiz'    => [
                    'title'    => 'Quiz Anti-Procrastination',
                    'passing'  => 75,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'La règle des 2 minutes (David Allen) dit :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Ne travailler que 2 minutes par jour', 'correct' => false],
                                ['content' => 'Si une tâche prend moins de 2 minutes, la faire immédiatement', 'correct' => true],
                                ['content' => 'Prendre 2 minutes de pause toutes les heures', 'correct' => false],
                                ['content' => 'Planifier 2 minutes de réflexion avant chaque tâche', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => '« Eat the frog » (Brian Tracy) signifie :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Manger sainement pour être productif', 'correct' => false],
                                ['content' => 'Commencer la journée par la tâche la plus difficile ou redoutée', 'correct' => true],
                                ['content' => 'Éviter les tâches désagréables', 'correct' => false],
                                ['content' => 'Faire des pauses fréquentes', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le perfectionnisme contribue à la procrastination car :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'On veut tout faire parfaitement et on n\'ose pas commencer', 'correct' => true],
                                ['content' => 'Le travail parfait est toujours rapide', 'correct' => false],
                                ['content' => 'Le perfectionnisme accélère le travail', 'correct' => false],
                                ['content' => 'Le perfectionnisme n\'a aucun lien avec la procrastination', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Fractionner une grande tâche en sous-tâches aide car :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Cela crée plus de travail', 'correct' => false],
                                ['content' => 'Cela réduit l\'anxiété et rend le démarrage plus facile', 'correct' => true],
                                ['content' => 'Cela complique la gestion', 'correct' => false],
                                ['content' => 'Cela n\'aide pas du tout', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'L\'engagement public (dire son objectif à d\'autres) aide car :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Cela crée de la pression sociale positive pour passer à l\'action', 'correct' => true],
                                ['content' => 'Cela permet de déléguer la tâche', 'correct' => false],
                                ['content' => 'Cela n\'a aucun effet', 'correct' => false],
                                ['content' => 'Cela augmente le stress négatif', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    // ─── COURS 6 : Résolution de Conflits & Négociation (ADVANCED) ───
    [
        'title'       => 'Conflits & Négociation',
        'description' => 'Maîtrisez la résolution de conflits (modèle Thomas-Kilmann), la négociation gagnant-gagnant (Harvard), la médiation et la gestion des personnalités difficiles.',
        'duration'    => 16,
        'difficulty'  => 'ADVANCED',
        'validation'  => 75,
        'content'     => 'Ce cours avancé vous prépare à gérer les situations conflictuelles et à négocier efficacement.',
        'sections'    => ['Thomas-Kilmann', 'Négociation Harvard', 'Médiation', 'Personnalités difficiles'],
        'chapters'    => [
            [
                'title'   => 'Styles de Gestion Conflits',
                'content' => 'Le modèle Thomas-Kilmann identifie 5 styles : Compétition (assertif, non coopératif), Collaboration (assertif, coopératif), Compromis (moyennement assertif et coopératif), Évitement (non assertif, non coopératif), Accommodation (non assertif, coopératif).',
                'order'   => 1,
                'quiz'    => [
                    'title'    => 'Quiz Thomas-Kilmann',
                    'passing'  => 75,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Combien de styles de gestion des conflits le modèle Thomas-Kilmann identifie-t-il ?',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => '3', 'correct' => false],
                                ['content' => '4', 'correct' => false],
                                ['content' => '5', 'correct' => true],
                                ['content' => '7', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le style « Collaboration » est :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Assertif et coopératif', 'correct' => true],
                                ['content' => 'Non assertif et non coopératif', 'correct' => false],
                                ['content' => 'Assertif mais non coopératif', 'correct' => false],
                                ['content' => 'Non assertif mais coopératif', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le style « Évitement » est approprié quand :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'L\'enjeu est majeur et critique', 'correct' => false],
                                ['content' => 'Le conflit est mineur ou le timing est mauvais', 'correct' => true],
                                ['content' => 'On veut gagner à tout prix', 'correct' => false],
                                ['content' => 'Il faut une solution immédiate', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Le « Compromis » implique :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Que les deux parties obtiennent tout ce qu\'elles veulent', 'correct' => false],
                                ['content' => 'Que chaque partie fait des concessions pour trouver un terrain d\'entente', 'correct' => true],
                                ['content' => 'Qu\'une partie gagne et l\'autre perd', 'correct' => false],
                                ['content' => 'Qu\'on ignore le problème', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'L\'« Accommodation » est utile quand :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'L\'enjeu est crucial pour vous', 'correct' => false],
                                ['content' => 'Préserver la relation est plus important que l\'enjeu', 'correct' => true],
                                ['content' => 'Vous voulez dominer la situation', 'correct' => false],
                                ['content' => 'Vous êtes en position de force', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'   => 'Négociation Harvard',
                'content' => 'La méthode de Harvard repose sur 4 principes : séparer les personnes du problème, se concentrer sur les intérêts (pas les positions), inventer des options pour un gain mutuel, utiliser des critères objectifs. C\'est une approche gagnant-gagnant.',
                'order'   => 2,
                'quiz'    => [
                    'title'    => 'Quiz Négociation Harvard',
                    'passing'  => 75,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Le premier principe de la méthode Harvard est :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Commencer par le prix le plus bas', 'correct' => false],
                                ['content' => 'Séparer les personnes du problème', 'correct' => true],
                                ['content' => 'Menacer de partir', 'correct' => false],
                                ['content' => 'Faire une offre agressive', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La différence entre « positions » et « intérêts » est :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Il n\'y a aucune différence', 'correct' => false],
                                ['content' => 'La position est ce qu\'on demande. L\'intérêt est pourquoi on le demande.', 'correct' => true],
                                ['content' => 'L\'intérêt est plus visible que la position', 'correct' => false],
                                ['content' => 'La position vient après l\'accord', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La BATNA (Best Alternative to a Negotiated Agreement) est :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Le premier prix proposé', 'correct' => false],
                                ['content' => 'La meilleure alternative si la négociation échoue', 'correct' => true],
                                ['content' => 'Le prix maximum à payer', 'correct' => false],
                                ['content' => 'Un bluff stratégique', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => '« Inventer des options pour un gain mutuel » signifie :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Trouver des solutions créatives où les deux parties gagnent', 'correct' => true],
                                ['content' => 'Proposer le plus d\'options pour embrouiller l\'autre', 'correct' => false],
                                ['content' => 'Copier la stratégie de l\'adversaire', 'correct' => false],
                                ['content' => 'Accepter toutes les demandes de l\'autre', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Les « critères objectifs » dans Harvard servent à :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Manipuler l\'autre partie avec des données', 'correct' => false],
                                ['content' => 'Baser l\'accord sur des standards indépendants et équitables', 'correct' => true],
                                ['content' => 'Impressionner l\'adversaire', 'correct' => false],
                                ['content' => 'Allonger la négociation', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'   => 'Médiation & Pers. Difficiles',
                'content' => 'La médiation est un processus structuré où un tiers neutre aide les parties à trouver un accord. Elle suit les étapes : cadrage, expression, clarification, recherche de solutions, accord. Pour les personnalités difficiles, utiliser la CNV (Communication NonViolente).',
                'order'   => 3,
                'quiz'    => [
                    'title'    => 'Quiz Médiation & CNV',
                    'passing'  => 75,
                    'attempts' => 3,
                    'questions' => [
                        [
                            'content' => 'Le médiateur est :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Un juge qui tranche le conflit', 'correct' => false],
                                ['content' => 'Un tiers neutre qui facilite la communication entre les parties', 'correct' => true],
                                ['content' => 'Un avocat qui défend une partie', 'correct' => false],
                                ['content' => 'Le supérieur hiérarchique', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Les 4 étapes de la CNV (Marshall Rosenberg) sont :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Accuser, Exiger, Menacer, Punir', 'correct' => false],
                                ['content' => 'Observer, Sentiment, Besoin, Demande', 'correct' => true],
                                ['content' => 'Écouter, Analyser, Répondre, Conclure', 'correct' => false],
                                ['content' => 'Parler, Négocier, Accepter, Signer', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'Face à une personnalité agressive, la meilleure approche est :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Répondre avec la même agressivité', 'correct' => false],
                                ['content' => 'Rester calme, écouter puis recadrer avec assertivité', 'correct' => true],
                                ['content' => 'Fuir immédiatement', 'correct' => false],
                                ['content' => 'Accepter tout pour éviter le conflit', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'En CNV, « Observer sans juger » signifie :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'Ne rien dire du tout', 'correct' => false],
                                ['content' => 'Décrire les faits sans interprétation ni évaluation', 'correct' => true],
                                ['content' => 'Observer en silence sans réagir', 'correct' => false],
                                ['content' => 'Juger en silence', 'correct' => false],
                            ],
                        ],
                        [
                            'content' => 'La médiation aboutit idéalement à :',
                            'type'    => 'single',
                            'point'   => 2,
                            'answers' => [
                                ['content' => 'La victoire d\'une partie', 'correct' => false],
                                ['content' => 'Un accord mutuellement acceptable construit par les parties elles-mêmes', 'correct' => true],
                                ['content' => 'Une sanction', 'correct' => false],
                                ['content' => 'La fin de toute relation entre les parties', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

];

// ════════════════════════════════════════════════════════════════════
// Création en base de données
// ════════════════════════════════════════════════════════════════════

$previousCourseQuiz = null; // Quiz du cours précédent → sert de prerequisite pour le suivant
$courseCount = 0;

foreach ($coursesData as $courseData) {
    $courseCount++;
    echo "── Cours $courseCount : {$courseData['title']} ──\n";

    // ── Créer le cours ──
    $course = new Course();
    $course->setTitle($courseData['title']);
    $course->setDescription($courseData['description']);
    $course->setDuration($courseData['duration']);
    $course->setDifficulty($courseData['difficulty']);
    $course->setValidationScore($courseData['validation']);
    $course->setContent($courseData['content']);
    $course->setCreator($creator);
    $course->setIsActive(true);

    if (!empty($courseData['sections'])) {
        $course->setSectionsToReview($courseData['sections']);
    }

    // ── Chaîne de prérequis : le quiz du cours précédent ──
    if ($previousCourseQuiz !== null) {
        $course->setPrerequisiteQuiz($previousCourseQuiz);
        echo "   → Prérequis : quiz \"{$previousCourseQuiz->getTitle()}\" du cours précédent\n";
    }

    $em->persist($course);

    // ── Créer les chapitres et quizzes ──
    $lastQuizOfCourse = null;

    foreach ($courseData['chapters'] as $chapterData) {
        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle($chapterData['title']);
        $chapter->setContent($chapterData['content']);
        $chapter->setChapterOrder($chapterData['order']);
        $chapter->setStatus('published');
        $chapter->setMinScore($courseData['validation']);
        $em->persist($chapter);

        echo "   Chapitre {$chapterData['order']} : {$chapterData['title']}\n";

        // ── Créer le quiz du chapitre ──
        if (isset($chapterData['quiz'])) {
            $quizData = $chapterData['quiz'];

            $quiz = new Quiz();
            $quiz->setTitle($quizData['title']);
            $quiz->setCourse($course);
            $quiz->setChapter($chapter);
            $quiz->setPassingScore($quizData['passing']);
            $quiz->setMaxAttempts($quizData['attempts']);
            $quiz->setQuestionsPerAttempt(count($quizData['questions']));
            $quiz->setSupervisor($creator);
            $em->persist($quiz);

            echo "     Quiz : {$quizData['title']} ({$quizData['passing']}% pour passer)\n";

            // ── Créer les questions et réponses ──
            $qNum = 0;
            foreach ($quizData['questions'] as $questionData) {
                $qNum++;
                $question = new Question();
                $question->setQuiz($quiz);
                $question->setContent($questionData['content']);
                $question->setType($questionData['type']);
                $question->setPoint($questionData['point']);
                $em->persist($question);

                foreach ($questionData['answers'] as $answerData) {
                    $answer = new Answer();
                    $answer->setQuestion($question);
                    $answer->setContent($answerData['content']);
                    $answer->setIsCorrect($answerData['correct']);
                    $em->persist($answer);
                }
            }

            echo "     → $qNum questions créées\n";

            $lastQuizOfCourse = $quiz;
        }
    }

    // Le dernier quiz de ce cours servira de prérequis pour le cours suivant
    $previousCourseQuiz = $lastQuizOfCourse;

    // Flush après chaque cours pour que les IDs soient disponibles
    $em->flush();

    echo "\n";
}

echo "=== Seed Soft Skills : Terminé ! ===\n";
echo "Total : $courseCount cours créés avec chapitres, quizzes, questions et dépendances.\n\n";
echo "Arbre de dépendances :\n";
echo "  1. Communication Efficace (BEGINNER) — aucun prérequis\n";
echo "  2. Intelligence Émotionnelle (BEGINNER) — prérequis : quiz final Communication\n";
echo "  3. Travail d'Équipe & Collab. (INTERMEDIATE) — prérequis : quiz final IE\n";
echo "  4. Leadership & Influence (INTERMEDIATE) — prérequis : quiz final Équipe\n";
echo "  5. Gestion du Temps & Product. (ADVANCED) — prérequis : quiz final Leadership\n";
echo "  6. Conflits & Négociation (ADVANCED) — prérequis : quiz final Temps\n";
