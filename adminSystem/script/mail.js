if (localStorage.getItem('token') == null) {
    window.location.href = 'login.php'
}
window.onload = function () {
    window.history.forward();
};
window.onpageshow = function (event) {
    if (event.persisted) {
        window.location.reload();
    }
};
document.addEventListener('DOMContentLoaded', function () {
    const mailItems = document.querySelectorAll('.mail-item');
    const mailDetailView = document.getElementById('mail-detail-view');
    const initialContent = mailDetailView.innerHTML;

    mailItems.forEach(item => {
        item.addEventListener('click', function () {
            // 他の選択状態を解除
            mailItems.forEach(i => i.classList.remove('active'));

            // クリックされたアイテムを選択状態にする
            this.classList.add('active');

            // 詳細ビューにコンテンツを表示 (ダミーデータ)
            const sender = this.querySelector('.mail-sender').textContent;
            const subject = this.querySelector('.mail-subject').textContent;
            const date = this.querySelector('.mail-date').textContent;

            mailDetailView.innerHTML = `
        <div class="detail-header">
          <h2>${subject}</h2>
          <div class="detail-meta">
            <p><strong>差出人:</strong> ${sender}</p>
            <p><strong>受信日時:</strong> ${date}</p>
          </div>
        </div>
        <div class="detail-body">
          <p>これはメール本文のサンプルです。</p>
          <p>${sender}さんからの「${subject}」に関するメールの詳細内容がここに表示されます。</p>
          <br>
          <p>よろしくお願いいたします。</p>
        </div>
      `;
        });
    });
});