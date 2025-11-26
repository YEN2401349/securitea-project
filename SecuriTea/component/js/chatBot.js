// ===== AI CHAT FUNCTIONALITY =====
const chatToggleBtn = document.getElementById('chat-toggle-button');
const chatContainer = document.getElementById('chat-container');
const closeBtn = document.querySelector('.close-btn');
const chatForm = document.getElementById('chat-form');
const chatInput = document.querySelector('.chat-input');
const chatLog = document.getElementById('chat-log');


async function fetchProducts() {
    try {
        const res = await fetch('./component/api/get_products.php');
        const json = await res.json();

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

        localStorage.setItem('products', JSON.stringify(product_items));
        return product_items;
    } catch (err) {
        console.error('Failed to fetch custom data:', err);
        return [];
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

    chatForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const product_items = JSON.parse(localStorage.getItem('products') || '[]');
        const message = chatInput.value.trim();
        if (!message) return;

        addMessage(message, 'user');
        chatInput.value = '';
        chatInput.style.height = 'auto';


        const productInfoText = product_items.map(p => {
            return `商品名: ${p.name}, 価格: ${p.price}円, プランタイプ: ${p.plan_type}, 期間: ${p.duration_months}ヶ月, 特徴: ${p.security_features}, カテゴリ: ${p.category_name}`;
        }).join('\n');
        const prompt = `
会社紹介：

\n

Q&A（よくある質問とその回答）：
Q　ライトプランについて
A　"ライトプランはウイルスやスパイウェア対策と危険サイトへのアクセスブロックだけといった
基本的な機能しかなく料金も安いため初めのセキュリティソフトの導入を検討されている方や
あまりインターネットを使わないがセキュリティ対策をしたい方向けのプランになっています"

Q　ファイヤーウォールとは何ですか？
A　外部の許可されていない通信から守るための「防火壁」です

Q　ポップアップ・広告ブロックとは何ですか？
A　サイトのアクセスなどで自動的に表示される広告や拡張機能を作動させないようにする機能です

Q　ペアレンタルコントロールとは何ですか？
A　保護者が子どものインターネットやスマートフォンの利用を制限・管理する機能で ON/OFF も可能です

Q　パフォーマンス最適化とは何ですか？
A　セキュリティソフトによって動作が重くならないように、機能維持しつつ軽量化を行う機能です

Q　VPN接続とは何ですか？
A　ユーザー個人にインターネット上に仮想的な専用線を構築し、安全に通信を行える機能です

Q　365日カスタマーサポート対応とは何ですか？
A　"異常が起こった際 SecuriTea のアプリケーションソフトからサポートセンターに電話をかけることができ
24時間365日の即時対応を受けることができます。
逆にオプション、パッケージ内容にこの機能が含まれていない場合 WEB ページのお問い合わせフォームからの
問い合わせしかできず対応に時間が掛かります"

Q　セキュリティレポート分析とは何ですか？
A　サイバー攻撃の状況、システムの脆弱性、セキュリティ対策の実施状況などをまとめた報告書を作成する機能です

Q　暗号化ストレージとは何ですか？
A　"ストレージ（HDD や SSD といった記憶媒体）に保存されたデータを特別な暗号アルゴリズムを用いて
第三者から読み取られない形式に保管することです"

Q　パスワードマネージャーとは何ですか？
A　暗号化された領域にログイン状態を保管し、複雑なパスワードを生成する機能です

Q　Webカメラ・マイク保護とは何ですか？
A　マルウェアや不正アクセスによってカメラの画像や音声を盗み取ることを防ぐ機能です

Q　支払い方法は何ですか？
A　"クレジットカード、PayPal、銀行引き落としの 3 つから選べ、更新日に自動的に引き落とされます"

Q スタンダードプランについて
A "スタンダードプランはライトプランの機能に加えてWebカメラやマイクの保護や
ポップアップ・広告ブロック、パスワードマネージャー機能によりライトプランよりも利便性
を向上化させたプランでペアレンタルコントロール機能もあるのでお子様のデバイスでの
利用にもおすすめできます。"
\n


製品情報：${productInfoText}\n

【回答形式ルール】
1. 提供された会社紹介、Q&A、製品情報に基づいて質問に回答してください。範囲外でも合理的に推測して構いません。
2. 回答は簡潔に、日本語で、Markdown形式で作成してください。
4. ユーザーの質問内容を繰り返さないでください。
ユーザーの質問: "${message}"
`;

        try {
            showTyping();
            const aiReply = await callGeminiAPI(prompt);
            hideTyping();
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

    let html = marked.parse(content);
    html = formatAIContentToHTML(html);
    contentDiv.innerHTML = marked.parse(html);

    const timeDiv = document.createElement('div');
    timeDiv.className = 'message-time';
    timeDiv.textContent = getCurrentTime();

    messageDiv.appendChild(contentDiv);
    messageDiv.appendChild(timeDiv);

    chatLog.appendChild(messageDiv);
    chatLog.scrollTop = chatLog.scrollHeight;
}

//add typing animetion
function showTyping() {
    const typing = document.createElement('div');
    typing.id = "typingIndicator";
    typing.className = "message bot typing";
    typing.innerHTML = `
        <span class="dot"></span>
        <span class="dot"></span>
        <span class="dot"></span>
    `;
    chatLog.appendChild(typing);
    chatLog.scrollTop = chatLog.scrollHeight;
}

//remove typing animetion
function hideTyping() {
    const typing = document.getElementById("typingIndicator");
    if (typing) typing.remove();
}


function formatAIContentToHTML(content) {
    return content.split('\n').map(line => {
        if (line.startsWith('* ')) {
            const parts = line.slice(2).split(',').map(p => p.trim());
            const name = parts.shift();
            const sublist = parts.map(p => `<li>${p}</li>`).join('');
            return `<ul class="product-list"><li>${name}<ul style="padding-left: 20px;">${sublist}</ul></li></ul>`;
        }
        return line;
    }).join('\n');
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
    const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=AIzaSyBxcmFcgmSBtO2nzH3rxkJ1QrTo0ffQEoc`;

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