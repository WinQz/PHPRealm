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
        
        // Assuming the message data is in JSON format
        const message = JSON.parse(event.data);

        if (message.type === 'userUpdate') {
            updateUsersList(message.data);
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

        const usersArray = Object.values(users);

        usersList.innerHTML = '';

        usersArray.forEach(user => {
            const listItem = document.createElement('li');
            listItem.textContent = `${user.username} (${user.status})`;
            usersList.appendChild(listItem);
        });
    }
</script>