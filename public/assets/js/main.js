const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer({ antialias: true });
renderer.setSize(window.innerWidth, window.innerHeight);
document.body.appendChild(renderer.domElement);

const textureLoader = new THREE.TextureLoader();
const playerTexture = textureLoader.load('/assets/textures/player.png');
const houseTexture = textureLoader.load('/assets/textures/house.png');

const playerGeometry = new THREE.BoxGeometry();
const playerMaterial = new THREE.MeshStandardMaterial({ map: playerTexture });
const player = new THREE.Mesh(playerGeometry, playerMaterial);
scene.add(player);

const groundGeometry = new THREE.PlaneGeometry(100, 100);
const groundMaterial = new THREE.MeshStandardMaterial({ color: 0x228B22 });
const ground = new THREE.Mesh(groundGeometry, groundMaterial);
ground.rotation.x = -Math.PI / 2;
ground.position.y = -0.5;
scene.add(ground);

const ambientLight = new THREE.AmbientLight(0x404040);
scene.add(ambientLight);

const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
directionalLight.position.set(5, 10, 7.5);
scene.add(directionalLight);

const skyGeometry = new THREE.SphereGeometry(500, 32, 32);
const skyMaterial = new THREE.MeshBasicMaterial({ color: 0x87CEEB, side: THREE.BackSide });
const sky = new THREE.Mesh(skyGeometry, skyMaterial);
scene.add(sky);

function createHouse(x, z) {
    const houseGeometry = new THREE.BoxGeometry(2, 2, 2);
    const houseMaterial = new THREE.MeshStandardMaterial({ map: houseTexture });
    const house = new THREE.Mesh(houseGeometry, houseMaterial);
    house.position.set(x, 1, z);
    scene.add(house);

    const roofGeometry = new THREE.ConeGeometry(1.5, 1, 4);
    const roofMaterial = new THREE.MeshStandardMaterial({ color: 0x8B0000 });
    const roof = new THREE.Mesh(roofGeometry, roofMaterial);
    roof.position.set(x, 2.5, z);
    roof.rotation.y = Math.PI / 4;
    scene.add(roof);
}

createHouse(10, 10);
createHouse(-10, -10);
createHouse(10, -10);
createHouse(-10, 10);

camera.position.set(player.position.x, player.position.y + 2, player.position.z + 5);

let players = {};

const raycaster = new THREE.Raycaster();
const mouse = new THREE.Vector2();

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
    checkIntersections();

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
let messageQueue = [];
let lastUpdateTime = 0;
const updateInterval = 10;

const statusElement = document.getElementById('status');
const usersList = document.getElementById('users');
const loadingContainer = document.getElementById('loadingContainer');
const connectedMessage = document.getElementById('connectedMessage');
const errorMessage = document.getElementById('errorMessage');
const disconnectOverlay = document.getElementById('disconnectOverlay');
const disconnectMessage = document.querySelector('.disconnect-message');

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

const progressFill = document.getElementById('progressFill');
const errorContainer = document.getElementById('errorContainer');
const retryButton = document.getElementById('retryButton');
let loadingProgress = 0;

function updateLoadingProgress(progress) {
    loadingProgress = Math.min(progress, 100);
    progressFill.style.width = `${loadingProgress}%`;
}

function startLoading() {
    loadingProgress = 0;
    updateLoadingProgress(0);
    errorContainer.style.display = 'none';
    loadingContainer.style.display = 'flex';
    
    const loadingInterval = setInterval(() => {
        if (loadingProgress < 90) {
            updateLoadingProgress(loadingProgress + 10);
        }
    }, 500);

    return loadingInterval;
}

const loadingStatus = document.querySelector('.loading-status');

function handleConnection() {
    updateLoadingProgress(100);
    loadingStatus.textContent = 'Loaded';
    
    setTimeout(() => {
        loadingContainer.style.opacity = '0';
        setTimeout(() => {
            loadingContainer.style.display = 'none';
        }, 300);
    }, 3000);
}

function displayError() {
    errorContainer.style.display = 'block';
    updateLoadingProgress(100);
}

retryButton.addEventListener('click', () => {
    location.reload();
});

