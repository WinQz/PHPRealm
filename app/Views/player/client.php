<div id="status"></div>
<ul id="users"></ul>

<script>
    const ws = new WebSocket('ws://localhost:8080');

    const statusElement = document.getElementById('status');
    const usersList = document.getElementById('users');

    ws.onopen = function() {
        console.log('Connected to WebSocket server');
        statusElement.textContent = 'Connected to WebSocket server';
        fetchUserData();
    };

    ws.onmessage = function(event) {
        console.log('Received message:', event.data);
        
        const message = JSON.parse(event.data);

        if (message.type === 'userUpdate') {
            console.log('User update data:', message.data);
            updateUsersList(message.data);
        } else if (message.type === 'userDisconnect') {
            console.log(`User disconnected: ${message.userId}`);
            removeUserFromList(message.userId);
        }
    };

    ws.onerror = function(error) {
        console.log('WebSocket error:', error);
        statusElement.textContent = 'WebSocket error: ' + error.message;
    };

    function fetchUserData() {
        fetch('/api/client/player/getUserData')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error fetching user data:', data.error);
                } else {
                    console.log('User data fetched:', data);
                    
                    ws.send(JSON.stringify({ type: 'userData', userData: data }));
                }
            })
            .catch(error => console.error('Fetch error:', error));
    }

    function updateUsersList(users) {
        usersList.innerHTML = '';

        for (const userId in users) {
            const user = users[userId];
            if (user) {
                const listItem = document.createElement('li');
                listItem.id = `user-${userId}`;
                listItem.textContent = `${user.username} (${user.status})`;
                usersList.appendChild(listItem);
            }
        }
    }

    function removeUserFromList(userId) {
        const userItem = document.getElementById(`user-${userId}`);
        if (userItem) {
            userItem.remove();
        }
    }
</script>