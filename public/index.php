<?php
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Main Content Area -->
<main class="app-main">
  <div class="content-container">
    
    <!-- Video/Sign Language Display -->
    <div class="video-display">
      <div class="video-placeholder">
        <video id="camera-feed" class="camera-video" autoplay muted playsinline></video>
        <canvas id="pose-canvas" class="pose-canvas"></canvas>
        <div id="camera-placeholder" class="camera-placeholder">
          <img src="../assets/images/avatar_placeholder.png" alt="Avatar" class="avatar-image">
          <div class="camera-permission-prompt">
            <p>Camera access required for live translation</p>
            <button id="enable-camera-btn" class="btn-enable-camera">Enable Camera</button>
          </div>
        </div>
        <div id="camera-error" class="camera-error" style="display: none;">
          <p>Camera not available</p>
          <p>Please check your camera permissions</p>
        </div>
      </div>
    </div>
    
    <!-- Progress Bar -->
    <div class="progress-container">
      <div class="progress-bar">
        <div class="progress-fill"></div>
        <div class="progress-indicator"></div>
      </div>
    </div>
    
    <!-- Translated Text Display -->
    <div class="translation-display">
      <div class="translated-text">IT'S NICE TO MEET YOU</div>
    </div>
    
    <!-- Options Section -->
    <div class="options-section">
      <div class="options-label">OPTIONS</div>
      <div class="dropdown-container">
        <select class="language-dropdown">
          <option value="english">English</option>
        </select>
      </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons">
      <button class="btn-stop">STOP</button>
      <button class="btn-pause">PAUSE</button>
    </div>
    
  </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
