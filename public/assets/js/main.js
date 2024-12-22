const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer({ antialias: true });
renderer.setSize(window.innerWidth, window.innerHeight);
document.body.appendChild(renderer.domElement);

const playerGeometry = new THREE.BoxGeometry();
const playerMaterial = new THREE.MeshStandardMaterial({ color: 0x00ff00 });
const player = new THREE.Mesh(playerGeometry, playerMaterial);
scene.add(player);

const groundGeometry = new THREE.PlaneGeometry(100, 100);
const groundMaterial = new THREE.MeshStandardMaterial({ color: 0x808080 });
const ground = new THREE.Mesh(groundGeometry, groundMaterial);
ground.rotation.x = -Math.PI / 2;
ground.position.y = -0.5;
scene.add(ground);

const ambientLight = new THREE.AmbientLight(0x404040);
scene.add(ambientLight);

const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
directionalLight.position.set(5, 10, 7.5);
scene.add(directionalLight);

camera.position.set(player.position.x, player.position.y + 2, player.position.z + 5);

let moveForward = false;
let moveBackward = false;
let moveLeft = false;
let moveRight = false;
const moveSpeed = 0.1;

let isRightMouseDown = false;
let previousMousePosition = {
    x: 0,
    y: 0
};

let cameraOffset = new THREE.Vector3(0, 2, 5);

let isJumping = false;
const jumpHeight = 1;
const jumpSpeed = 0.1;
let jumpDirection = 1;

function updatePlayerPosition() {
    const direction = new THREE.Vector3();
    camera.getWorldDirection(direction);

    const right = new THREE.Vector3();
    right.crossVectors(camera.up, direction).normalize();

    const forward = new THREE.Vector3(direction.x, 0, direction.z).normalize();

    const originalPosition = player.position.clone();

    if (moveForward) player.position.add(forward.clone().multiplyScalar(moveSpeed));
    if (moveBackward) player.position.add(forward.clone().multiplyScalar(-moveSpeed));
    if (moveLeft) player.position.add(right.clone().multiplyScalar(moveSpeed));
    if (moveRight) player.position.add(right.clone().multiplyScalar(-moveSpeed));

    if (isJumping) {
        player.position.y += jumpSpeed * jumpDirection;
        if (player.position.y >= jumpHeight) {
            jumpDirection = -1;
        } else if (player.position.y <= 0) {
            player.position.y = 0;
            isJumping = false;
            jumpDirection = 1;
        }
    }

    if (!originalPosition.equals(player.position)) {
        sendPositionUpdate();
    }
}

let lastPositionUpdate = 0;
const UPDATE_INTERVAL = 50;

function sendPositionUpdate() {
    const now = Date.now();
    if (now - lastPositionUpdate >= UPDATE_INTERVAL) {
        if (ws.readyState === WebSocket.OPEN && userId) {
            const updateMessage = {
                type: 'playerUpdate',
                data: {
                    id: userId,
                    x: player.position.x,
                    y: player.position.z,
                    z: player.position.y,
                    isJumping: isJumping
                }
            };
            ws.send(JSON.stringify(updateMessage));
        }
        lastPositionUpdate = now;
    }
}

function animate() {
    requestAnimationFrame(animate);

    updatePlayerPosition();

    camera.position.copy(player.position).add(cameraOffset);
    camera.lookAt(player.position);

    renderer.render(scene, camera);
}
animate();

window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
});

document.addEventListener('keydown', (event) => {
    switch (event.key) {
        case 'w':
        case 'W':
            moveForward = true;
            break;
        case 's':
        case 'S':
            moveBackward = true;
            break;
        case 'a':
        case 'A':
            moveLeft = true;
            break;
        case 'd':
        case 'D':
            moveRight = true;
            break;
        case ' ':
            if (!isJumping) {
                isJumping = true;
            }
            break;
    }
});

document.addEventListener('keyup', (event) => {
    switch (event.key) {
        case 'w':
        case 'W':
            moveForward = false;
            break;
        case 's':
        case 'S':
            moveBackward = false;
            break;
        case 'a':
        case 'A':
            moveLeft = false;
            break;
        case 'd':
        case 'D':
            moveRight = false;
            break;
    }
});

document.addEventListener('mousedown', (event) => {
    if (event.button === 2) {
        isRightMouseDown = true;
        previousMousePosition.x = event.clientX;
        previousMousePosition.y = event.clientY;
    }
});

document.addEventListener('mousemove', (event) => {
    if (isRightMouseDown) {
        const deltaX = event.clientX - previousMousePosition.x;
        const deltaY = event.clientY - previousMousePosition.y;

        const rotationSpeed = 0.005;

        const spherical = new THREE.Spherical();
        spherical.setFromVector3(cameraOffset);
        spherical.theta -= deltaX * rotationSpeed;
        spherical.phi = Math.min(Math.max(spherical.phi - deltaY * rotationSpeed, 0.1), Math.PI - 0.1);
        cameraOffset.setFromSpherical(spherical);

        previousMousePosition.x = event.clientX;
        previousMousePosition.y = event.clientY;
    }
});

