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
$usernameUpdateError = '';
$usernameUpdateSuccess = '';
$deleteAccountError = '';
$isSignupView = isset($_GET['view']) && $_GET['view'] === 'signup';

// Check for successful username update
if (isset($_GET['username_updated']) && $_GET['username_updated'] === '1') {
  $usernameUpdateSuccess = 'Username updated successfully!';
}

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

// Handle username update
if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_username') {
  $newUsername = trim((string)($_POST['new_username'] ?? ''));
  $currentUserId = (int)$_SESSION['user_id'];
  
  if ($newUsername === '') {
    $usernameUpdateError = 'Please enter a new username.';
  } else {
    // Check if username is different from current
    if ($newUsername === $_SESSION['username']) {
      $usernameUpdateError = 'New username must be different from your current username.';
    } else {
      // Check if username already exists
      $checkSql = sprintf(
        'SELECT 1 FROM `%s` WHERE `%s` = ? AND `%s` != ? LIMIT 1',
        $dbRefs['table_users'],
        $dbRefs['col_username'],
        $dbRefs['col_user_id']
      );
      if ($checkStmt = mysqli_prepare($dbc, $checkSql)) {
        mysqli_stmt_bind_param($checkStmt, 'si', $newUsername, $currentUserId);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        $exists = mysqli_stmt_num_rows($checkStmt) > 0;
        mysqli_stmt_close($checkStmt);
        if ($exists) {
          $usernameUpdateError = 'Username is already taken.';
        } else {
          // Update username
          $updateSql = sprintf(
            'UPDATE `%s` SET `%s` = ? WHERE `%s` = ?',
            $dbRefs['table_users'],
            $dbRefs['col_username'],
            $dbRefs['col_user_id']
          );
          if ($updateStmt = mysqli_prepare($dbc, $updateSql)) {
            mysqli_stmt_bind_param($updateStmt, 'si', $newUsername, $currentUserId);
            if (mysqli_stmt_execute($updateStmt)) {
              $_SESSION['username'] = $newUsername;
              mysqli_stmt_close($updateStmt);
              // Redirect to prevent form resubmission
              header('Location: ' . $_SERVER['PHP_SELF'] . '?username_updated=1');
              exit;
            } else {
              $usernameUpdateError = 'Failed to update username. Please try again.';
            }
            if (isset($updateStmt)) {
              mysqli_stmt_close($updateStmt);
            }
          } else {
            $usernameUpdateError = 'Failed to prepare update query.';
          }
        }
      } else {
        $usernameUpdateError = 'Failed to prepare check query.';
      }
    }
  }
}

