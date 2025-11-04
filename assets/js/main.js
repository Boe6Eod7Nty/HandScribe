document.addEventListener('DOMContentLoaded', function () {
  console.log('HandScribe starter loaded.');
  
  // Camera functionality
  const cameraFeed = document.getElementById('camera-feed');
  const cameraPlaceholder = document.getElementById('camera-placeholder');
  const cameraError = document.getElementById('camera-error');
  const enableCameraBtn = document.getElementById('enable-camera-btn');
  const poseCanvas = document.getElementById('pose-canvas');
  const poseCtx = poseCanvas ? poseCanvas.getContext('2d') : null;
  // Controls
  const facingModeSelect = document.getElementById('pose-facing-mode');
  const stopBtn = document.querySelector('.btn-stop');
  let animationFrameId = null;
  let holistic = null;
  let isSending = false;
  
  let stream = null;
  const containerEl = cameraFeed ? cameraFeed.parentElement : null; // .video-placeholder
  // minimal state
  const drawState = {
    connectorLineWidth: 4,
    landmarkRadius: 4,
    connectorColor: '#00d1ff',
    landmarkColor: '#ff006e'
  };

  // Removed test overlay helpers

  async function processFrame() {
    if (!cameraFeed || !holistic) {
      animationFrameId = window.requestAnimationFrame(processFrame);
      return;
    }
    if (!isSending) {
      isSending = true;
      try {
        await holistic.send({ image: cameraFeed });
      } catch (e) {
        console.error('Holistic send error:', e);
      } finally {
        isSending = false;
      }
    }
    animationFrameId = window.requestAnimationFrame(processFrame);
  }

  function onResults(results) {
    if (!poseCtx || !poseCanvas) return;
    // Clear canvas
    poseCtx.clearRect(0, 0, poseCanvas.width, poseCanvas.height);

    // Pose skeleton (filter out hand/thumb connections and landmarks; keep forearm/wrist)
    if (
      results && results.poseLandmarks && results.poseLandmarks.length &&
      typeof drawConnectors !== 'undefined' && typeof drawLandmarks !== 'undefined' && typeof POSE_CONNECTIONS !== 'undefined'
    ) {
      const connectorSpec = { color: drawState.connectorColor, lineWidth: drawState.connectorLineWidth };
      const landmarkSpec = { color: drawState.landmarkColor, lineWidth: 0, radius: drawState.landmarkRadius };

      const handIdx = new Set([17, 18, 19, 20, 21, 22]);
      const filteredConnections = POSE_CONNECTIONS.filter(([a, b]) => !handIdx.has(a) && !handIdx.has(b));
      const nonHandLandmarks = results.poseLandmarks.filter((_, idx) => !handIdx.has(idx));

      drawConnectors(poseCtx, results.poseLandmarks, filteredConnections, connectorSpec);
      drawLandmarks(poseCtx, nonHandLandmarks, landmarkSpec);
    }

    // Left hand with per-finger connections
    if (
      results && results.leftHandLandmarks &&
      typeof HAND_CONNECTIONS !== 'undefined' && typeof drawConnectors !== 'undefined' && typeof drawLandmarks !== 'undefined'
    ) {
      drawConnectors(poseCtx, results.leftHandLandmarks, HAND_CONNECTIONS, {
        color: '#34d399',
        lineWidth: drawState.connectorLineWidth
      });
      drawLandmarks(poseCtx, results.leftHandLandmarks, {
        color: '#34d399',
        lineWidth: 0,
        radius: drawState.landmarkRadius
      });
    }

    // Right hand with per-finger connections
    if (
      results && results.rightHandLandmarks &&
      typeof HAND_CONNECTIONS !== 'undefined' && typeof drawConnectors !== 'undefined' && typeof drawLandmarks !== 'undefined'
    ) {
      drawConnectors(poseCtx, results.rightHandLandmarks, HAND_CONNECTIONS, {
        color: '#f59e0b',
        lineWidth: drawState.connectorLineWidth
      });
      drawLandmarks(poseCtx, results.rightHandLandmarks, {
        color: '#f59e0b',
        lineWidth: 0,
        radius: drawState.landmarkRadius
      });
    }
  }

  function initHolistic() {
    if (holistic || typeof Holistic === 'undefined') return;
    holistic = new Holistic({
      locateFile: f => "https://cdn.jsdelivr.net/npm/@mediapipe/holistic/" + f
    });
    // Default to light model (complexity 0)
    holistic.setOptions({
      modelComplexity: 0,
      smoothLandmarks: true,
      minDetectionConfidence: 0.5,
      minTrackingConfidence: 0.5,
      refineFaceLandmarks: false
    });
    holistic.onResults(onResults);
  }

  // No debug overlay exports
  
  function updateMirrorClass() {
    if (!cameraFeed || !poseCanvas) return;
    const isFront = (facingModeSelect ? facingModeSelect.value === 'user' : false);
    cameraFeed.classList.toggle('mirror', isFront);
    poseCanvas.classList.toggle('mirror', isFront);
  }

  function syncCanvasToCover() {
    if (!cameraFeed || !poseCanvas || !containerEl) return;
    const videoW = cameraFeed.videoWidth || 0;
    const videoH = cameraFeed.videoHeight || 0;
    if (!videoW || !videoH) return;
    const rect = containerEl.getBoundingClientRect();
    const containerW = rect.width;
    const containerH = rect.height;
    const scale = Math.max(containerW / videoW, containerH / videoH); // cover
    const drawW = videoW * scale;
    const drawH = videoH * scale;
    const offsetX = (containerW - drawW) / 2;
    const offsetY = (containerH - drawH) / 2;

    if (poseCanvas.width !== videoW || poseCanvas.height !== videoH) {
      poseCanvas.width = videoW;
      poseCanvas.height = videoH;
    }
    poseCanvas.style.width = drawW + 'px';
    poseCanvas.style.height = drawH + 'px';
    poseCanvas.style.left = offsetX + 'px';
    poseCanvas.style.top = offsetY + 'px';
  }

  // Function to start camera
  async function startCamera() {
    try {
      // Request camera access
      const facingMode = facingModeSelect ? facingModeSelect.value : 'environment';
      stream = await navigator.mediaDevices.getUserMedia({
        video: {
          width: { ideal: 1280 },
          height: { ideal: 720 },
          facingMode: facingMode // Back or front camera
        },
        audio: false
      });
      
      // Set the video source
      cameraFeed.srcObject = stream;
      
      // Hide placeholder and show camera feed
      cameraPlaceholder.style.display = 'none';
      cameraError.style.display = 'none';
      cameraFeed.style.display = 'block';
      if (poseCanvas) {
        poseCanvas.style.display = 'block';
      }
      
      console.log('Camera started successfully');
      
      // Ensure canvas matches video dimensions once metadata is available
      cameraFeed.addEventListener('loadedmetadata', () => {
        // Size canvas to intrinsic video dimensions
        if (poseCanvas) {
          poseCanvas.width = cameraFeed.videoWidth || poseCanvas.clientWidth || 0;
          poseCanvas.height = cameraFeed.videoHeight || poseCanvas.clientHeight || 0;
        }
        updateMirrorClass();
        syncCanvasToCover();
        // Initialize MediaPipe Holistic and start processing loop
        initHolistic();
        if (animationFrameId !== null) {
          cancelAnimationFrame(animationFrameId);
        }
        animationFrameId = window.requestAnimationFrame(processFrame);
      }, { once: true });
      
    } catch (error) {
      console.error('Error accessing camera:', error);
      handleCameraError(error);
    }
  }
  
  // Function to stop camera
  function stopCamera() {
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      stream = null;
    }
    if (animationFrameId !== null) {
      cancelAnimationFrame(animationFrameId);
      animationFrameId = null;
    }
    isSending = false;
    if (holistic) {
      try { holistic.close && holistic.close(); } catch (e) { /* ignore */ }
      holistic = null;
    }
    
    // Show placeholder again
    cameraPlaceholder.style.display = 'flex';
    cameraError.style.display = 'none';
    cameraFeed.style.display = 'none';
    if (poseCanvas) {
      poseCanvas.style.display = 'none';
      if (poseCtx) {
        poseCtx.clearRect(0, 0, poseCanvas.width, poseCanvas.height);
      }
    }
    
    console.log('Camera stopped');
  }
  
  // Function to handle camera errors
  function handleCameraError(error) {
    console.error('Camera error:', error);
    
    // Hide placeholder and show error
    cameraPlaceholder.style.display = 'none';
    cameraError.style.display = 'flex';
    cameraFeed.style.display = 'none';
    
    // Update error message based on error type
    const errorMessages = cameraError.querySelectorAll('p');
    if (errorMessages.length >= 2) {
      if (error.name === 'NotAllowedError') {
        errorMessages[0].textContent = 'Camera access denied';
        errorMessages[1].textContent = 'Please allow camera access in your browser settings';
      } else if (error.name === 'NotFoundError') {
        errorMessages[0].textContent = 'No camera found';
        errorMessages[1].textContent = 'Please connect a camera to your device';
      } else {
        errorMessages[0].textContent = 'Camera error';
        errorMessages[1].textContent = 'Please check your camera and try again';
      }
    }
  }
  
  // Event listeners
  enableCameraBtn.addEventListener('click', startCamera);
  if (stopBtn) stopBtn.addEventListener('click', stopCamera);
  if (facingModeSelect) {
    facingModeSelect.addEventListener('change', function () {
      // Restart camera with new facing mode
      stopCamera();
      startCamera();
    });
  }
  // Keep canvas aligned on resize/orientation changes
  window.addEventListener('resize', syncCanvasToCover);
  window.addEventListener('orientationchange', syncCanvasToCover);
  
  // Check if camera is available on page load
  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    console.log('Camera API is available');
  } else {
    console.log('Camera API not available');
    handleCameraError({ name: 'NotSupportedError' });
  }
  
  // Clean up camera stream when page is unloaded
  window.addEventListener('beforeunload', stopCamera);

  // Demo text functionality
  const triggerDemoTextBtn = document.getElementById('trigger-demo-text-btn');
  const translatedTextEl = document.getElementById('translated-text');
  
  if (triggerDemoTextBtn && translatedTextEl) {
    triggerDemoTextBtn.addEventListener('click', function() {
      // Wait 3 seconds
      setTimeout(function() {
        // Remove placeholder class and ensure black color for demo text
        translatedTextEl.classList.remove('placeholder');
        translatedTextEl.style.color = '';
        
        const targetText = "Hello, my name is Lee. Nice to meet you!";
        const words = targetText.split(' ');
        let currentWordIndex = 0;
        
        // Function to display words progressively
        function displayNextWord() {
          if (currentWordIndex < words.length) {
            // Display first word, then first 2 words, then first 3 words, etc.
            const displayedWords = words.slice(0, currentWordIndex + 1);
            translatedTextEl.textContent = displayedWords.join(' ');
            currentWordIndex++;
            
            // Schedule next word with random variation (±100ms) for organic feel
            // Base timing: 500ms, random variation: -100ms to +100ms
            const baseDelay = 500;
            const randomVariation = (Math.random() * 100) - 100; // -100 to +100
            const delay = baseDelay + randomVariation;
            setTimeout(displayNextWord, delay);
          }
        }
        
        // Start displaying words
        displayNextWord();
      }, 3000);
    });
  }

  // No splash hooks; keep code minimal
});

// Removed global overlay test helpers