const loadingInterval = startLoading();

ws.onopen = function() {
    clearInterval(loadingInterval);
    handleConnection();

    fetchUserData().then(() => {
        ws.send(JSON.stringify({ type: 'userJoin', userId: userId }));

        while (messageQueue.length > 0) {
            const message = messageQueue.shift();
            ws.send(JSON.stringify(message));
        }
    });
};

ws.onclose = function(event) {
    clearInterval(loadingInterval);
    displayError();
    showDisconnectOverlay(event.code);
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
                        players[playerId] = createPlayer(data.x, data.z, 0xe74c3c, data.username, data.health, data.mana);
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
            updatePlayerPositions(message.data);
        } else if (message.type === 'userDisconnect') {
            removeUserFromList(message.id);
            removePlayer(message.id);
        } else if (message.type === 'userJoined') {
            const newUserData = message.data;
            const x = newUserData.x || 0;
            const y = newUserData.y || 0;
            const color = newUserData.id === userId ? 0x00ff00 : 0xe74c3c;
            if (!players[newUserData.id]) {
                players[newUserData.id] = createPlayer(x, y, color, newUserData.username, newUserData.health, newUserData.mana);
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
    clearInterval(loadingInterval);
    displayError();
    statusElement.textContent = 'WebSocket error: ' + error.message;
};

function createPlayerLabel(username, health, mana) {
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    
    canvas.width = 256;
    canvas.height = 128;

    context.font = '32px Arial';
    context.fillStyle = '#ffffff';
    context.textAlign = 'center';
    context.fillText(username, canvas.width / 2, 32);

    context.fillStyle = '#e74c3c';
    context.fillRect(28, 64, 200 * (health / 100), 20);

    context.fillStyle = '#3498db';
    context.fillRect(28, 96, 200 * (mana / 100), 20);
    
    const texture = new THREE.CanvasTexture(canvas);
    const spriteMaterial = new THREE.SpriteMaterial({ map: texture });
    const sprite = new THREE.Sprite(spriteMaterial);
    sprite.scale.set(4, 2, 1);
    
    return sprite;
}

function createPlayer(x, y, color, username, health = 100, mana = 100) {
    const geometry = new THREE.BoxGeometry();
    const material = new THREE.MeshStandardMaterial({ map: playerTexture });
    const player = new THREE.Mesh(geometry, material);
    player.position.set(x, y, 0);
    
    if (username) {
        const label = createPlayerLabel(username, health, mana);
        label.position.set(0, 1.5, 0);
        player.add(label);
    }
    
    const outlineMaterial = new THREE.MeshBasicMaterial({ color: 0x000000, side: THREE.BackSide });
    const outline = new THREE.Mesh(geometry, outlineMaterial);
    outline.scale.multiplyScalar(1.05);
    player.add(outline);
    
    scene.add(player);
    return player;
}

function updatePlayerPositions(playersData) {
    for (const id in playersData) {
        if (id !== userId) { 
            if (!players[id]) {
                players[id] = createPlayer(playersData[id].x, playersData[id].y, 0xe74c3c, playersData[id].username, playersData[id].health, playersData[id].mana);
            } else {
                players[id].position.set(playersData[id].x, playersData[id].y, 0);
            }
        }
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
                players[userId] = createPlayer(0, 0, 0x00ff00, data.username, data.health, data.mana);
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

function onMouseMove(event) {
    mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
    mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
}

function checkIntersections() {
    raycaster.setFromCamera(mouse, camera);
    const intersects = raycaster.intersectObjects(Object.values(players));

    Object.values(players).forEach(player => {
        if (player.children[0]) {
            player.children[0].visible = false;
        }
    });

    if (intersects.length > 0) {
        const intersectedPlayer = intersects[0].object;
        if (intersectedPlayer.children[0]) {
            intersectedPlayer.children[0].visible = true;
        }
    }
}

function showDisconnectOverlay(errorCode) {
    disconnectOverlay.style.display = 'flex';
    disconnectMessage.textContent = `Disconnected from server. Error code: ${errorCode}`;
}

document.addEventListener('mousemove', onMouseMove, false);