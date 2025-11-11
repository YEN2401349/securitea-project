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


document.addEventListener("DOMContentLoaded", () => {
  const inquiryList = document.getElementById("inquiryList");
  const mailDetailView = document.getElementById("mail-detail-view");
  const refreshBtn = document.getElementById("refreshBtn");
  const showUnhandledCheckbox = document.getElementById("showUnhandled");


  async function loadInquiries() {
    inquiryList.innerHTML = `<li class="mail-item"><div class="mail-sender">読み込み中...</div></li>`;
    try {
      const res = await fetch("./component/api/get_inquiries.php");
      const data = await res.json();

      allInquiries = data;
      renderInquiries();
    } catch (err) {
      inquiryList.innerHTML = `<li class="mail-item"><div class="mail-sender">エラーが発生しました</div></li>`;
    }
  }

  function renderInquiries() {
    const inquiryListWrapper = document.getElementById("inquiryListWrapper");
    inquiryList.innerHTML = "";

    const filtered = showUnhandledCheckbox.checked
      ? allInquiries.filter(mail => mail.status === "未対応")
      : allInquiries;
    console.log(filtered.length, inquiryListWrapper.scrollHeight);
    if (filtered.length >= 6) {
      inquiryListWrapper.style.maxHeight = "500px";
      inquiryListWrapper.style.overflowY = "auto";
    } else {
      inquiryListWrapper.style.maxHeight = "none";
      inquiryListWrapper.style.overflowY = "visible";
    }

    if (filtered.length === 0) {
      inquiryList.innerHTML = `<li class="mail-item"><div class="mail-sender">データがありません</div></li>`;
      return;
    }

    filtered.forEach(mail => {
      const li = document.createElement("li");
      li.className = "mail-item";
      li.innerHTML = `
            <div class="flex" style="justify-content: space-between; align-items:center;">
                <span class="mail-status" data-status="${mail.status}">${mail.status}</span>
                <div>
                    <div class="mail-sender">${mail.name || "不明"}</div>
                    <div class="mail-subject">${mail.subject || "(件名なし)"}</div>
                </div>
                <div class="mail-date">${mail.created_at}</div>
            </div>
        `;
      li.addEventListener("click", () => showDetail(mail.inquiry_id));
      inquiryList.appendChild(li);
    });
  }



  async function showDetail(id) {
    const res = await fetch(`./component/api/get_inquiries.php?id=${id}`);
    const mail = await res.json();
    mailDetailView.innerHTML = mail.status == "未対応" ? `
      <div class="detail-header">
        <h2>${mail.subject}</h2>
        <div class="detail-meta">
          <p>差出人：${mail.name}（${mail.email}）</p>
          <p>日付：${mail.created_at}</p>
          <p>現在のステータス：
            <span class="mail-status" data-status="${mail.status}">${mail.status}</span>
          </p>
        </div>
      </div>

      <div class="detail-body">
        <p>${mail.message.replace(/\n/g, "<br>")}</p>
      </div>

      <div class="reply-list" id="replyList"></div>

      <div class="reply-box">
        <textarea id="replyText" rows="4" placeholder="返信内容を入力..."></textarea>
        <button onclick="sendReply('${mail.inquiry_id}', '${mail.name}', '${mail.email}', '${mail.subject}')">返信を送信</button>
      </div>
    `: `
      <div class="detail-header">
        <h2>${mail.subject}</h2>
        <div class="detail-meta">
          <p>差出人：${mail.name}（${mail.email}）</p>
          <p>日付：${mail.created_at}</p>
          <p>現在のステータス：
            <span class="mail-status" data-status="${mail.status}">${mail.status}</span>
          </p>
        </div>
      </div>

      <div class="detail-body">
        <p>${mail.message.replace(/\n/g, "<br>")}</p>
      </div>

      <div class="reply-list" id="replyList"></div>
    `;

    loadReplies(mail.inquiry_id);
  }


  async function loadReplies(mailId) {
    const res = await fetch(`./component/api/get_inquiries.php?inquiry_id=${mailId}`);
    const replies = await res.json();
    const replyList = document.getElementById("replyList");
    replyList.innerHTML = replies
      .map(
        (r) => `
        <div class="reply-item">
          <p><b>${r.name}</b> (${r.created_at})</p>
          <p>${r.message.replace(/\n/g, "<br>")}</p>
        </div>
      `
      )
      .join("");
  }

  window.sendReply = async (mailId, name, email, subject) => {
    const text = document.getElementById("replyText").value.trim();
    const decoded = jwt_decode(token);
    const adminName = decoded.full_name;
    if (!text) return alert("返信内容を入力してください");

    const res = await fetch("./component/api/send_reply.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ inquiry_id: mailId, message: text, userName: name, userEmail: email, adminName: adminName, subject: subject }),
    });

    const result = await res.json();
    if (result.success) {
      alert("返信を送信しました");
      document.getElementById("replyText").value = "";
      await loadInquiries();

      await showDetail(mailId);
    } else {
      alert("送信に失敗しました");
    }
  };
  showUnhandledCheckbox.addEventListener("change", renderInquiries);
  refreshBtn.addEventListener("click", loadInquiries);
  loadInquiries();
});
