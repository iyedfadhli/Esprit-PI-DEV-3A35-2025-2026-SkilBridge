/**
 * Quiz Application - Student Quiz Interface
 * High-quality interactive quiz experience with server-side timer support
 */
document.addEventListener('DOMContentLoaded', function () {
  const app = document.getElementById('quiz-app');
  if (!app) return;

  // Configuration from data attributes
  const quizId = app.dataset.quizId;
  const initialAttemptId = app.dataset.attemptId;   // may be overridden by API
  const quizTitle = app.dataset.quizTitle || 'Quiz';
  const passingScore = parseInt(app.dataset.passingScore) || 70;

  // DOM Elements
  const startScreen = document.getElementById('quiz-start-screen');
  const loadingScreen = document.getElementById('quiz-loading');
  const progressScreen = document.getElementById('quiz-progress-screen');
  const submitModal = document.getElementById('submit-modal');

  const startBtn = document.getElementById('start-quiz');
  const prevBtn = document.getElementById('prev-question');
  const nextBtn = document.getElementById('next-question');
  const finishBtn = document.getElementById('finish-quiz');
  const cancelSubmitBtn = document.getElementById('cancel-submit');
  const confirmSubmitBtn = document.getElementById('confirm-submit');

  const progressText = document.getElementById('progress-text');
  const progressFill = document.getElementById('progress-fill');
  const questionBadge = document.getElementById('question-badge');
  const questionNumber = document.getElementById('question-number');
  const questionText = document.getElementById('question-text');
  const answersContainer = document.getElementById('answers-container');
  const questionDots = document.getElementById('question-dots');
  const answeredCount = document.getElementById('answered-count');
  const unansweredCount = document.getElementById('unanswered-count');

  // Timer DOM
  const timerContainer = document.getElementById('quiz-timer');
  const timerDisplay = document.getElementById('timer-display');

  // State
  let questions = [];
  let currentIndex = 0;
  let selections = {};            // { questionIndex: answerId }
  let questionToAnswerMap = {};    // { questionId: answerId } for submission
  let activeAttemptId = null;      // set by server after start-attempt
  let timeLimitSeconds = 0;        // 0 = unlimited
  let remainingSeconds = null;     // countdown value (null = no limit)
  let timerInterval = null;        // setInterval handle
  let isSubmitting = false;        // guard against double-submit

  // ─── Timer helpers ──────────────────────────────────────────────

  /**
   * Format seconds into MM:SS or HH:MM:SS
   */
  function formatTime(totalSec) {
    if (totalSec == null || totalSec < 0) totalSec = 0;
    const h = Math.floor(totalSec / 3600);
    const m = Math.floor((totalSec % 3600) / 60);
    const s = totalSec % 60;
    const mm = String(m).padStart(2, '0');
    const ss = String(s).padStart(2, '0');
    return h > 0 ? `${h}:${mm}:${ss}` : `${mm}:${ss}`;
  }

  /**
   * Start the visual countdown timer.
   */
  function startTimer(seconds) {
    remainingSeconds = Math.max(0, Math.floor(seconds));
    timeLimitSeconds = remainingSeconds;

    if (remainingSeconds <= 0) {
      // No time limit → keep timer hidden
      if (timerContainer) timerContainer.style.display = 'none';
      return;
    }

    // Show timer
    if (timerContainer) timerContainer.style.display = 'flex';
    updateTimerDisplay();

    timerInterval = setInterval(() => {
      remainingSeconds--;
      updateTimerDisplay();

      if (remainingSeconds <= 0) {
        clearInterval(timerInterval);
        timerInterval = null;
        handleTimeExpired();
      }
    }, 1000);
  }

  /**
   * Update the timer text and apply urgency classes.
   */
  function updateTimerDisplay() {
    if (!timerDisplay) return;
    timerDisplay.textContent = formatTime(remainingSeconds);

    // Visual urgency (matches CSS: timer-warning / timer-critical)
    if (timerContainer) {
      timerContainer.classList.remove('timer-warning', 'timer-critical');
      if (remainingSeconds <= 30) {
        timerContainer.classList.add('timer-critical');
      } else if (remainingSeconds <= 60) {
        timerContainer.classList.add('timer-warning');
      }
    }
  }

  /**
   * Called when the countdown reaches 0. Auto-submits the quiz.
   */
  function handleTimeExpired() {
    // Close any open modal
    hideSubmitModal();
    submitQuiz(true);   // force = true  →  auto-submit, skip confirmation
  }

  /**
   * Stop the countdown (e.g. after submission).
   */
  function stopTimer() {
    if (timerInterval) {
      clearInterval(timerInterval);
      timerInterval = null;
    }
  }

  // ─── Quiz lifecycle ─────────────────────────────────────────────

  /**
   * Initialize and start the quiz via the timer-aware endpoint.
   */
  async function startQuiz() {
    startScreen.style.display = 'none';
    loadingScreen.style.display = 'block';

    try {
      const resp = await fetch(`/api/quizzes/${quizId}/start-attempt`, { method: 'POST' });

      const data = await resp.json();

      // Handle specific error statuses
      if (resp.status === 410) {
        // Previous attempt expired
        alert(data.message || 'Votre tentative précédente a expiré.');
        window.location.reload();
        return;
      }
      if (resp.status === 403) {
        alert(`Nombre maximum de tentatives atteint (${data.max_attempts}).`);
        window.location.reload();
        return;
      }
      if (!resp.ok) {
        throw new Error(data.error || 'Failed to start quiz');
      }

      // Store server-assigned attempt id
      activeAttemptId = data.attempt_id;
      questions = data.questions || [];

      if (questions.length === 0) {
        alert('Aucune question disponible pour ce quiz.');
        window.location.reload();
        return;
      }

      // Initialize UI
      loadingScreen.style.display = 'none';
      progressScreen.classList.add('active');

      initializeQuestionDots();
      renderQuestion();
      updateNavigation();

      // Start timer using remaining_seconds from server (handles resumed attempts)
      const serverRemaining = data.remaining_seconds;
      const serverTimeLimit = data.time_limit_seconds || 0;

      if (serverTimeLimit > 0 && serverRemaining != null) {
        startTimer(serverRemaining);
      } else if (serverTimeLimit > 0) {
        startTimer(serverTimeLimit);
      } else {
        // Unlimited quiz
        if (timerContainer) timerContainer.style.display = 'none';
      }

    } catch (error) {
      console.error('Error starting quiz:', error);
      alert('Impossible de démarrer le quiz. Veuillez réessayer.');
      loadingScreen.style.display = 'none';
      startScreen.style.display = 'block';
    }
  }

  /**
   * Initialize question navigation dots
   */
  function initializeQuestionDots() {
    questionDots.innerHTML = '';
    questions.forEach((_, index) => {
      const dot = document.createElement('button');
      dot.className = 'question-dot';
      dot.textContent = index + 1;
      dot.addEventListener('click', () => goToQuestion(index));
      questionDots.appendChild(dot);
    });
    updateQuestionDots();
  }

  /**
   * Update question dots state
   */
  function updateQuestionDots() {
    const dots = questionDots.querySelectorAll('.question-dot');
    dots.forEach((dot, index) => {
      dot.classList.remove('current', 'answered');
      if (index === currentIndex) {
        dot.classList.add('current');
      } else if (selections[index] !== undefined) {
        dot.classList.add('answered');
      }
    });
  }

  /**
   * Render the current question
   */
  function renderQuestion() {
    const q = questions[currentIndex];
    if (!q) return;

    const progress = ((currentIndex + 1) / questions.length) * 100;
    progressText.textContent = `Question ${currentIndex + 1} sur ${questions.length}`;
    progressFill.style.width = `${progress}%`;
    questionNumber.textContent = currentIndex + 1;

    questionText.textContent = q.text;

    answersContainer.innerHTML = '';
    const letters = ['A', 'B', 'C', 'D', 'E', 'F'];

    q.answers.forEach((answer, idx) => {
      const answerDiv = document.createElement('div');
      answerDiv.className = 'answer-option';
      if (selections[currentIndex] === answer.id) {
        answerDiv.classList.add('selected');
      }

      answerDiv.innerHTML = `
        <div class="answer-indicator">${letters[idx] || idx + 1}</div>
        <div class="answer-text">${answer.text}</div>
      `;

      answerDiv.addEventListener('click', () => selectAnswer(answer.id, q.id, answerDiv));
      answersContainer.appendChild(answerDiv);
    });

    updateQuestionDots();
    updateNavigation();
  }

  /**
   * Select an answer
   */
  function selectAnswer(answerId, questionId, element) {
    selections[currentIndex] = answerId;
    questionToAnswerMap[questionId] = answerId;

    answersContainer.querySelectorAll('.answer-option').forEach(opt => {
      opt.classList.remove('selected');
    });
    element.classList.add('selected');

    updateQuestionDots();

    // Auto-advance after a short delay
    if (currentIndex < questions.length - 1) {
      setTimeout(() => {
        goToQuestion(currentIndex + 1);
      }, 300);
    }
  }

  /**
   * Navigate to a specific question
   */
  function goToQuestion(index) {
    if (index < 0 || index >= questions.length) return;
    currentIndex = index;
    renderQuestion();
  }

  /**
   * Update navigation buttons
   */
  function updateNavigation() {
    prevBtn.disabled = currentIndex === 0;

    if (currentIndex === questions.length - 1) {
      nextBtn.style.display = 'none';
      finishBtn.style.display = 'flex';
    } else {
      nextBtn.style.display = 'flex';
      finishBtn.style.display = 'none';
    }
  }

  /**
   * Show submit confirmation modal
   */
  function showSubmitModal() {
    const answered = Object.keys(selections).length;
    const unanswered = questions.length - answered;

    answeredCount.textContent = answered;
    unansweredCount.textContent = unanswered;

    submitModal.classList.add('active');
  }

  /**
   * Hide submit confirmation modal
   */
  function hideSubmitModal() {
    submitModal.classList.remove('active');
  }

  /**
   * Submit the quiz via the timer-validated endpoint.
   * @param {boolean} forcedByTimer – true when auto-submitted on time expiry
   */
  async function submitQuiz(forcedByTimer = false) {
    if (isSubmitting) return;      // guard double-submit
    isSubmitting = true;
    stopTimer();
    hideSubmitModal();

    // Show loading state
    progressScreen.innerHTML = `
      <div class="quiz-loading" style="display: block;">
        <div class="spinner"></div>
        <p>${forcedByTimer ? 'Temps écoulé ! Soumission automatique...' : 'Soumission en cours...'}</p>
      </div>
    `;

    try {
      const effectiveAttemptId = activeAttemptId || initialAttemptId;

      const payload = { responses: questionToAnswerMap };

      const resp = await fetch(
        `/api/quizzes/${quizId}/attempts/${effectiveAttemptId}/submit`,
        {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        }
      );

      const result = await resp.json();

      if (resp.status === 400 && result.status === 'EXPIRED') {
        // Server confirmed expiry
        alert(result.message || 'Le temps imparti est dépassé. Vos réponses ont été enregistrées.');
        window.location.href = `/student/quiz/result/${result.attempt_id || effectiveAttemptId}`;
        return;
      }

      if (resp.status === 409) {
        alert(result.error || 'Cette tentative a déjà été soumise.');
        window.location.href = '/student/dashboard';
        return;
      }

      if (!resp.ok) {
        throw new Error(result.error || 'Submission failed');
      }

      // Success – redirect to result page
      const redirectId = result.attempt_id || result.attemptId || effectiveAttemptId;
      window.location.href = `/student/quiz/result/${redirectId}`;

    } catch (error) {
      console.error('Error submitting quiz:', error);
      isSubmitting = false;
      alert('Erreur lors de la soumission. Veuillez réessayer.');
      window.location.reload();
    }
  }

  // ─── Event Listeners ────────────────────────────────────────────

  startBtn.addEventListener('click', startQuiz);

  prevBtn.addEventListener('click', () => {
    if (currentIndex > 0) goToQuestion(currentIndex - 1);
  });

  nextBtn.addEventListener('click', () => {
    if (currentIndex < questions.length - 1) goToQuestion(currentIndex + 1);
  });

  finishBtn.addEventListener('click', showSubmitModal);
  cancelSubmitBtn.addEventListener('click', hideSubmitModal);
  confirmSubmitBtn.addEventListener('click', () => submitQuiz(false));

  // Close modal on outside click
  submitModal.addEventListener('click', (e) => {
    if (e.target === submitModal) hideSubmitModal();
  });

  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (!progressScreen.classList.contains('active')) return;

    switch (e.key) {
      case 'ArrowLeft':
        if (currentIndex > 0) goToQuestion(currentIndex - 1);
        break;
      case 'ArrowRight':
        if (currentIndex < questions.length - 1) goToQuestion(currentIndex + 1);
        break;
      case '1': case '2': case '3': case '4': case '5': case '6':
        const answerIndex = parseInt(e.key) - 1;
        const answerOptions = answersContainer.querySelectorAll('.answer-option');
        if (answerOptions[answerIndex]) answerOptions[answerIndex].click();
        break;
    }
  });

  // Warn before closing / navigating away while quiz is in progress
  window.addEventListener('beforeunload', (e) => {
    if (activeAttemptId && !isSubmitting) {
      e.preventDefault();
      e.returnValue = '';
    }
  });
});