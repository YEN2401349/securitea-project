<php>
    <button id="chat-icon" class="chat-toggle-btn">
        <i class="fas fa-comments"></i>
    </button>
    <div id="chat-box">
        <div id="chat-header">👨‍💼AIチャットサポート</div>
        <div id="chatbox"></div>
        <div class="chat-input-div">
            <input type="text" id="userInput" class="chat-input" placeholder="入力してください"/>
            <button class="chat-send" onclick="handleSend()">➤</button>
        </div>
    </div>
    <script src="chatBot.js"></script>
</php>