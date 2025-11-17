// ===== AI CHAT FUNCTIONALITY =====
const chatToggleBtn = document.getElementById('chat-toggle-button');
const chatContainer = document.getElementById('chat-container');
const closeBtn = document.querySelector('.close-btn');
const chatForm = document.getElementById('chat-form');
const chatInput = document.querySelector('.chat-input');
const chatLog = document.getElementById('chat-log');


async function fetchProducts() {
    try {
        const res = await fetch('./component/api/get_products.php'); // wait for fetch
        const json = await res.json(); // wait for JSON parsing

        if (!json.success) throw new Error(json.error);

        const product_items = json.data.map(p => ({
            id: p.product_id,
            name: p.name,
            price: p.price,
            plan_type: p.plan_type,
            security_features: p.security_features,
            eye_catch: p.eye_catch,
            duration_months: p.duration_months,
            description: p.description,
            category_name: p.category_name
        }));
        console.log(product_items);
        localStorage.setItem('products', JSON.stringify(product_items));
    } catch (err) {
        console.error('Failed to fetch custom data:', err);
    }
}



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
    const product_items = JSON.parse(localStorage.getItem('products') || '[]');

    chatForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const message = chatInput.value.trim();
        if (!message) return;

        addMessage(message, 'user');
        chatInput.value = '';
        chatInput.style.height = 'auto';


        const productInfoText = product_items.map(p => {
            return `商品名: ${p.name}, 価格: ${p.price}円, プランタイプ: ${p.plan_type}, 期間: ${p.duration_months}ヶ月, 特徴: ${p.security_features}, カテゴリ: ${p.category_name}`;
        }).join('\n');
        console.log(productInfoText);
        const prompt = `
会社紹介：

\n

Q&A（よくある質問とその回答）：

\n


製品情報：${productInfoText}\n
提供された「会社紹介」と「Q&A」と　「製品情報」（よくある質問とその回答）」の内容のみに基づいてユーザーの質問に回答してください。
それ以外の質問にはすべて「申し訳ありませんが、該当する質問が見つかりませんでした」とお答えください。以下のルールを厳守してください。

ユーザーの質問: "${message}"
【回答形式ルール】
1. 提供された「会社紹介」と「Q&A」と　「製品情報」の内容のみに基づいて質問に回答してください。

2. 質問がこれらの範囲に該当しない場合は、必ず「申し訳ありませんが、該当する質問が見つかりませんでした」だけを返答し、それ以外の言葉は一切返答しないでください。

3. 回答は必ず簡潔かつ短くしてください。説明や余計な言葉を付け加えないでください。

4. 回答は必ず日本語で回答してください。

5. 回答は必ず Markdown 形式で、製品ごとに箇条書きで表示してください。

6.「ユーザーの質問」を繰り返さず、質問に対する答えだけを返してください。

`;

        try {
            const aiReply = await callGeminiAPI(prompt);
            addMessage(aiReply, 'bot');
        } catch (error) {
            addMessage(`⚠️ ${error.message}`, 'bot');
        }
    });
}


// Function to add messages to chat
function addMessage(content, sender) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}`;

    const contentDiv = document.createElement('div');
    contentDiv.className = 'message-content';

    
    let html = content;
    if (!content.includes('- ') && !content.includes('\n')) {
        html = content.split(',').map(item => item.trim()).join('<br>');
    }

    
    contentDiv.innerHTML = marked.parse(html);

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
// Call the function
fetchProducts();