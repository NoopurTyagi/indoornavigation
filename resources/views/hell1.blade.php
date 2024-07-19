<!DOCTYPE html>
<html lang="en">

<head>
    <style>
        .visited {
            font-weight: bold;
            color: green;
        }
    </style>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        h1 {
            margin: 20px 0;
            text-align: center;
            font-size: 2.5em;
            color: #333;
        }

        .panel {
            background: linear-gradient(135deg, #ffffff 0%, #e6e9f0 100%);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            margin: 10px;
            flex: 1;
            max-width: 350px;
            min-width: 280px;
        }

        .waypoints-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
            width: 100%;
            max-width: 1200px;
        }

        .panel h2 {
            margin-top: 0;
            font-size: 1.5em;
            color: #444;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        li {
            margin-bottom: 10px;
            padding: 8px 10px;
            background: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background 0.3s;
        }

        li:hover {
            background: #e6e6e6;
        }

        button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }

        button:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        #navigate-btn {
            background: #28a745;
            border-radius: 50%;
            padding: 20px 25px;
            font-size: 1.5em;
            margin: 20px;
            transition: background 0.3s, transform 0.3s;
        }

        #navigate-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .central-panel {
            max-width: 100%;
            width: 100%;
            text-align: center;
            padding: 0 10px;
        }

        .central-panel ul {
            max-width: 500px;
            margin: 0 auto;
            text-align: left;
        }

        .central-panel button {
            margin-top: 15px;
        }

        #path-waypoints {
            position: relative !important;
            bottom: 24px !important;
            right: 377px !important;

        }

        #waypoints {
            overflow: scroll !important;
            height: 500px !important;

        }

        @media (max-width: 768px) {
            .panel {
                width: 90%;
                margin: 10px auto;
            }

            #navigate-btn {
                width: 80%;
                text-align: center;
                padding: 15px;
                font-size: 1.25em;
            }

            .central-panel {
                padding: 0 5%;
            }
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GLTF Viewer with Navigation (Three.js)</title>
    <script src="https://cdn.jsdelivr.net/npm/three@0.145.0/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.145.0/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.145.0/examples/js/loaders/GLTFLoader.js"></script>
</head>

