const API_KEY = "AIzaSyC8LyGCuAcKu-83Yz_3_YezePV-WWIqdyM"; // 請換成你自己的 API Key
const userInput = document.getElementById("userInput");
userInput.addEventListener("keydown", function (event) {
  if (event.key === "Enter") {
    handleSend();
  }
});
function appendMessage(sender, text) {
  const chatbox = document.getElementById("chatbox");
  const div = document.createElement("div");
  div.className = `message ${sender}`;
  div.textContent = `${sender === "user" ? `${text}:👤` : `👨‍💼:${text}`}`;
  chatbox.appendChild(div);
  chatbox.scrollTop = chatbox.scrollHeight;
}

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

async function handleSend() {
  const input = document.getElementById("userInput");
  const userMessage = input.value.trim();
  if (!userMessage) return;
  const resUserMessage = "会社紹介：   Q&A:      ユーザーの質問：" + userMessage + "。"+`提供された「会社紹介」と「Q&A（よくある質問とその回答）」の内容のみに基づいてユーザーの質問に回答してください。
それ以外の質問にはすべて「申し訳ありませんが、該当する質問が見つかりませんでした」とお答えください。以下のルールを厳守してください。

1. 提供された「会社紹介」と「Q&A」の内容のみに基づいて質問に回答してください。

2. 質問がこれらの範囲に該当しない場合は、必ず「申し訳ありませんが、該当する質問が見つかりませんでした」だけを返答し、それ以外の言葉は一切返答しないでください。

3. 回答は必ず簡潔かつ短くしてください。説明や余計な言葉を付け加えないでください。
`;
  appendMessage("user", userMessage);
  input.value = "";

  try {
    const aiReply = await callGeminiAPI(resUserMessage);
    appendMessage("ai", aiReply);
  } catch (error) {
    appendMessage("ai", `⚠️ ${error.message}`);
  }
}
const chatIcon = document.getElementById("chat-icon");
const chatBox = document.getElementById("chat-box");

chatIcon.addEventListener("click", () => {
  chatBox.style.display = (chatBox.style.display === "flex") ? "none" : "flex";
});