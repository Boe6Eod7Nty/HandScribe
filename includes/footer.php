    <!-- Mobile Footer -->
    <footer class="app-footer">
      <div class="footer-nav">
        <a href="../public/index.php" class="nav-button">
          <div class="nav-icon">
            <span class="icon-text">A</span>
          </div>
          <span class="nav-label">Translate</span>
        </a>
        <a href="../public/avatar.php" class="nav-button">
          <div class="nav-icon">
            <span class="icon-text">P</span>
          </div>
          <span class="nav-label">Avatar</span>
        </a>
        <a href="../public/education.php" class="nav-button">
          <div class="nav-icon">
            <span class="icon-text">B</span>
          </div>
          <span class="nav-label">Education</span>
        </a>
        <a href="../public/user.php" class="nav-button">
          <div class="nav-icon">
            <span class="icon-text">i</span>
          </div>
          <span class="nav-label">User Info</span>
        </a>
      </div>
    </footer>
    </div> <!-- Close main-content div -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="../assets/js/main.js?v=<?php echo time(); ?>"></script>
    
    <!-- Splash Screen Animation Script -->
    <script>
      // Add loading class immediately to prevent content flash
      document.body.classList.add('loading');
      
      // Show splash screen immediately, hide main content
      document.addEventListener('DOMContentLoaded', function() {
        const splash = document.getElementById('splash');
        const mainContent = document.getElementById('main-content');
        
        // Ensure main content is hidden initially
        if (mainContent) {
          mainContent.style.display = 'none';
        }
        
        // Wait 2.5s, then trigger slide-up fade
        setTimeout(() => {
          splash.classList.add('fade-out');
          
          // Show main content and hide splash after animation completes
          setTimeout(() => {
            splash.style.display = 'none';
            if (mainContent) {
              mainContent.style.display = 'flex';
            }
            // Remove loading class
            document.body.classList.remove('loading');
            // Main content is visible now
          }, 1300);
        }, 2500);
      });
    </script>
  </body>
</html>
