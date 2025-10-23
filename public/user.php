<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="app-main">
  <div class="content-container">
    <h1>User Information</h1>
    
    <form class="login-form" method="POST" action="">
      <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="submit-btn">Login</button>
    </form>
    
    <hr>
    
    <h3>History</h3>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>