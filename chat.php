<?php include "auth.php"; ?>
<?php
// Fetch all users for sidebar (except self)
$me = $_SESSION['user_id'];
$myName = $_SESSION['username'] ?? 'User';

$users_result = mysqli_query($conn, "SELECT id, username FROM users WHERE id != $me ORDER BY username ASC");
$users = [];
while($row = mysqli_fetch_assoc($users_result)){
    $users[] = $row;
}

// Active chat partner from URL
$active_id   = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$active_name = "";
foreach($users as $u){
    if($u['id'] == $active_id) $active_name = $u['username'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nexus Chat</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #060a0d;
    --sidebar: #0b1016;
    --surface: #0e1318;
    --bubble-in: #141c24;
    --bubble-out-start: #00e5ff;
    --bubble-out-end: #0099b3;
    --border: #1a2330;
    --accent: #00e5ff;
    --accent2: #7c3aed;
    --text: #e2e8f0;
    --muted: #4a6070;
    --muted2: #2a3a4a;
    --online: #22c55e;
  }

  html, body { height: 100%; overflow: hidden; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    display: flex;
    flex-direction: column;
  }

  /* ─── Layout ─── */
  .app {
    display: grid;
    grid-template-columns: 280px 1fr;
    height: 100vh;
    overflow: hidden;
  }

  /* ─── Sidebar ─── */
  .sidebar {
    background: var(--sidebar);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .sidebar-header {
    padding: 22px 20px 16px;
    border-bottom: 1px solid var(--border);
  }

  .brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
  }

  .brand-mark {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 16px;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 0 16px rgba(0,229,255,0.2);
  }

  .brand-name {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 18px;
    letter-spacing: -0.3px;
    background: linear-gradient(135deg, var(--text), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .me-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 14px;
    color: #fff;
    flex-shrink: 0;
  }

  .avatar-me { background: linear-gradient(135deg, var(--accent2), #9d5cf6); }
  .avatar-user { background: linear-gradient(135deg, #1e4060, #0e6090); }
  .avatar-active { background: linear-gradient(135deg, var(--accent), #0099b3); color: #000; }

  .me-info .name {
    font-size: 14px;
    font-weight: 500;
    color: var(--text);
  }

  .me-info .status {
    font-size: 12px;
    color: var(--online);
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .me-info .status::before {
    content: '';
    width: 6px;
    height: 6px;
    background: var(--online);
    border-radius: 50%;
    display: inline-block;
  }

  .sidebar-section-title {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--muted);
    padding: 16px 20px 8px;
  }

  .users-list {
    flex: 1;
    overflow-y: auto;
    padding: 4px 10px 10px;
    scrollbar-width: thin;
    scrollbar-color: var(--muted2) transparent;
  }

  .user-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    cursor: pointer;
    text-decoration: none;
    color: var(--text);
    transition: background 0.15s;
    margin-bottom: 2px;
  }

  .user-item:hover { background: rgba(255,255,255,0.04); }
  .user-item.active { background: rgba(0,229,255,0.08); }

  .user-item .info .uname {
    font-size: 14px;
    font-weight: 500;
  }

  .user-item .info .sub {
    font-size: 12px;
    color: var(--muted);
    margin-top: 2px;
  }

  .user-item.active .info .uname { color: var(--accent); }

  .sidebar-footer {
    border-top: 1px solid var(--border);
    padding: 14px 16px;
  }

  .logout-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: none;
    border: 1px solid var(--border);
    color: var(--muted);
    padding: 10px 14px;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    cursor: pointer;
    width: 100%;
    text-align: left;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
  }

  .logout-btn:hover {
    background: rgba(248,113,113,0.08);
    border-color: rgba(248,113,113,0.3);
    color: #f87171;
  }

  /* ─── Main Chat Area ─── */
  .main {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
  }

  /* ambient bg */
  .main::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
      radial-gradient(ellipse 40% 40% at 70% 10%, rgba(0,229,255,0.04) 0%, transparent 60%),
      radial-gradient(ellipse 30% 30% at 20% 80%, rgba(124,58,237,0.04) 0%, transparent 60%);
    pointer-events: none;
    z-index: 0;
  }

  /* ─── Chat Header ─── */
  .chat-header {
    position: relative;
    z-index: 2;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 16px 24px;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-shrink: 0;
  }

  .chat-header .info .name {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 17px;
  }

  .chat-header .info .sub {
    font-size: 12px;
    color: var(--online);
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
  }

  .chat-header .info .sub::before {
    content: '';
    width: 6px; height: 6px;
    background: var(--online);
    border-radius: 50%;
  }

  .header-placeholder {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    font-size: 15px;
  }

  /* ─── Messages ─── */
  .messages {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
    z-index: 1;
    scrollbar-width: thin;
    scrollbar-color: var(--muted2) transparent;
  }

  .msg-row {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    animation: msgIn 0.25s ease both;
  }

  @keyframes msgIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .msg-row.out { flex-direction: row-reverse; }

  .msg-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 11px;
    flex-shrink: 0;
  }

  .msg-avatar.in  { background: linear-gradient(135deg, #1e4060, #0e6090); color: #fff; }
  .msg-avatar.out { background: linear-gradient(135deg, var(--accent2), #9d5cf6); color: #fff; }

  .bubble {
    max-width: 480px;
    padding: 11px 16px;
    border-radius: 18px;
    font-size: 15px;
    line-height: 1.5;
    word-break: break-word;
    position: relative;
  }

  .bubble.in {
    background: var(--bubble-in);
    border: 1px solid var(--border);
    border-bottom-left-radius: 5px;
    color: var(--text);
  }

  .bubble.out {
    background: linear-gradient(135deg, var(--bubble-out-start), var(--bubble-out-end));
    color: #000;
    border-bottom-right-radius: 5px;
    font-weight: 500;
  }

  .bubble .time {
    font-size: 11px;
    margin-top: 5px;
    opacity: 0.5;
    display: block;
    text-align: right;
  }

  .bubble.in .time { text-align: left; }

  /* Empty state */
  .empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    text-align: center;
    gap: 12px;
  }

  .empty-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(0,229,255,0.06);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
  }

  .empty-state h3 {
    font-family: 'Syne', sans-serif;
    font-size: 18px;
    color: var(--text);
    font-weight: 700;
  }

  .empty-state p { font-size: 14px; max-width: 260px; line-height: 1.5; }

  /* ─── Input Bar ─── */
  .input-bar {
    position: relative;
    z-index: 2;
    background: var(--surface);
    border-top: 1px solid var(--border);
    padding: 16px 24px;
    display: flex;
    gap: 12px;
    align-items: flex-end;
  }

  .input-wrap {
    flex: 1;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 16px;
    display: flex;
    align-items: center;
    padding: 4px 6px 4px 16px;
    transition: border-color 0.2s, box-shadow 0.2s;
  }

  .input-wrap:focus-within {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(0,229,255,0.1);
  }

  .input-wrap textarea {
    flex: 1;
    background: none;
    border: none;
    outline: none;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    line-height: 1.5;
    resize: none;
    max-height: 120px;
    padding: 8px 0;
  }

  .input-wrap textarea::placeholder { color: var(--muted); opacity: 0.6; }

  .send-btn {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--accent), #0099b3);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
    box-shadow: 0 4px 14px rgba(0,229,255,0.3);
  }

  .send-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0,229,255,0.4);
  }

  .send-btn:active { transform: scale(0.96); }

  .send-btn:disabled { opacity: 0.4; cursor: default; transform: none; }

  .send-btn svg { color: #000; }

  /* No conversation selected */
  .no-chat {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    color: var(--muted);
    position: relative;
    z-index: 1;
  }

  .no-chat-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(0,229,255,0.05);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
  }

  .no-chat h2 {
    font-family: 'Syne', sans-serif;
    font-size: 22px;
    color: var(--text);
    font-weight: 700;
  }

  .no-chat p { font-size: 14px; max-width: 260px; text-align: center; line-height: 1.6; }

  /* Scrollbar */
  ::-webkit-scrollbar { width: 4px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: var(--muted2); border-radius: 2px; }

  /* Date divider */
  .date-divider {
    text-align: center;
    font-size: 12px;
    color: var(--muted);
    padding: 8px 0;
    position: relative;
    flex-shrink: 0;
  }

  .date-divider span {
    background: var(--bg);
    padding: 0 12px;
    position: relative;
    z-index: 1;
  }

  .date-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0; right: 0;
    height: 1px;
    background: var(--border);
  }
</style>
</head>
<body>

<div class="app">
  <!-- ─── Sidebar ─── -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="brand">
        <div class="brand-mark">N</div>
        <span class="brand-name">Nexus Chat</span>
      </div>
      <div class="me-card">
        <div class="avatar avatar-me"><?= strtoupper(substr($myName, 0, 1)) ?></div>
        <div class="me-info">
          <div class="name"><?= htmlspecialchars($myName) ?></div>
          <div class="status">Online</div>
        </div>
      </div>
    </div>

    <div class="sidebar-section-title">Conversations</div>

    <div class="users-list">
      <?php if(empty($users)): ?>
        <div style="padding:20px;color:var(--muted);font-size:13px;text-align:center;">No other users yet</div>
      <?php else: ?>
        <?php foreach($users as $u): ?>
          <a href="chat.php?user=<?= $u['id'] ?>" class="user-item <?= ($active_id == $u['id']) ? 'active' : '' ?>">
            <div class="avatar <?= ($active_id == $u['id']) ? 'avatar-active' : 'avatar-user' ?>">
              <?= strtoupper(substr($u['username'], 0, 1)) ?>
            </div>
            <div class="info">
              <div class="uname"><?= htmlspecialchars($u['username']) ?></div>
              <div class="sub">Click to chat</div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="sidebar-footer">
      <a href="logout.php">
        <button class="logout-btn">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          Sign Out
        </button>
      </a>
    </div>
  </aside>

  <!-- ─── Main ─── -->
  <main class="main">
    <?php if($active_id && $active_name): ?>
      <!-- Chat Header -->
      <div class="chat-header">
        <div class="avatar avatar-active"><?= strtoupper(substr($active_name, 0, 1)) ?></div>
        <div class="info">
          <div class="name"><?= htmlspecialchars($active_name) ?></div>
          <div class="sub">Online</div>
        </div>
      </div>

      <!-- Messages -->
      <div class="messages" id="chat">
        <!-- loaded via JS -->
      </div>

      <!-- Input Bar -->
      <div class="input-bar">
        <div class="input-wrap">
          <textarea id="msg" placeholder="Type a message…" rows="1"></textarea>
        </div>
        <button class="send-btn" id="sendBtn" onclick="sendMsg()">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
          </svg>
        </button>
      </div>

    <?php else: ?>
      <!-- No chat selected -->
      <div class="no-chat">
        <div class="no-chat-icon">💬</div>
        <h2>Your Messages</h2>
        <p>Select a conversation from the sidebar to start chatting.</p>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php if($active_id && $active_name): ?>
<script>
  const ME = <?= $me ?>;
  const OTHER_ID = <?= $active_id ?>;
  const OTHER_NAME = <?= json_encode($active_name) ?>;
  const MY_NAME = <?= json_encode($myName) ?>;

  const chatEl = document.getElementById('chat');
  const msgInput = document.getElementById('msg');
  let lastCount = 0;

  // Auto-resize textarea
  msgInput.addEventListener('input', () => {
    msgInput.style.height = 'auto';
    msgInput.style.height = Math.min(msgInput.scrollHeight, 120) + 'px';
  });

  // Send on Enter (Shift+Enter for newline)
  msgInput.addEventListener('keydown', e => {
    if(e.key === 'Enter' && !e.shiftKey){
      e.preventDefault();
      sendMsg();
    }
  });

  function formatTime(dateStr){
    if(!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
  }

  function sendMsg(){
    const text = msgInput.value.trim();
    if(!text) return;

    msgInput.value = '';
    msgInput.style.height = 'auto';

    fetch("send.php", {
      method: "POST",
      headers: {"Content-Type": "application/x-www-form-urlencoded"},
      body: "receiver_id=" + OTHER_ID + "&message=" + encodeURIComponent(text)
    }).then(() => loadMessages());
  }

  function loadMessages(){
    fetch("fetch.php?user2=" + OTHER_ID)
      .then(res => res.json())
      .then(data => {
        if(data.length === lastCount) return;
        lastCount = data.length;

        if(data.length === 0){
          chatEl.innerHTML = `
            <div class="empty-state">
              <div class="empty-icon">👋</div>
              <h3>Say hello!</h3>
              <p>This is the beginning of your conversation with ${OTHER_NAME}.</p>
            </div>`;
          return;
        }

        chatEl.innerHTML = data.map(m => {
          const isOut = parseInt(m.sender_id) === ME;
          const initial = isOut ? MY_NAME.charAt(0).toUpperCase() : OTHER_NAME.charAt(0).toUpperCase();
          const time = formatTime(m.created_at || null);
          return `
            <div class="msg-row ${isOut ? 'out' : 'in'}">
              <div class="msg-avatar ${isOut ? 'out' : 'in'}">${initial}</div>
              <div class="bubble ${isOut ? 'out' : 'in'}">
                ${escapeHtml(m.message)}
                ${time ? `<span class="time">${time}</span>` : ''}
              </div>
            </div>`;
        }).join('');

        // Scroll to bottom
        chatEl.scrollTop = chatEl.scrollHeight;
      })
      .catch(() => {});
  }

  function escapeHtml(str){
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  // Load immediately then poll
  loadMessages();
  setInterval(loadMessages, 1500);
</script>
<?php endif; ?>

</body>
</html>
