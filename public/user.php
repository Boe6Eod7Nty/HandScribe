<?php require_once __DIR__ . '/../includes/header.php'; ?>

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
    
    <!-- Login Form Section -->
    <div class="form-section">
      <div class="section-header">
        <h2 class="section-title">User Login</h2>
        <p class="section-description">Sign in to your HandScribe account</p>
      </div>
      
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
      </form>
    </div>
    
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
    
    <!-- Copyright Footer -->
    <div class="copyright-section">
      <p class="copyright-text">© 2025 HandScribe | Real-Time ASL Translation</p>
    </div>
    
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>