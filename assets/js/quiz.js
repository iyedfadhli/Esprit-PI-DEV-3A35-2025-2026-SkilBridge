/**
 * Quiz Application - Student Quiz Interface
 * With secure server-side timer & auto-submit on expiry
 */
document.addEventListener('DOMContentLoaded', function () {
  const app = document.getElementById('quiz-app');
  if (!app) return;

  // Configuration from data attributes
  const quizId = app.dataset.quizId;
  const attemptId = app.dataset.attemptId;
  const quizTitle = app.dataset.quizTitle || 'Quiz';
  const passingScore = parseInt(app.dataset.passingScore) || 70;
  const timeLimit = parseInt(app.dataset.timeLimit) || 0; // minutes, 0 = unlimited
  
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

  // Timer elements
  const timerContainer = document.getElementById('quiz-timer');
  const timerDisplay = document.getElementById('timer-display');

  // State
  let questions = [];
  let currentIndex = 0;
  let selections = {}; // { questionIndex: answerId }
  let questionToAnswerMap = {}; // { questionId: answerId } for submission
  let timerInterval = null;
  let remainingSeconds = 0;
  let isSubmitting = false; // prevent double-submit

  /**
   * Initialize and start the quiz
   */
  async function startQuiz() {
    // Show loading
    startScreen.style.display = 'none';
    loadingScreen.style.display = 'block';
    
    try {
      const resp = await fetch(`/api/quizzes/${quizId}/start`, { method: 'POST' });
      if (!resp.ok) {
        throw new Error('Failed to start quiz');
      }
      
      const data = await resp.json();
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

      // ── Start the countdown timer if timeLimit > 0 ──
      if (timeLimit > 0) {
        remainingSeconds = timeLimit * 60;
        timerContainer.style.display = 'flex';
        updateTimerDisplay();
        timerInterval = setInterval(timerTick, 1000);
      }
      
    } catch (error) {
      console.error('Error starting quiz:', error);
      alert('Impossible de démarrer le quiz. Veuillez réessayer.');
      loadingScreen.style.display = 'none';
      startScreen.style.display = 'block';
    }
  }

  // ════════════════════════════════════════════════════════════════
  // TIMER – Countdown & auto-submit on expiry
  // ════════════════════════════════════════════════════════════════

  /**
   * Called every second while the quiz is in progress.
   * When remaining time hits 0, auto-submits the quiz.
   */
  function timerTick() {
    remainingSeconds--;

    updateTimerDisplay();

    // Warning state: last 60 seconds
    if (remainingSeconds <= 60 && remainingSeconds > 0) {
      timerContainer.classList.add('timer-warning');
    }

    // Critical state: last 10 seconds
    if (remainingSeconds <= 10 && remainingSeconds > 0) {
      timerContainer.classList.remove('timer-warning');
      timerContainer.classList.add('timer-critical');
    }

    // ╔══════════════════════════════════════════════════════════╗
    // ║  TEMPS ÉCOULÉ → Auto-soumission immédiate              ║
    // ║  Le backend validera aussi côté serveur (double check)  ║
    // ╚══════════════════════════════════════════════════════════╝
    if (remainingSeconds <= 0) {
      clearInterval(timerInterval);
      timerInterval = null;
      autoSubmitExpired();
    }
  }

  /**
   * Update the timer visual display (MM:SS format)
   */
  function updateTimerDisplay() {
    const mins = Math.max(0, Math.floor(remainingSeconds / 60));
    const secs = Math.max(0, remainingSeconds % 60);
    timerDisplay.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
  }

  /**
   * Auto-submit when time expires.
   * Shows an overlay message then submits whatever answers exist.
   */
  async function autoSubmitExpired() {
    if (isSubmitting) return;
    isSubmitting = true;

    // Close any open modal
    hideSubmitModal();

    // Show time-up overlay
    progressScreen.innerHTML = `
      <div class="quiz-loading" style="display: block;">
        <div class="timer-expired-icon" style="font-size: 3rem; margin-bottom: 1rem;">⏰</div>
        <h2 style="color: #e74c3c; margin-bottom: 0.5rem;">Temps écoulé !</h2>
        <p>Soumission automatique de vos réponses...</p>
        <div class="spinner"></div>
      </div>
    `;

    try {
      const payload = {
        attemptId: parseInt(attemptId),
        answers: Object.values(selections),
        responses: questionToAnswerMap
      };

      const resp = await fetch(`/api/quizzes/${quizId}/attempts/${attemptId}/submit`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      const result = await resp.json();

      // Redirect to result page (works for both EXPIRED and SUBMITTED)
      if (result.attempt_id || result.attemptId) {
        const resultId = result.attempt_id || result.attemptId;
        window.location.href = `/student/quiz/result/${resultId}`;
      } else {
        window.location.href = `/student/quiz/result/${attemptId}`;
      }

    } catch (error) {
      console.error('Error auto-submitting expired quiz:', error);
      // Even on error, redirect to result page
      window.location.href = `/student/quiz/result/${attemptId}`;
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

    // Update progress
    const progress = ((currentIndex + 1) / questions.length) * 100;
    progressText.textContent = `Question ${currentIndex + 1} sur ${questions.length}`;
    progressFill.style.width = `${progress}%`;
    questionNumber.textContent = currentIndex + 1;
    
    // Update question text
    questionText.textContent = q.text;
    
    // Render answers
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
    // Store selection
    selections[currentIndex] = answerId;
    questionToAnswerMap[questionId] = answerId;
    
    // Update UI
    answersContainer.querySelectorAll('.answer-option').forEach(opt => {
      opt.classList.remove('selected');
    });
    element.classList.add('selected');
    
    updateQuestionDots();
    
    // Auto-advance after a short delay (optional)
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

    // Show remaining time in modal when timer is active
    const modalBody = submitModal.querySelector('.submit-modal-body');
    const existingTimerNote = modalBody?.querySelector('.timer-note');
    if (existingTimerNote) existingTimerNote.remove();
    if (timeLimit > 0 && remainingSeconds > 0) {
      const mins = Math.floor(remainingSeconds / 60);
      const secs = remainingSeconds % 60;
      const note = document.createElement('p');
      note.className = 'timer-note';
      note.style.cssText = 'color: #e67e22; font-weight: 600; margin-top: 0.5rem;';
      note.textContent = `⏱ Temps restant : ${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
      modalBody?.appendChild(note);
    }
    
    submitModal.classList.add('active');
  }

  /**
   * Hide submit confirmation modal
   */
  function hideSubmitModal() {
    submitModal.classList.remove('active');
  }

  /**
   * Submit the quiz (manual or timer-triggered)
   */
  async function submitQuiz() {
    if (isSubmitting) return;
    isSubmitting = true;

    hideSubmitModal();

    // Stop timer
    if (timerInterval) {
      clearInterval(timerInterval);
      timerInterval = null;
    }
    
    // Show loading state
    progressScreen.innerHTML = `
      <div class="quiz-loading" style="display: block;">
        <div class="spinner"></div>
        <p>Soumission en cours...</p>
      </div>
    `;
    
    try {
      // Build payload with question-answer mapping
      const payload = {
        attemptId: parseInt(attemptId),
        answers: Object.values(selections),
        responses: questionToAnswerMap
      };
      
      const resp = await fetch(`/api/quizzes/${quizId}/attempts/${attemptId}/submit`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      
      const result = await resp.json();

      // Handle both success (200) and expired (400) — backend always saves answers
      const resultId = result.attempt_id || result.attemptId || attemptId;
      window.location.href = `/student/quiz/result/${resultId}`;
      
    } catch (error) {
      console.error('Error submitting quiz:', error);
      alert('Erreur lors de la soumission. Veuillez réessayer.');
      isSubmitting = false;
      window.location.reload();
    }
  }

  // Event Listeners
  startBtn.addEventListener('click', startQuiz);
  
  prevBtn.addEventListener('click', () => {
    if (currentIndex > 0) {
      goToQuestion(currentIndex - 1);
    }
  });
  
  nextBtn.addEventListener('click', () => {
    if (currentIndex < questions.length - 1) {
      goToQuestion(currentIndex + 1);
    }
  });
  
  finishBtn.addEventListener('click', showSubmitModal);
  cancelSubmitBtn.addEventListener('click', hideSubmitModal);
  confirmSubmitBtn.addEventListener('click', submitQuiz);
  
  // Close modal on outside click
  submitModal.addEventListener('click', (e) => {
    if (e.target === submitModal) {
      hideSubmitModal();
    }
  });
  
  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (!progressScreen.classList.contains('active')) return;
    
    switch(e.key) {
      case 'ArrowLeft':
        if (currentIndex > 0) goToQuestion(currentIndex - 1);
        break;
      case 'ArrowRight':
        if (currentIndex < questions.length - 1) goToQuestion(currentIndex + 1);
        break;
      case '1':
      case '2':
      case '3':
      case '4':
      case '5':
      case '6':
        const answerIndex = parseInt(e.key) - 1;
        const answerOptions = answersContainer.querySelectorAll('.answer-option');
        if (answerOptions[answerIndex]) {
          answerOptions[answerIndex].click();
        }
        break;
    }
  });
});