<body>
    <h1>3D Demorgon Model</h1>
    <div id="waypoints" style="position: fixed; top: 0; right: 0; background: rgba(255, 255, 255, 0.8); padding: 10px;">
        <h2>Waypoints</h2>
        <ul id="waypoint-list"></ul>
    </div>
    <div id="selected-waypoints"
        style="position: fixed; bottom: 0; left: 0; background: rgba(255, 255, 255, 0.8); padding: 10px;">
        <h2>Selected Waypoints</h2>
        <p>Start Waypoint: <span id="start-waypoint">None</span></p>
        <p>End Waypoint: <span id="end-waypoint">None</span></p>
    </div>
    <div id="path-waypoints"
        style="position: fixed; bottom: 0; right: 0; background: rgba(255, 255, 255, 0.8); padding: 10px;">
        <h2>Waypoints Along the Path</h2>
        <ul id="path-waypoint-list"></ul>
        <button id="next-waypoint-btn" onclick="showNextWaypoint()">Next Waypoint</button>
        <p id="current-waypoint">Current Waypoint: None</p>
        <p id="reached-message" style="display: none;">You have reached the destination!</p>
    </div>

    <button onclick="navigate()">Navigate</button>
    <script src="https://github.com/spite/THREE.MeshLine/blob/master/src/THREE.MeshLine.js"></script>

    <script>
        let waypoints = [];
        let waypointMap = {};
        let startWaypoint = null;
        let endWaypoint = null;
        let line = null;
        let animatedObject = null;
        let animationPath = [];
        let animationIndex = 0;

        function loadWaypointsT() {
            const waypointList = document.getElementById('waypoint-list');

            fetch('/waypoints')
                .then(response => response.json())
                .then(data => {
                    waypoints = data;
                    data.forEach(waypoint => {
                        waypointMap[waypoint.id] = waypoint;
                        const listItem = document.createElement('li');
                        listItem.textContent = waypoint.name;
                        listItem.style.cursor = 'pointer';
                        listItem.onclick = () => selectWaypoint(waypoint.id);
                        waypointList.appendChild(listItem);
                    });
                })
                .catch(error => {
                    console.error('Error loading waypoints:', error);
                    alert('Failed to load waypoints. Please try again later.');
                });
        }

        // Initialize the Three.js scene
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0xf0f0f0);
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.set(0, 1, 10);
        const renderer = new THREE.WebGLRenderer({
            antialias: true
        });
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.body.appendChild(renderer.domElement);

        const controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.25;
        controls.screenSpacePanning = false;
        controls.maxPolarAngle = Math.PI / 2;

        // Add lighting to the scene
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
        scene.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
        directionalLight.position.set(5, 10, 7.5).normalize();
        scene.add(directionalLight);

        // Function to handle errors
        function handleError(error) {
            console.error("Error loading model:", error);
            alert("Oops! There was an error loading the model. Please try again later.");
        }

        // Function to load the GLTF model
        function loadGltfModel() {
            const loader = new THREE.GLTFLoader();
            loader.load(
                'https://NoopurTyagi.github.io/mymodel4.gltf',
                (gltf) => {
                    scene.add(gltf.scene);
                    console.log('Model loaded successfully');
                    loadWaypointsT(); // Load waypoints after model is loaded
                },
                undefined,
                handleError
            );
        }

        // Function to handle waypoint selection
        function selectWaypoint(waypointId) {
            const waypoint = waypointMap[waypointId];
            if (!startWaypoint) {
                startWaypoint = waypoint;
                document.getElementById('start-waypoint').textContent = waypoint.name;
            } else if (!endWaypoint) {
                endWaypoint = waypoint;
                document.getElementById('end-waypoint').textContent = waypoint.name;
                getPathBetweenWaypoints(startWaypoint.id, endWaypoint.id);
            }
        }

        // Function to get path between waypoints
        function getPathBetweenWaypoints(startId, endId) {
            fetch(`/path?start_id=${startId}&end_id=${endId}`)
                .then(response => response.json())
                .then(path => {
                    if (path.length === 0) {
                        alert("No path found!");
                        return;
                    }
                    console.log(path);
                    // Render the path
                    const points = path.map(waypoint => new THREE.Vector3(waypoint.x, waypoint.y, waypoint.z));
                    const geometry = new THREE.BufferGeometry().setFromPoints(points);
                    const material = new THREE.LineBasicMaterial({
                        color: 0xff0000
                    });
                    line = new THREE.Line(geometry, material);
                    scene.add(line);

                    const pathWaypointList = document.getElementById('path-waypoint-list');
                    pathWaypointList.innerHTML = '';
                    path.forEach((waypoint, index) => {
                        const listItem = document.createElement('li');
                        listItem.textContent = `${index + 1}. ${waypoint.name}`;
                        pathWaypointList.appendChild(listItem);
                    });

                    animationPath = path;
                    animationIndex = 0;

                    console.log('Path found and drawn');
                })
                .catch(error => {
                    console.error('Error fetching path:', error);
                });
        }

        // Function to start the animation along the path
        function startAnimation(path) {
            if (path.length < 2) {
                console.log('Path too short for animation');
                return;
            }

            if (animatedObject) {
                scene.remove(animatedObject);
            }

            const geometry = new THREE.BoxGeometry(0.2, 0.2, 0.2);
            const material = new THREE.MeshBasicMaterial({
                color: 0x0000ff
            });
            animatedObject = new THREE.Mesh(geometry, material);
            scene.add(animatedObject);

            animationPath = path;
            animationIndex = 0;
            moveAlongPath();
        }

        // Function to move the object along the path
        function moveAlongPath() {
            if (animationIndex >= animationPath.length) {
                console.log('Animation completed');
                return;
            }

            const waypoint = animationPath[animationIndex];
            animatedObject.position.set(waypoint.x, waypoint.y, waypoint.z);
        }

        // Function to show the next waypoint
        function showNextWaypoint() {
            if (animationIndex >= animationPath.length - 1) {
                document.getElementById('reached-message').style.display = 'block';
            } else {
                animationIndex++;
                moveAlongPath();
                const currentWaypoint = animationPath[animationIndex];
                document.getElementById('current-waypoint').textContent = `Current Waypoint: ${currentWaypoint.name}`;
                const pathWaypointList = document.getElementById('path-waypoint-list');
                pathWaypointList.children[animationIndex].classList.add('visited');
            }
        }

        // Function to start navigation
        function navigate() {
            if (!startWaypoint || !endWaypoint) {
                alert("Please select both start and end waypoints");
                return;
            }
            getPathBetweenWaypoints(startWaypoint.id, endWaypoint.id);
            startAnimation(animationPath);
        }

        // Animation loop
        function animate() {
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
        }

        // Load the GLTF model and start the animation loop
        loadGltfModel();
        animate();

        // Listen for device orientation events
        window.addEventListener('deviceorientation', handleOrientation);

        // Function to handle device orientation changes
        // Function to handle device orientation changes
        function handleOrientation(event) {
            const beta = event.beta; // Device tilt in the front-back direction
            const gamma = event.gamma; // Device tilt in the left-right direction
            const scaleFactor = 0.1; // Adjust scale factor as needed

            // Update animatedObject position based on device motion
            if (animatedObject) {
                animatedObject.position.x = gamma * scaleFactor;
                animatedObject.position.y = -beta * scaleFactor; // Invert beta for correct movement direction
            }

            controls.update(); // Update controls for OrbitControls interaction
            renderer.render(scene, camera); // Render the scene
        }


        // Resize handling
        window.addEventListener('resize', onWindowResize);

        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }
    </script>
</body>

</html>
