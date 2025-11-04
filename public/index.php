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
          <img src="../assets/images/camera-icon.jpg" alt="Camera Icon" class="camera-icon">
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
    
    <!-- Translated Text Display -->
    <div class="translation-display">
      <div class="translated-text">IT'S NICE TO MEET YOU</div>
    </div>
    
    <!-- Options Section -->
    <div class="options-section">
      <div class="mode-controls" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div>
          <label for="mode-select" class="form-label" style="margin-bottom: 4px;">Mode</label>
          <select id="mode-select" class="language-dropdown">
            <option value="asl-to-text">ASL -> Text</option>
            <option value="text-to-asl">Text -> ASL</option>
          </select>
        </div>
        <div>
          <label for="pose-facing-mode" class="form-label" style="margin-bottom: 4px;">Camera</label>
          <select id="pose-facing-mode" class="language-dropdown">
            <option value="environment" selected>Back camera</option>
            <option value="user">Front camera</option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons">
      <button class="btn-stop">STOP</button>
    </div>
    
  </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
