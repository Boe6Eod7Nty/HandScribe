<?php
session_start();

require_once __DIR__ . '/../includes/mysqli_connect.php';

// Centralized DB references
$dbRefs = [
  'table_users' => 'users',
  'col_user_id' => 'user_id',
  'col_username' => 'username',
  'col_password_hash' => 'password_hash',
];

$loginError = '';
$signupError = '';
$isSignupView = isset($_GET['view']) && $_GET['view'] === 'signup';

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }
  session_destroy();
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

// Handle signup
if (!isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {
  $newUsername = trim((string)($_POST['signup_username'] ?? ''));
  $newPassword = (string)($_POST['signup_password'] ?? '');
  $newPasswordConfirm = (string)($_POST['signup_password_confirm'] ?? '');

  if ($newUsername === '' || $newPassword === '' || $newPasswordConfirm === '') {
    $signupError = 'Please fill in all fields.';
    $isSignupView = true;
  } elseif ($newPassword !== $newPasswordConfirm) {
    $signupError = 'Passwords do not match.';
    $isSignupView = true;
  } else {
    // Check if username exists
    $checkSql = sprintf(
      'SELECT 1 FROM `%s` WHERE `%s` = ? LIMIT 1',
      $dbRefs['table_users'],
      $dbRefs['col_username']
    );
    if ($checkStmt = mysqli_prepare($dbc, $checkSql)) {
      mysqli_stmt_bind_param($checkStmt, 's', $newUsername);
      mysqli_stmt_execute($checkStmt);
      mysqli_stmt_store_result($checkStmt);
      $exists = mysqli_stmt_num_rows($checkStmt) > 0;
      mysqli_stmt_close($checkStmt);
      if ($exists) {
        $signupError = 'Username is already taken.';
        $isSignupView = true;
      } else {
        $passwordHash = hash('sha256', $newPassword);
        $insertSql = sprintf(
          'INSERT INTO `%s` (`%s`, `%s`) VALUES (?, ?)',
          $dbRefs['table_users'],
          $dbRefs['col_username'],
          $dbRefs['col_password_hash']
        );
        if ($insertStmt = mysqli_prepare($dbc, $insertSql)) {
          mysqli_stmt_bind_param($insertStmt, 'ss', $newUsername, $passwordHash);
          if (mysqli_stmt_execute($insertStmt)) {
            $newUserId = mysqli_insert_id($dbc);
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['username'] = $newUsername;
            mysqli_stmt_close($insertStmt);
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
          }
          mysqli_stmt_close($insertStmt);
        }
        $signupError = 'Failed to create account. Please try again.';
        $isSignupView = true;
      }
    } else {
      $signupError = 'Failed to prepare signup query.';
      $isSignupView = true;
    }
  }
}

// Handle login
if (!isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password']) && (!isset($_POST['action']) || $_POST['action'] !== 'signup')) {
  $username = trim((string)$_POST['username']);
  $password = (string)$_POST['password'];

  if ($username !== '' && $password !== '') {
    $sql = sprintf(
      'SELECT `%s`, `%s` FROM `%s` WHERE `%s` = ? LIMIT 1',
      $dbRefs['col_user_id'],
      $dbRefs['col_password_hash'],
      $dbRefs['table_users'],
      $dbRefs['col_username']
    );

    if ($stmt = mysqli_prepare($dbc, $sql)) {
      mysqli_stmt_bind_param($stmt, 's', $username);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_bind_result($stmt, $fetchedUserId, $fetchedPasswordHash);
      if (mysqli_stmt_fetch($stmt)) {
        $inputHash = hash('sha256', $password);
        if (hash_equals($fetchedPasswordHash, $inputHash)) {
          $_SESSION['user_id'] = $fetchedUserId;
          $_SESSION['username'] = $username;
          mysqli_stmt_close($stmt);
          header('Location: ' . $_SERVER['PHP_SELF']);
          exit;
        }
      }
      mysqli_stmt_close($stmt);
    }

    $loginError = 'Invalid username or password.';
  } else {
    $loginError = 'Please enter username and password.';
  }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="app-main">
  <div class="content-container">
    
    <!-- Logo Section -->
    <div class="logo-section">
      <div class="handscribe-logo">
        <div class="logo-top-row">
          <div class="logo-icon">
            <img src="../assets/images/hand_logo.svg" alt="Hand Icon" class="hand-icon">
          </div>
          <div class="logo-text">
            <div class="logo-title">
              <span class="logo-word">Hand</span>
              <span class="logo-word">Scribe</span>
            </div>
          </div>
        </div>
        <div class="logo-tagline">REAL-TIME ASL TRANSLATION</div>
      </div>
    </div>
    
    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Logged-in Section -->
    <div class="form-section">
      <div class="section-header">
        <h2 class="section-title">Welcome</h2>
        <p class="section-description">Signed in as <strong><?php echo htmlspecialchars((string)($_SESSION['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong></p>
      </div>
      <form class="modern-form" method="POST" action="">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="btn-secondary">Logout</button>
      </form>
    </div>
    <?php else: ?>
    <!-- Auth Section: Login or Signup -->
    <?php if ($isSignupView): ?>
    <div class="form-section">
      <div class="section-header">
        <h2 class="section-title">Create Account</h2>
        <p class="section-description">Sign up for a new HandScribe account</p>
      </div>
      <?php if ($signupError !== ''): ?>
      <div class="form-error" style="color:#c0392b;margin-bottom:12px;"><?php echo htmlspecialchars($signupError, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <form class="modern-form" method="POST" action="?view=signup">
        <input type="hidden" name="action" value="signup">
        <div class="form-group">
          <label for="signup_username" class="form-label">Username:</label>
          <input type="text" id="signup_username" name="signup_username" class="form-input" placeholder="Choose a username" required>
        </div>
        <div class="form-group">
          <label for="signup_password" class="form-label">Password:</label>
          <input type="password" id="signup_password" name="signup_password" class="form-input" placeholder="Create a password" required>
        </div>
        <div class="form-group">
          <label for="signup_password_confirm" class="form-label">Confirm Password:</label>
          <input type="password" id="signup_password_confirm" name="signup_password_confirm" class="form-input" placeholder="Re-enter your password" required>
        </div>
        <div style="display:flex; gap:8px;">
          <button type="submit" class="btn-primary">Create account</button>
          <a href="<?php echo htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?'), ENT_QUOTES, 'UTF-8'); ?>" class="btn-secondary" style="display:inline-block; text-decoration:none; padding:10px 22px; white-space:nowrap;">Back to login</a>
        </div>
      </form>
    </div>
    <?php else: ?>
    <div class="form-section">
      <div class="section-header">
        <h2 class="section-title">User Login</h2>
        <p class="section-description">Sign in to your HandScribe account</p>
      </div>
      <?php if ($loginError !== ''): ?>
      <div class="form-error" style="color:#c0392b;margin-bottom:12px;"><?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <form class="modern-form" method="POST" action="">
        <div class="form-group">
          <label for="username" class="form-label">Username:</label>
          <input type="text" id="username" name="username" class="form-input" placeholder="Enter username" required>
        </div>
        <div class="form-group">
          <label for="password" class="form-label">Password:</label>
          <input type="password" id="password" name="password" class="form-input" placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn-primary">Login</button>
        <div style="text-align:center; margin:16px 0; color:#666; font-size:14px;">or sign up here</div>
        <div style="text-align:center;">
          <a href="?view=signup" class="btn-secondary" style="display:inline-block; text-decoration:none; padding:10px 22px; white-space:nowrap;">Sign up</a>
        </div>
      </form>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- History Section -->
    <div class="history-section">
      <div class="section-header">
        <h2 class="section-title">TRANSLATION HISTORY</h2>
        <p class="section-description">Your recent translation sessions</p>
      </div>
      
      <div class="history-container">
        <div class="history-item">
          <div class="history-content">
            <div class="history-text">"IT'S NICE TO MEET YOU"</div>
            <div class="history-meta">
              <span class="history-time">2 minutes ago</span>
              <span class="history-duration">Session: 3m 45s</span>
            </div>
          </div>
          <div class="history-actions">
            <button class="btn-secondary">VIEW</button>
          </div>
        </div>
        
        <div class="history-item">
          <div class="history-content">
            <div class="history-text">"HOW ARE YOU TODAY?"</div>
            <div class="history-meta">
              <span class="history-time">15 minutes ago</span>
              <span class="history-duration">Session: 2m 12s</span>
            </div>
          </div>
          <div class="history-actions">
            <button class="btn-secondary">VIEW</button>
          </div>
        </div>
        
        <div class="history-item">
          <div class="history-content">
            <div class="history-text">"THANK YOU VERY MUCH"</div>
            <div class="history-meta">
              <span class="history-time">1 hour ago</span>
              <span class="history-duration">Session: 1m 30s</span>
            </div>
          </div>
          <div class="history-actions">
            <button class="btn-secondary">VIEW</button>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <!-- Copyright Footer -->
    <div class="copyright-section">
      <p class="copyright-text">© 2025 HandScribe | Real-Time ASL Translation</p>
    </div>
    
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>