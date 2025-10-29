document.addEventListener('DOMContentLoaded', function () {
  console.log('HandScribe starter loaded.');
  
  // Camera functionality
  const cameraFeed = document.getElementById('camera-feed');
  const cameraPlaceholder = document.getElementById('camera-placeholder');
  const cameraError = document.getElementById('camera-error');
  const enableCameraBtn = document.getElementById('enable-camera-btn');
  const poseCanvas = document.getElementById('pose-canvas');
  const poseCtx = poseCanvas ? poseCanvas.getContext('2d') : null;
  
  let stream = null;
  // minimal state

  function updateCanvasSize() {
    if (!poseCanvas || !cameraFeed) return;
    // Match canvas drawing buffer to displayed size
    const width = cameraFeed.clientWidth;
    const height = cameraFeed.clientHeight;
    if (width === 0 || height === 0) return;
    if (poseCanvas.width !== width || poseCanvas.height !== height) {
      poseCanvas.width = width;
      poseCanvas.height = height;
    }
  }

  function drawOverlayRectangle() {
    if (!poseCtx || !poseCanvas) return;
    // Clear previous frame
    poseCtx.clearRect(0, 0, poseCanvas.width, poseCanvas.height);
    // Rectangle is 50% of frame, centered
    const rectWidth = poseCanvas.width * 0.5;
    const rectHeight = poseCanvas.height * 0.5;
    const rectX = (poseCanvas.width - rectWidth) / 2;
    const rectY = (poseCanvas.height - rectHeight) / 2;
    poseCtx.lineWidth = 4;
    poseCtx.strokeStyle = '#ff0000';
    poseCtx.strokeRect(rectX, rectY, rectWidth, rectHeight);
  }

  function renderOverlay() {
    updateCanvasSize();
    drawOverlayRectangle();
  }

  // Expose for console debugging
  window.renderOverlay = renderOverlay;
  window.handscribeOverlay = {
    updateCanvasSize,
    drawOverlayRectangle,
    renderOverlay
  };
  
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
        renderOverlay();
      }, { once: true });
      
      // Simple window resize redraw
      window.addEventListener('resize', renderOverlay);
      
      // Initial draw
      renderOverlay();
      
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
    window.removeEventListener('resize', renderOverlay);
    
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

// Global overlay helpers (available even if DOMContentLoaded handler didn't run)
(function setupGlobalOverlayHelpers() {
  function globalUpdateCanvasSize() {
    const canvas = document.getElementById('pose-canvas');
    const video = document.getElementById('camera-feed');
    if (!canvas || !video) return;
    const w = video.clientWidth;
    const h = video.clientHeight;
    if (!w || !h) return;
    if (canvas.width !== w || canvas.height !== h) {
      canvas.width = w;
      canvas.height = h;
    }
  }

  function globalDrawOverlayRectangle() {
    const canvas = document.getElementById('pose-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    const rw = canvas.width * 0.5;
    const rh = canvas.height * 0.5;
    const rx = (canvas.width - rw) / 2;
    const ry = (canvas.height - rh) / 2;
    ctx.lineWidth = 4;
    ctx.strokeStyle = '#ff0000';
    ctx.strokeRect(rx, ry, rw, rh);
  }

  function globalRenderOverlay() {
    globalUpdateCanvasSize();
    globalDrawOverlayRectangle();
  }

  // Expose
  window.renderOverlay = globalRenderOverlay;
  window.handscribeOverlay = {
    updateCanvasSize: globalUpdateCanvasSize,
    drawOverlayRectangle: globalDrawOverlayRectangle,
    renderOverlay: globalRenderOverlay
  };
})();