// Handle delete account
if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {
  $currentUserId = (int)$_SESSION['user_id'];
  
  // Delete the user account
  $deleteSql = sprintf(
    'DELETE FROM `%s` WHERE `%s` = ?',
    $dbRefs['table_users'],
    $dbRefs['col_user_id']
  );
  
  if ($deleteStmt = mysqli_prepare($dbc, $deleteSql)) {
    mysqli_stmt_bind_param($deleteStmt, 'i', $currentUserId);
    if (mysqli_stmt_execute($deleteStmt)) {
      // Clear session and destroy it
      $_SESSION = [];
      if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
      }
      session_destroy();
      mysqli_stmt_close($deleteStmt);
      // Redirect to login page
      header('Location: ' . $_SERVER['PHP_SELF']);
      exit;
    } else {
      $deleteAccountError = 'Failed to delete account. Please try again.';
    }
    mysqli_stmt_close($deleteStmt);
  } else {
    $deleteAccountError = 'Failed to prepare delete query.';
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
      <div style="display:flex; gap:8px; margin-top:12px; width:100%;">
        <button type="button" id="update-username-btn" class="btn-secondary" style="flex:1;">Update Username</button>
        <button type="button" id="delete-account-btn" class="btn-secondary" style="flex:1;">Delete Account</button>
      </div>
      
      <!-- Username Update Modal -->
      <div id="username-update-modal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
          <div class="modal-header">
            <h3 class="modal-title">Update Username</h3>
            <button type="button" class="modal-close" id="close-username-modal">&times;</button>
          </div>
          <div class="modal-body">
            <?php if ($usernameUpdateError !== ''): ?>
            <div class="form-error" style="color:#c0392b;margin-bottom:12px;"><?php echo htmlspecialchars($usernameUpdateError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($usernameUpdateSuccess !== ''): ?>
            <div class="form-success" style="color:#27ae60;margin-bottom:12px;"><?php echo htmlspecialchars($usernameUpdateSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form id="username-update-form" class="modern-form" method="POST" action="">
              <input type="hidden" name="action" value="update_username">
              <div class="form-group">
                <label for="new_username" class="form-label">New Username:</label>
                <input type="text" id="new_username" name="new_username" class="form-input" placeholder="Enter new username" value="<?php echo htmlspecialchars((string)($_POST['new_username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
              </div>
              <div style="display:flex; gap:8px;">
                <button type="submit" class="btn-primary">Update</button>
                <button type="button" class="btn-secondary" id="cancel-username-update">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <!-- Delete Account Confirmation Modal -->
      <div id="delete-account-modal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
          <div class="modal-header" style="justify-content:center;">
            <h3 class="modal-title" style="color:#c0392b;">Delete Account</h3>
          </div>
          <div class="modal-body">
            <?php if ($deleteAccountError !== ''): ?>
            <div class="form-error" style="color:#c0392b;margin-bottom:12px;"><?php echo htmlspecialchars($deleteAccountError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <div style="text-align:center; margin-bottom:20px;">
              <p style="font-size:18px; font-weight:600; color:#c0392b; margin-bottom:12px;">⚠️ Are you sure?</p>
              <p style="font-size:16px; color:#333; margin-bottom:8px;">This action cannot be undone.</p>
              <p style="font-size:14px; color:#666;">All your account data and translation history will be permanently deleted.</p>
            </div>
            <form id="delete-account-form" class="modern-form" method="POST" action="">
              <input type="hidden" name="action" value="delete_account">
              <div style="display:flex; gap:8px;">
                <button type="submit" class="btn-primary" style="background-color:#c0392b; flex:1;">Yes, Delete My Account</button>
                <button type="button" class="btn-secondary" id="cancel-delete-account" style="flex:1;">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>
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

<script>
// Username update modal functionality
document.addEventListener('DOMContentLoaded', function() {
  const updateUsernameBtn = document.getElementById('update-username-btn');
  const modal = document.getElementById('username-update-modal');
  const closeModalBtn = document.getElementById('close-username-modal');
  const cancelBtn = document.getElementById('cancel-username-update');
  const form = document.getElementById('username-update-form');
  
  if (updateUsernameBtn && modal) {
    // Open modal
    updateUsernameBtn.addEventListener('click', function() {
      modal.style.display = 'flex';
      // Focus on input field
      const input = document.getElementById('new_username');
      if (input) {
        setTimeout(() => input.focus(), 100);
      }
    });
    
    // Close modal
    function closeModal() {
      modal.style.display = 'none';
    }
    
    if (closeModalBtn) {
      closeModalBtn.addEventListener('click', closeModal);
    }
    
    if (cancelBtn) {
      cancelBtn.addEventListener('click', closeModal);
    }
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeModal();
      }
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && modal.style.display === 'flex') {
        closeModal();
      }
    });
    
    // Auto-show modal if there's an error or success message
    <?php if ($usernameUpdateError !== '' || $usernameUpdateSuccess !== ''): ?>
    modal.style.display = 'flex';
    <?php endif; ?>
    
    // If success, close modal after 2 seconds
    <?php if ($usernameUpdateSuccess !== ''): ?>
    setTimeout(function() {
      closeModal();
      // Remove the query parameter from URL without reloading
      if (window.history.replaceState) {
        window.history.replaceState({}, document.title, window.location.pathname);
      }
    }, 2000);
    <?php endif; ?>
  }
  
  // Delete account modal functionality
  const deleteAccountBtn = document.getElementById('delete-account-btn');
  const deleteModal = document.getElementById('delete-account-modal');
  const cancelDeleteBtn = document.getElementById('cancel-delete-account');
  const deleteForm = document.getElementById('delete-account-form');
  
  if (deleteAccountBtn && deleteModal) {
    // Open modal
    deleteAccountBtn.addEventListener('click', function() {
      deleteModal.style.display = 'flex';
    });
    
    // Close modal
    function closeDeleteModal() {
      deleteModal.style.display = 'none';
    }
    
    if (cancelDeleteBtn) {
      cancelDeleteBtn.addEventListener('click', closeDeleteModal);
    }
    
    // Close modal when clicking outside
    deleteModal.addEventListener('click', function(e) {
      if (e.target === deleteModal) {
        closeDeleteModal();
      }
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && deleteModal.style.display === 'flex') {
        closeDeleteModal();
      }
    });
    
    // Auto-show modal if there's an error message
    <?php if ($deleteAccountError !== ''): ?>
    deleteModal.style.display = 'flex';
    <?php endif; ?>
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>