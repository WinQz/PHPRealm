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
    </div>

    <div id="status"></div>
    <ul id="users"></ul>

    <script>
    const ws = new WebSocket('ws://localhost:8080');

    const statusElement = document.getElementById('status');
    const usersList = document.getElementById('users');
    const loadingContainer = document.getElementById('loadingContainer');
    const connectedMessage = document.getElementById('connectedMessage');

    let userId = null;

    ws.onopen = function() {
        console.log('Connected to WebSocket server');
        statusElement.textContent = 'Connected to WebSocket server';
        fetchUserData().then(() => {
            handleConnection();
        });
    };

    ws.onmessage = function(event) {
        console.log('Received message:', event.data);

        try {
            const message = JSON.parse(event.data);
            console.log('Parsed message:', message);

            if (message.type === 'userUpdate') {
                console.log('User update data:', message.data);
                updateUsersList(message.data);
            } else if (message.type === 'userDisconnect') {
                console.log(`User disconnected: ${message.id}`);
                removeUserFromList(message.id);
            }
        } catch (e) {
            console.error('Failed to parse message:', e);
        }
    };

    ws.onerror = function(error) {
        console.log('WebSocket error:', error);
        statusElement.textContent = 'WebSocket error: ' + error.message;
    };

    function fetchUserData() {
        return fetch('/api/client/player/getUserData')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error fetching user data:', data.error);
                } else {
                    console.log('User data fetched:', data);
                    userId = data.id;
                    console.log(`Sending userId: ${userId}`);
                    
                    ws.send(JSON.stringify({ type: 'userData', userId: userId }));
                }
            })
            .catch(error => console.error('Fetch error:', error));
    }

    function updateUsersList(users) {
        usersList.innerHTML = '';

        for (const id in users) {
            const user = users[id];
            if (user) {
                const listItem = document.createElement('li');
                listItem.id = `user-${id}`;
                listItem.textContent = `${user.username} (${user.status})`;
                usersList.appendChild(listItem);
            }
        }
    }

    function removeUserFromList(id) {
        const userItem = document.getElementById(`user-${id}`);
        if (userItem) {
            userItem.remove();
        }
    }

    function handleConnection() {
        loadingContainer.classList.add('hidden');
        connectedMessage.style.display = 'block';
    }

    fetchUserData().then(() => {
        handleConnection();
    });
</script>
</body>
</html>