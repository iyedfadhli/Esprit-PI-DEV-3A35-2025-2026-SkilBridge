const chatBubbles = document.querySelectorAll('.chat-bubble');
const chatWindows = document.querySelectorAll('.chat-window');
const searchInput = document.getElementById('chat-search');

// Filter chat bubbles
searchInput.addEventListener('input', () => {
    const value = searchInput.value.toLowerCase();
    chatBubbles.forEach(bubble => {
        const name = bubble.dataset.group.toLowerCase();
        bubble.style.display = name.includes(value) ? 'flex' : 'none';
    });
});

// Open/close chat windows
chatBubbles.forEach(bubble => {
    bubble.addEventListener('click', () => {
        const groupName = bubble.dataset.group;
        const chatWindow = document.querySelector(`.chat-window[data-group='${groupName}']`);
        if (!chatWindow) return;

        chatWindow.style.display = chatWindow.style.display === 'flex' ? 'none' : 'flex';
    });
});

// Close buttons
chatWindows.forEach(window => {
    const closeBtn = window.querySelector('.close-btn');
    closeBtn.addEventListener('click', () => {
        window.style.display = 'none';
    });
});

// Send message
chatWindows.forEach(window => {
    const input = window.querySelector('input');
    const sendBtn = window.querySelector('.send-btn');
    const messages = window.querySelector('.chat-messages');

    const sendMessage = () => {
        if (!input.value.trim()) return;
        const msg = document.createElement('div');
        msg.classList.add('message', 'self');
        msg.textContent = input.value;
        messages.appendChild(msg);
        input.value = '';
        messages.scrollTop = messages.scrollHeight;
    };

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', e => {
        if (e.key === 'Enter') sendMessage();
    });
});
