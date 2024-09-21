const config = {
    type: Phaser.AUTO,
    width: window.innerWidth,
    height: window.innerHeight,
    physics: {
        default: 'arcade',
        arcade: {
            gravity: { y: 0 },
            debug: false
        }
    },
    scene: {
        preload: preload,
        create: create,
        update: update
    }
};

const game = new Phaser.Game(config);

let players = {};
let userId;
const statusElement = document.getElementById('status');
const usersList = document.getElementById('users');
const loadingContainer = document.getElementById('loadingContainer');
const connectedMessage = document.getElementById('connectedMessage');
const errorMessage = document.getElementById('errorMessage');

const ws = new WebSocket('ws://localhost:8080');

function preload() {}

function create() {
    userId = generateUserId();
    players[userId] = createPlayerTriangle(400, 300, 0x1abc9c);
    this.cursors = this.input.keyboard.createCursorKeys();
    setupWebSocket();
}

function update() {
    if (this.cursors.left.isDown) {
        players[userId].setVelocityX(-160);
        sendPlayerUpdate();
    } else if (this.cursors.right.isDown) {
        players[userId].setVelocityX(160);
        sendPlayerUpdate();
    } else {
        players[userId].setVelocityX(0);
    }

    if (this.cursors.up.isDown) {
        players[userId].setVelocityY(-160);
        sendPlayerUpdate();
    } else if (this.cursors.down.isDown) {
        players[userId].setVelocityY(160);
        sendPlayerUpdate();
    } else {
        players[userId].setVelocityY(0);
    }
}

function createPlayerTriangle(x, y, color) {
    const triangle = game.scene.scenes[0].physics.add.sprite(x, y, null);
    triangle.setTint(color);
    triangle.setDisplaySize(20, 20);
    return triangle;
}

function setupWebSocket() {
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

            if (message.type === 'playerUpdate') {
                updatePlayerPositions(message.data);
            } else if (message.type === 'userUpdate') {
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
        displayError();
    };

    ws.onclose = function() {
        displayError();
    };
}

function sendPlayerUpdate() {
    const playerData = {
        id: userId,
        x: players[userId].x,
        y: players[userId].y
    };
    ws.send(JSON.stringify({ type: 'playerUpdate', data: playerData }));
}

function updatePlayerPositions(playersData) {
    for (const id in playersData) {
        if (!players[id]) {
            players[id] = createPlayerTriangle(playersData[id].x, playersData[id].y, 0xe74c3c);
        }
        players[id].setPosition(playersData[id].x, playersData[id].y);
    }
}

function removePlayer(id) {
    if (players[id]) {
        players[id].destroy();
        delete players[id];
    }
}

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

function generateUserId() {
    return Math.floor(Math.random() * 10000).toString();
}

function handleConnection() {
    loadingContainer.classList.add('hidden');
    connectedMessage.style.display = 'block';
}

function displayError() {
    loadingContainer.classList.remove('hidden');
    errorMessage.style.display = 'block';
}