document.addEventListener('DOMContentLoaded', function () {
  console.log('HandScribe starter loaded.');
  
  // Camera functionality
  const cameraFeed = document.getElementById('camera-feed');
  const cameraPlaceholder = document.getElementById('camera-placeholder');
  const cameraError = document.getElementById('camera-error');
  const enableCameraBtn = document.getElementById('enable-camera-btn');
  const poseCanvas = document.getElementById('pose-canvas');
  const poseCtx = poseCanvas ? poseCanvas.getContext('2d') : null;
  let animationFrameId = null;
  let pose = null;
  let isSending = false;
  
  let stream = null;
  // minimal state

  // Removed test overlay helpers

  async function processFrame() {
    if (!cameraFeed || !pose) {
      animationFrameId = window.requestAnimationFrame(processFrame);
      return;
    }
    if (!isSending) {
      isSending = true;
      try {
        await pose.send({ image: cameraFeed });
      } catch (e) {
        console.error('Pose send error:', e);
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
    const landmarks = results && results.poseLandmarks;
    if (!landmarks || !landmarks.length) return;
    const nose = landmarks[0];
    if (!nose) return;
    const x = nose.x * poseCanvas.width;
    const y = nose.y * poseCanvas.height;
    poseCtx.fillStyle = '#00ff00';
    poseCtx.beginPath();
    poseCtx.arc(x, y, Math.max(3, Math.round(poseCanvas.width * 0.006)), 0, Math.PI * 2);
    poseCtx.fill();
  }

  function initPose() {
    if (pose || typeof Pose === 'undefined') return;
    pose = new Pose({
      locateFile: f => "https://cdn.jsdelivr.net/npm/@mediapipe/pose/" + f
    });
    pose.setOptions({
      modelComplexity: 0,
      minDetectionConfidence: 0.5,
      minTrackingConfidence: 0.5
    });
    pose.onResults(onResults);
  }

  // No debug overlay exports
  
  // Function to start camera
  async function startCamera() {
    try {
      // Request camera access
      stream = await navigator.mediaDevices.getUserMedia({
        video: {
          width: { ideal: 1280 },
          height: { ideal: 720 },
          facingMode: 'environment' // Back camera
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
        // Initialize MediaPipe Pose and start processing loop
        initPose();
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
  
  // Check if camera is available on page load
  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    console.log('Camera API is available');
  } else {
    console.log('Camera API not available');
    handleCameraError({ name: 'NotSupportedError' });
  }
  
  // Clean up camera stream when page is unloaded
  window.addEventListener('beforeunload', stopCamera);

  // No splash hooks; keep code minimal
});

// Removed global overlay test helpers
