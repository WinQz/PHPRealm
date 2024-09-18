<script>
    const ws = new WebSocket('ws://localhost:8080');

    ws.onopen = function() {
        console.log('Connected to WebSocket server');
        fetchUserData();
    };

    ws.onmessage = function(event) {
        console.log('Received message:', event.data);
    };

    ws.onerror = function(error) {
        console.log('WebSocket error:', error);
    };

    function fetchUserData() {
        fetch('/api/client/player/getUserData')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error fetching user data:', data.error);
                } else {
                    console.log('User data fetched:', data);
                    
                    ws.send(JSON.stringify({ userData: data }));
                }
            })
            .catch(error => console.error('Fetch error:', error));
    }
</script>