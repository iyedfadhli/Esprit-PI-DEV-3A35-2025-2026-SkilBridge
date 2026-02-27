/**
 * Quiz Application - Student Quiz Interface
 * High-quality interactive quiz experience
 */
document.addEventListener('DOMContentLoaded', function () {
  const app = document.getElementById('quiz-app');
  if (!app) return;

  // Configuration from data attributes
  const quizId = app.dataset.quizId;
  const attemptId = app.dataset.attemptId;
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

  // State
  let questions = [];
  let currentIndex = 0;
  let selections = {}; // { questionIndex: answerId }
  let questionToAnswerMap = {}; // { questionId: answerId } for submission

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
    
    submitModal.classList.add('active');
  }

  /**
   * Hide submit confirmation modal
   */
  function hideSubmitModal() {
    submitModal.classList.remove('active');
  }

  /**
   * Submit the quiz
   */
  async function submitQuiz() {
    hideSubmitModal();
    
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
      
      const resp = await fetch(`/api/quizzes/${quizId}/submit`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      
      if (!resp.ok) {
        throw new Error('Submission failed');
      }
      
      const result = await resp.json();
      
      // Redirect to result page
      if (result.attemptId) {
        window.location.href = `/student/quiz/result/${result.attemptId}`;
      } else {
        alert('Quiz soumis avec succès!');
        window.location.href = '/student/dashboard';
      }
      
    } catch (error) {
      console.error('Error submitting quiz:', error);
      alert('Erreur lors de la soumission. Veuillez réessayer.');
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