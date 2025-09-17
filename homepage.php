<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst - Your Health, Our Priority</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        #hero-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            cursor: grab;
        }
        #hero-canvas:grabbing {
            cursor: grabbing;
        }
        .content-overlay {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-pink-50 text-gray-800">

    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-20">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="#" class="text-2xl font-bold text-purple-600">HealthFirst</a>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="#home" class="text-gray-600 hover:text-purple-600">Home</a>
                <a href="#services" class="text-gray-600 hover:text-purple-600">Services</a>
                <a href="contact.php" class="text-gray-600 hover:text-purple-600">Contact</a>
                <a href="./auth/login.php" class="text-gray-600 hover:text-purple-600">Login</a>
                <a href="./auth/register.php" class="bg-purple-600 text-white px-4 py-2 rounded-full hover:bg-purple-700 transition duration-300">Register</a>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-gray-600 hover:text-purple-600 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
        </nav>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white py-4">
            <a href="#home" class="block text-center py-2 text-gray-600 hover:bg-purple-50">Home</a>
            <a href="#services" class="block text-center py-2 text-gray-600 hover:bg-purple-50">Services</a>
            <a href="contact.php" class="block text-center py-2 text-gray-600 hover:bg-purple-50">Contact</a>
            <a href="./auth/login.php" class="block text-center py-2 text-gray-600 hover:bg-purple-50">Login</a>
            <a href="./auth/register.php" class="block text-center py-2 text-purple-600 font-semibold hover:bg-purple-50">Register</a>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="relative min-h-screen flex items-center bg-purple-50 overflow-hidden">
        <canvas id="hero-canvas"></canvas>
        <div class="content-overlay container mx-auto px-6 text-center">
            <div class="bg-white/70 backdrop-blur-sm p-8 md:p-12 rounded-xl max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-4">A New Era of Healthcare</h1>
                <p class="text-lg md:text-xl text-gray-700 mb-8">Connect with top medical professionals. Your wellness journey, simplified and secured.</p>
                <a href="./auth/register.php" class="bg-purple-600 text-white px-8 py-3 rounded-full text-lg font-semibold hover:bg-purple-700 transition duration-300">Join Now</a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center mb-12">Our Core Features</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Service Card 1 -->
                <div class="bg-gray-100 p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition-shadow duration-300">
                    <div class="text-purple-600 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Secure Login</h3>
                    <p class="text-gray-600">Your personal health data is protected with our secure authentication system.</p>
                </div>
                <!-- Service Card 2 -->
                <div class="bg-gray-100 p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition-shadow duration-300">
                    <div class="text-purple-600 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Expert Doctors</h3>
                    <p class="text-gray-600">Once logged in, access a network of highly qualified medical professionals.</p>
                </div>
                <!-- Service Card 3 -->
                <div class="bg-gray-100 p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition-shadow duration-300">
                    <div class="text-purple-600 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 4h5m-5 4h5m-5-8h5"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Appointment Booking</h3>
                    <p class="text-gray-600">Easily schedule and manage your appointments from your user dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-gray-800 text-white py-10">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; 2025 HealthFirst. All Rights Reserved.</p>
            <p class="text-gray-400">Your Health, Our Priority.</p>
        </div>
    </footer>

    <script>
        // --- Mobile Menu Toggle ---
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // --- Three.js 3D DNA Animation ---
        const canvas = document.querySelector('#hero-canvas');
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ 
            canvas: canvas,
            alpha: true 
        });

        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);

        // --- DNA Group ---
        const dnaGroup = new THREE.Group();
        scene.add(dnaGroup);

        // --- Helper function to create DNA parts ---
        function createStrand(isReversed) {
            const points = [];
            for (let i = 0; i < 20; i++) {
                const x = Math.cos(i * 0.5) * 5 * (isReversed ? -1 : 1);
                const z = Math.sin(i * 0.5) * 5 * (isReversed ? -1 : 1);
                const y = i * 2 - 20;
                points.push(new THREE.Vector3(x, y, z));
            }
            const curve = new THREE.CatmullRomCurve3(points);
            const geometry = new THREE.TubeGeometry(curve, 64, 0.4, 8, false);
            const material = new THREE.MeshStandardMaterial({ color: 0x5b21b6, roughness: 0.5 }); // purple-800
            return new THREE.Mesh(geometry, material);
        }

        function createRung(y) {
            const rungGeometry = new THREE.CylinderGeometry(0.2, 0.2, 10, 8);
            const rungMaterial = new THREE.MeshStandardMaterial({ color: Math.random() > 0.5 ? 0xa855f7 : 0xc084fc, metalness: 0.2 }); // purple-500 and purple-400
            const rung = new THREE.Mesh(rungGeometry, rungMaterial);
            rung.position.y = y;
            rung.rotation.z = Math.PI / 2;
            return rung;
        }
        
        // --- Build DNA ---
        const strand1 = createStrand(false);
        const strand2 = createStrand(true);
        dnaGroup.add(strand1, strand2);

        for (let i = 0; i < 20; i++) {
            const y = i * 2 - 20;
            const rung = createRung(y);
            dnaGroup.add(rung);
        }
        
        // --- Lighting ---
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        scene.add(ambientLight);
        const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
        directionalLight.position.set(5, 10, 7.5);
        scene.add(directionalLight);
        
        // --- Camera Position ---
        camera.position.z = 40;

        // --- Mouse Interaction ---
        let isMouseDown = false;
        let previousMousePosition = { x: 0, y: 0 };

        canvas.addEventListener('mousedown', (e) => {
            isMouseDown = true;
            previousMousePosition = { x: e.clientX, y: e.clientY };
        });
        canvas.addEventListener('mouseup', () => isMouseDown = false);
        canvas.addEventListener('mouseleave', () => isMouseDown = false);
        canvas.addEventListener('mousemove', (e) => {
            if (!isMouseDown) return;
            const deltaX = e.clientX - previousMousePosition.x;
            const deltaY = e.clientY - previousMousePosition.y;
            dnaGroup.rotation.y += deltaX * 0.005;
            dnaGroup.rotation.x += deltaY * 0.005;
            previousMousePosition = { x: e.clientX, y: e.clientY };
        });
        
        // --- Animation Loop ---
        function animate() {
            requestAnimationFrame(animate);
            if (!isMouseDown) {
                dnaGroup.rotation.y += 0.002;
            }
            renderer.render(scene, camera);
        }
        animate();

        // --- Handle Window Resize ---
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

    </script>
</body>
</html>