document.addEventListener('mouseup', (event) => {
    if (event.button === 2) {
        isRightMouseDown = false;
    }
});

document.addEventListener('contextmenu', (event) => {
    event.preventDefault();
});

const ws = new WebSocket('ws://localhost:8080');
let userId;
let players = {};
let messageQueue = [];
let lastUpdateTime = 0;
const updateInterval = 10;

const statusElement = document.getElementById('status');
const usersList = document.getElementById('users');
const loadingContainer = document.getElementById('loadingContainer');
const connectedMessage = document.getElementById('connectedMessage');
const errorMessage = document.getElementById('errorMessage');

const healthFill = document.getElementById('healthFill');
const manaFill = document.getElementById('manaFill');
const inventoryList = document.getElementById('inventoryList');
const inventory = document.getElementById('inventory');
const inventoryButton = document.getElementById('inventoryButton');

inventoryButton.addEventListener('click', () => {
    if (inventory.style.display === 'none') {
        inventory.style.display = 'block';
    } else {
        inventory.style.display = 'none';
    }
});

function updateHealth(health) {
    healthFill.style.width = `${health}%`;
}

function updateMana(mana) {
    manaFill.style.width = `${mana}%`;
}

function updateInventory(items) {
    inventoryList.innerHTML = '';
    items.forEach(item => {
        const listItem = document.createElement('li');
        listItem.textContent = item;
        inventoryList.appendChild(listItem);
    });
}

updateHealth(75);
updateMana(50);
updateInventory(['Sword', 'Shield', 'Potion']);

ws.onopen = function() {
    statusElement.textContent = 'Connected to WebSocket server';
    handleConnection();

    fetchUserData().then(() => {
        ws.send(JSON.stringify({ type: 'userJoin', userId: userId }));

        while (messageQueue.length > 0) {
            const message = messageQueue.shift();
            ws.send(JSON.stringify(message));
        }
    });
};

ws.onclose = function() {
    displayError();
    setTimeout(() => {
        setupWebSocket();
    }, 2000);
};

ws.onmessage = function(event) {
    try {
        const message = JSON.parse(event.data);

        if (message.type === 'updatePlayerPosition') {
            Object.entries(message.data).forEach(([playerId, data]) => {
                if (playerId !== userId) {
                    if (!players[playerId]) {
                        players[playerId] = createPlayer(data.x, data.z, 0xe74c3c);
                    } else {
                        players[playerId].position.set(data.x, data.z, data.y);
                        if (data.isJumping) {
                            players[playerId].position.y += jumpSpeed * jumpDirection;
                        }
                    }
                }
            });
        } else if (message.type === 'userUpdate') {
            updateUsersList(message.data);
        } else if (message.type === 'userDisconnect') {
            removeUserFromList(message.id);
        } else if (message.type === 'userJoined') {
            const newUserData = message.data;
            const x = newUserData.x || 0;
            const y = newUserData.y || 0;
            const color = newUserData.id === userId ? 0x00ff00 : 0xe74c3c;
            if (!players[newUserData.id]) {
                players[newUserData.id] = createPlayer(x, y, color);
            }
            updateUsersList({ [newUserData.id]: newUserData });
        } else if (message.type === 'updateHealth') {
            updateHealth(message.data.health);
        } else if (message.type === 'updateMana') {
            updateMana(message.data.mana);
        } else if (message.type === 'updateInventory') {
            updateInventory(message.data.items);
        }
    } catch (e) {
        console.error('Failed to parse message:', e);
    }
};

ws.onerror = function(error) {
    statusElement.textContent = 'WebSocket error: ' + error.message;
    displayError();
};

function createPlayer(x, y, color) {
    const geometry = new THREE.BoxGeometry();
    const material = new THREE.MeshStandardMaterial({ color });
    const player = new THREE.Mesh(geometry, material);
    player.position.set(x, y, 0);
    scene.add(player);
    return player;
}

function updatePlayerPositions(playersData) {
    for (const id in playersData) {
        if (!players[id]) {
            players[id] = createPlayer(playersData[id].x, playersData[id].y, 0xe74c3c);
        }
        players[id].position.set(playersData[id].x, playersData[id].y, 0);
    }
}

function removePlayer(id) {
    if (players[id]) {
        scene.remove(players[id]);
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
                userId = data.id;
            }
        })
        .catch(error => console.error('Fetch error:', error));
}

function updateUsersList(newUsers) {
    const existingUsers = {};

    usersList.querySelectorAll('li').forEach(item => {
        const id = item.id.replace('user-', '');
        existingUsers[id] = {
            username: item.textContent.split(' ')[0],
            status: item.textContent.split(' ')[1].replace(/[()]/g, '')
        };
    });

    const mergedUsers = { ...existingUsers, ...newUsers };

    usersList.innerHTML = '';
    for (const id in mergedUsers) {
        const user = mergedUsers[id];
        const listItem = document.createElement('li');
        listItem.id = `user-${id}`;
        listItem.textContent = `${user.username} (${user.status})`;
        usersList.appendChild(listItem);
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

function displayError() {
    loadingContainer.classList.remove('hidden');
    errorMessage.style.display = 'block';
}