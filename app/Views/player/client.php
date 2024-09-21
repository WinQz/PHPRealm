<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHPRealm</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #2c3e50;
            color: #ecf0f1;
            text-align: center;
            overflow: hidden;
        }
        .loading-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(44, 62, 80, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        .loading-container.hidden {
            opacity: 0;
            visibility: hidden;
        }
        .loading-text {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .spinner {
            border: 8px solid rgba(44, 62, 80, 0.2);
            border-top: 8px solid #1abc9c;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .connected-message {
            display: none;
            font-size: 1.5rem;
            color: #1abc9c;
            margin-top: 20px;
        }
        .error-message {
            display: none;
            font-size: 1.5rem;
            color: #e74c3c;
            margin-top: 20px;
        }
        #status {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="loading-container" id="loadingContainer">
        <div class="loading-text">PHPRealm</div>
        <div class="spinner"></div>
        <div class="connected-message" id="connectedMessage">Connected to Server</div>
        <div class="error-message" id="errorMessage">Error Connecting to Server</div>
    </div>

    <div id="status"></div>
    <ul id="users"></ul>

    <!-- Include the main JS module file -->
    <script src="https://cdn.jsdelivr.net/npm/phaser@3.55.2/dist/phaser.min.js"></script>
    <script type="text/javascript" src="/assets/js/main.js?v=10"></script>
</body>
</html>