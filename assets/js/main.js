document.addEventListener('DOMContentLoaded', function () {
  console.log('HandScribe starter loaded.');
  
  // Camera functionality
  const cameraFeed = document.getElementById('camera-feed');
  const cameraPlaceholder = document.getElementById('camera-placeholder');
  const cameraError = document.getElementById('camera-error');
  const enableCameraBtn = document.getElementById('enable-camera-btn');
  
  let stream = null;
  
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
      
      console.log('Camera started successfully');
      
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
});
