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
        const resUserMessage = "会社紹介：   Q&A:      ユーザーの質問：" + message + "。" + `提供された「会社紹介」と「Q&A（よくある質問とその回答）」の内容のみに基づいてユーザーの質問に回答してください。
それ以外の質問にはすべて「申し訳ありませんが、該当する質問が見つかりませんでした」とお答えください。以下のルールを厳守してください。

1. 提供された「会社紹介」と「Q&A」の内容のみに基づいて質問に回答してください。

2. 質問がこれらの範囲に該当しない場合は、必ず「申し訳ありませんが、該当する質問が見つかりませんでした」だけを返答し、それ以外の言葉は一切返答しないでください。

3. 回答は必ず簡潔かつ短くしてください。説明や余計な言葉を付け加えないでください。
`;
        // Add user message
        addMessage(message, 'user');
        chatInput.value = '';
        chatInput.style.height = 'auto';

        //api call
        try {
            const aiReply = await callGeminiAPI(resUserMessage);
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
    const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=AIzaSyDYuVhc0z89JA4BYW9G8x6mvvT4NnqCboU`;

    const requestBody = {
        contents: [
            {
                parts: [
                    {
                        text: message
                    }
                ]
            }
        ]
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
    return data.candidates[0].content.parts[0].text;
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
