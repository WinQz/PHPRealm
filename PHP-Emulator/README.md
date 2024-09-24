# PHP MMORPG Emulator

Advanced 2D MMORPG Emulator built with PHP and WebSockets, handling real-time player connections and interactions.

## Features
- **Real-Time Multiplayer:** Utilizes WebSockets for instant communication between server and clients.
- **Player Session Management:** Ensures each player has a single active session, handling duplicates gracefully.
- **Broadcasting:** Sends updates to all connected players about user connections and disconnections.
- **Modular Architecture:** Clean and organized codebase for easy maintenance and scalability.

## Prerequisites
Before you begin, ensure you have the following installed:

- **PHP 7.4 or higher**
- **Composer** for managing dependencies
- **Ratchet WebSocket library** (installed via Composer)

### Installation

```bash
git clone https://github.com/WinQz/PHP-MMORPG.git
cd PHP-Emulator
composer install
```

Then you edit the ```env``` file to your database needs.

When you succesfully did this you can run the following:
```
php run.php
```
This would make the Emulator listen on 8080.


### Roadmap

- More Performance
- Mob and Boss Raid
- World Rendering
- And More

### Authors
**WinQz** - Lead Developer
