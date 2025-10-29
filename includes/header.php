<?php
// Mobile-first HTML head and header
?><!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, viewport-fit=cover">
    <title>HandScribe - Live Translation</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
    <!-- MediaPipe CDN libraries for WIP setup -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/holistic/holistic.js"></script>
  </head>
  <body class="mobile-app">
    <!-- Splash Screen -->
    <div class="splash" id="splash">
      <div class="ring"></div>
      <img src="../assets/images/hand_logo.svg" alt="HandScribe Logo" class="logo">
      <h1>Welcome to HandScribe</h1>
      <p>Real-Time ASL Translation</p>
      <footer class="splash-footer">© 2025 HandScribe • All Rights Reserved</footer>
    </div>
    
    <!-- Main Content Container (initially hidden) -->
    <div class="main-content" id="main-content" style="display: none;">
    <!-- Mobile Header -->
    <header class="app-header">
      <div class="header-content">
        <img src="../assets/images/hand_logo.svg" alt="HandScribe Logo" class="header-logo">
        <h1 class="app-title">LIVE TRANSLATION</h1>
      </div>
    </header>
