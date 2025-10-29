<link rel="stylesheet" href="./component/css/chatBot.css">
<button id="chat-toggle-button" class="chat-toggle-btn">
    <i class="fas fa-comments"></i>
</button>

<div id="chat-container" class="chat-container">
    <div class="chat-header">
        <h3>AI サポート</h3>
        <button class="close-btn">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="chat-log" id="chat-log">
        <div class="message bot">
            <div class="message-content">
                こんにちは！SecuriTeaのAIサポートです。防毒ソフトについて何かご質問がございましたら、お気軽にお聞きください。
            </div>
            <div class="message-time">今</div>
        </div>
    </div>

    <form class="chat-form" id="chat-form">
        <textarea class="chat-input" placeholder="メッセージを入力してください..." rows="1"></textarea>
        <button type="submit" class="chat-send-btn">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>
<script src="./component/js/chatBot.js"></script>