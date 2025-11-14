// ===== AI CHAT FUNCTIONALITY =====
const chatToggleBtn = document.getElementById('chat-toggle-button');
const chatContainer = document.getElementById('chat-container');
const closeBtn = document.querySelector('.close-btn');
const chatForm = document.getElementById('chat-form');
const chatInput = document.querySelector('.chat-input');
const chatLog = document.getElementById('chat-log');

// Toggle chat visibility
if (chatToggleBtn && chatContainer) {
    chatToggleBtn.addEventListener('click', function () {
        chatContainer.classList.add('open');
        chatToggleBtn.classList.add('hidden');
    });
}

if (closeBtn && chatContainer) {
    closeBtn.addEventListener('click', function () {
        chatContainer.classList.remove('open');
        chatToggleBtn.classList.remove('hidden');
    });
}

// Auto-resize textarea
if (chatInput) {
    chatInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
}

// Chat form submission
if (chatForm) {
    chatForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        addMessage(message, 'user');
        chatInput.value = '';
        chatInput.style.height = 'auto';

        //api call
        try {
            // ここで渡すのを「resUserMessage」から「message」に変更します
            const aiReply = await callGeminiAPI(message); 
            console.log(aiReply);
            addMessage(aiReply, 'bot');
        } catch (error) {
            addMessage( `⚠️ ${error.message}` , 'bot');
        }
    });
}


// Function to add messages to chat
function addMessage(content, sender) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}`;

    const contentDiv = document.createElement('div');
    contentDiv.className = 'message-content';
    contentDiv.textContent = content;

    const timeDiv = document.createElement('div');
    timeDiv.className = 'message-time';
    timeDiv.textContent = getCurrentTime();

    messageDiv.appendChild(contentDiv);
    messageDiv.appendChild(timeDiv);

    chatLog.appendChild(messageDiv);
    chatLog.scrollTop = chatLog.scrollHeight;
}

// Function to get current time
function getCurrentTime() {
    const now = new Date();
    return now.toLocaleTimeString('ja-JP', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Function to call Gemini API
async function callGeminiAPI(message) {
    // Gemini APIのURLやキーは削除します
    // 代わりに、サーバー上のPHPファイルへのパスを指定します
    const url = 'component/api.php'; // 例: 同じ階層に api.php を作る場合

    const requestBody = {
        message: message // ユーザーの質問だけを送る
    };

    const response = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(requestBody),
    });

    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`API Error: ${errorText}`);
    }

    const data = await response.json();

    // PHPから返ってきた 'reply' というキーのテキストを返す
    if (data.reply) {
        return data.reply;
    } else {
        throw new Error('APIからの応答が不正です。');
    }
}


document.addEventListener('keydown', function (e) {
    // ESC key closes chat
    if (e.key === 'Escape' && chatContainer && chatContainer.classList.contains('open')) {
        chatContainer.classList.remove('open');
    }

    // Enter key in chat input
    if (e.key === 'Enter' && e.target === chatInput && !e.shiftKey) {
        e.preventDefault();
        chatForm.dispatchEvent(new Event('submit'));
    }
});
