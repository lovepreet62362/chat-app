<?php
include "db.php";

$error = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username=?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: chat.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — Nexus Chat</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #080c10;
    --surface: #0e1318;
    --border: #1e2830;
    --accent: #00e5ff;
    --accent2: #7c3aed;
    --text: #e8edf2;
    --muted: #64748b;
    --error: #f87171;
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }

  /* Animated background */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
      radial-gradient(ellipse 60% 50% at 20% 20%, rgba(0,229,255,0.07) 0%, transparent 60%),
      radial-gradient(ellipse 50% 60% at 80% 80%, rgba(124,58,237,0.08) 0%, transparent 60%);
    pointer-events: none;
    z-index: 0;
  }

  /* Grid lines */
  body::after {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(0,229,255,0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(0,229,255,0.03) 1px, transparent 1px);
    background-size: 60px 60px;
    pointer-events: none;
    z-index: 0;
  }

  .container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 420px;
    padding: 20px;
    animation: fadeUp 0.6s ease both;
  }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .logo {
    text-align: center;
    margin-bottom: 36px;
  }

  .logo-mark {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 22px;
    color: #fff;
    margin-bottom: 16px;
    box-shadow: 0 0 30px rgba(0,229,255,0.25);
  }

  .logo h1 {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 28px;
    letter-spacing: -0.5px;
    background: linear-gradient(135deg, var(--text) 40%, var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .logo p {
    color: var(--muted);
    font-size: 14px;
    margin-top: 6px;
    font-weight: 300;
  }

  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 36px;
    backdrop-filter: blur(20px);
    box-shadow:
      0 0 0 1px rgba(255,255,255,0.03) inset,
      0 32px 64px rgba(0,0,0,0.4);
  }

  .error-msg {
    background: rgba(248,113,113,0.1);
    border: 1px solid rgba(248,113,113,0.3);
    color: var(--error);
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--muted);
    margin-bottom: 8px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  input {
    width: 100%;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 14px 16px;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  }

  input::placeholder { color: var(--muted); opacity: 0.5; }

  input:focus {
    border-color: var(--accent);
    background: rgba(0,229,255,0.04);
    box-shadow: 0 0 0 3px rgba(0,229,255,0.1);
  }

  .btn {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, var(--accent), #00b8cc);
    border: none;
    border-radius: 12px;
    color: #000;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 15px;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
    margin-top: 8px;
    box-shadow: 0 4px 20px rgba(0,229,255,0.3);
  }

  .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 28px rgba(0,229,255,0.4);
  }

  .btn:active { transform: translateY(0); opacity: 0.9; }

  .footer-link {
    text-align: center;
    margin-top: 24px;
    font-size: 14px;
    color: var(--muted);
  }

  .footer-link a {
    color: var(--accent);
    text-decoration: none;
    font-weight: 500;
    transition: opacity 0.2s;
  }

  .footer-link a:hover { opacity: 0.7; }
</style>
</head>
<body>

<div class="container">
  <div class="logo">
    <div class="logo-mark">N</div>
    <h1>Nexus Chat</h1>
    <p>Welcome back — sign in to continue</p>
  </div>

  <div class="card">
    <?php if($error): ?>
    <div class="error-msg">
      <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter your username" required autocomplete="username">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn">Sign In →</button>
    </form>
  </div>

  <div class="footer-link">
    Don't have an account? <a href="register.php">Create one</a>
  </div>
</div>

</body>
</html>
