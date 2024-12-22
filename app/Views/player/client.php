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
            overflow: hidden;
        }
        .loading-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #2c3e50;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            transition: all 0.3s ease;
            opacity: 1;
        }
        .progress-bar {
            width: 300px;
            height: 20px;
            background: #2c3e50;
            border-radius: 10px;
            margin: 20px 0;
            overflow: hidden;
        }
        .progress-fill {
            width: 0%;
            height: 100%;
            background: #1abc9c;
            transition: width 0.3s ease;
        }
        .error-container {
            display: none;
            text-align: center;
            padding: 20px;
            background: rgba(231, 76, 60, 0.2);
            border-radius: 10px;
            margin: 20px;
        }
        .error-message {
            color: #e74c3c;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        .retry-button {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .retry-button:hover {
            background: #c0392b;
        }
        .loading-text {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .loading-status {
            color: #ecf0f1;
            font-size: 1.2rem;
            margin-bottom: 10px;
            transition: opacity 0.3s ease;
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
        .hud {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1000;
        }
        .health-bar, .mana-bar {
            width: 200px;
            height: 20px;
            background-color: #34495e;
            margin-bottom: 5px;
            border-radius: 5px;
            overflow: hidden;
        }
        .health-bar .fill, .mana-bar .fill {
            height: 100%;
            border-radius: 5px;
        }
        .health-bar .fill {
            background-color: #e74c3c;
            width: 100%;
        }
        .mana-bar .fill {
            background-color: #3498db;
            width: 100%;
        }
        .inventory {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 150px;
            background-color: #34495e;
            padding: 10px;
            border-radius: 5px;
            overflow-y: auto;
            display: none;
        }
        .inventory h2 {
            margin: 0 0 10px 0;
        }
        .inventory ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .inventory ul li {
            background-color: #2c3e50;
            padding: 5px;
            margin-bottom: 5px;
            border-radius: 3px;
        }
        .inventory-button {
            position: fixed;
            bottom: 10px;
            left: 10px;
            width: 50px;
            height: 50px;
            background-color: #1abc9c;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
        }
        .player-cube {
            outline: 2px solid #1abc9c;
        }
        .player-label {
            outline: 1px solid #ecf0f1;
        }
        .disconnect-overlay {
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
            z-index: 2000;
            transition: all 0.3s ease;
            opacity: 0;
            pointer-events: none;
        }
        .disconnect-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
        .disconnect-message {
            color: #e74c3c;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .reconnect-button {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .reconnect-button:hover {
            background: #16a085;
        }
    </style>
</head>
<body>
    <div class="loading-container" id="loadingContainer">
        <div class="loading-text">PHPRealm Client</div>
        <div class="loading-status">Loading into world...</div>
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill"></div>
        </div>
        <div class="error-container" id="errorContainer">
            <div class="error-message" id="errorMessage">Connection failed</div>
            <button class="retry-button" id="retryButton">Retry Connection</button>
        </div>
        <div class="connected-message" id="connectedMessage">Connected to Server</div>
    </div>

    <div class="hud">
        <div class="health-bar">
            <div class="fill" id="healthFill"></div>
        </div>
        <div class="mana-bar">
            <div class="fill" id="manaFill"></div>
        </div>
    </div>

    <div class="inventory" id="inventory">
        <h2>Inventory</h2>
        <ul id="inventoryList"></ul>
    </div>

    <button class="inventory-button" id="inventoryButton">I</button>

    <div id="status" style="display: none;">Not connected</div>

    <div class="disconnect-overlay" id="disconnectOverlay">
        <div class="disconnect-message">You have been disconnected</div>
        <button class="reconnect-button" id="reconnectButton">Reconnect</button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script type="text/javascript" src="/assets/js/main.js?v=33"></script>
    <script>
        function showDisconnectOverlay(errorCode) {
            const disconnectOverlay = document.getElementById('disconnectOverlay');
            const disconnectMessage = document.querySelector('.disconnect-message');
            disconnectMessage.textContent = `Disconnected from server. Error code: ${errorCode}`;
            disconnectOverlay.classList.add('show');
        }

        document.getElementById('reconnectButton').addEventListener('click', () => {
            location.reload();
        });
    </script>
</body>
</html>