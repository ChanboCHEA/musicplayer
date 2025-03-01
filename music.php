<?php
// PHP Circle Music Visualization
// This script generates an animation of circles that react to simulated music beats

// Set content type to image/gif if outputting directly
header('Content-Type: text/html');

// Configuration
$width = 800;
$height = 600;
$frames = 40;
$fps = 12;
$background = [10, 10, 25]; // Dark blue background

// Create HTML page with the visualization
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Circle Music Visualization</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                background-color: rgb(<?php echo implode(',', $background); ?>);
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                font-family: Arial, sans-serif;
                color: white;
                flex-direction: column;
            }

            .container {
                position: relative;
                width: <?php echo $width; ?>px;
                height: <?php echo $height; ?>px;
            }

            .audio-controls {
                margin-top: 20px;
                padding: 15px;
                background-color: rgba(255, 255, 255, 0.1);
                border-radius: 10px;
                text-align: center;
            }

            audio {
                width: 100%;
                max-width: 500px;
            }

            h1 {
                text-shadow: 0 0 10px rgba(100, 100, 255, 0.8);
            }
        </style>
    </head>
    <body>
    <h1>Circle Music Visualization</h1>

    <div class="container">
        <canvas id="visualization" width="<?php echo $width; ?>" height="<?php echo $height; ?>"></canvas>
    </div>

    <div class="audio-controls">
        <h3>Audio Player</h3>
        <audio id="audio" controls autoplay>
            <source src="Messy.mp3" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
    </div>

    <script>
        const canvas = document.getElementById('visualization');
        const ctx = canvas.getContext('2d');
        const audio = document.getElementById('audio');

        // Audio context for analysis
        let audioContext = null;
        let analyser = null;
        let dataArray = null;
        let source = null;

        // Animation variables
        let animationId = null;
        let circles = [];
        let particles = [];

        // Colors for the visualization
        const colors = [
            { r: 41, g: 121, b: 255 },  // Blue
            { r: 117, g: 80, b: 255 },  // Purple
            { r: 200, g: 62, b: 255 },  // Magenta
            { r: 255, g: 83, b: 153 },  // Pink
            { r: 66, g: 245, b: 239 }   // Cyan
        ];

        // Initialize audio analyzer
        function initAudio() {
            // Create audio context
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            analyser = audioContext.createAnalyser();

            // Connect audio source to analyzer
            source = audioContext.createMediaElementSource(audio);
            source.connect(analyser);
            analyser.connect(audioContext.destination);

            // Set up analyzer
            analyser.fftSize = 256;
            const bufferLength = analyser.frequencyBinCount;
            dataArray = new Uint8Array(bufferLength);

            // Initialize circles
            createCircles();
        }

        // Create initial circles
        function createCircles() {
            circles = [];
            const numCircles = 12;

            // Create main circle ring
            for (let i = 0; i < numCircles; i++) {
                const angle = (i / numCircles) * Math.PI * 2;
                circles.push({
                    x: canvas.width / 2,
                    y: canvas.height / 2,
                    radius: 80,
                    baseRadius: 80,
                    targetRadius: 80,
                    angle: angle,
                    distance: 150,
                    color: colors[i % colors.length],
                    alpha: 0.7,
                    frequency: i * 2,
                    phase: Math.random() * Math.PI * 2
                });
            }

            // Create inner circle rings
            for (let ring = 1; ring < 3; ring++) {
                const ringCircles = Math.floor(numCircles / (ring + 1));
                for (let i = 0; i < ringCircles; i++) {
                    const angle = (i / ringCircles) * Math.PI * 2;
                    const distance = 75 * ring;
                    circles.push({
                        x: canvas.width / 2,
                        y: canvas.height / 2,
                        radius: 30 + (30 / ring),
                        baseRadius: 30 + (30 / ring),
                        targetRadius: 30 + (30 / ring),
                        angle: angle,
                        distance: distance,
                        color: colors[(i + ring) % colors.length],
                        alpha: 0.8,
                        frequency: i + 5 * ring,
                        phase: Math.random() * Math.PI * 2
                    });
                }
            }

            // Create center circle
            circles.push({
                x: canvas.width / 2,
                y: canvas.height / 2,
                radius: 100,
                baseRadius: 100,
                targetRadius: 100,
                angle: 0,
                distance: 0,
                color: { r: 255, g: 255, b: 255 },
                alpha: 0.15,
                frequency: 0,
                phase: 0
            });
        }

        // Create a particle effect
        function createParticles(x, y, color, count) {
            for (let i = 0; i < count; i++) {
                const angle = Math.random() * Math.PI * 2;
                const speed = 1 + Math.random() * 3;

                particles.push({
                    x: x,
                    y: y,
                    radius: 1 + Math.random() * 3,
                    color: color,
                    alpha: 1,
                    speed: speed,
                    angle: angle,
                    life: 1
                });
            }
        }

        // Update and draw particles
        function updateParticles() {
            for (let i = particles.length - 1; i >= 0; i--) {
                const p = particles[i];

                // Update position
                p.x += Math.cos(p.angle) * p.speed;
                p.y += Math.sin(p.angle) * p.speed;

                // Update life and alpha
                p.life -= 0.01;
                p.alpha = p.life;

                // Remove dead particles
                if (p.life <= 0) {
                    particles.splice(i, 1);
                    continue;
                }

                // Draw particle
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius * p.life, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(${p.color.r}, ${p.color.g}, ${p.color.b}, ${p.alpha})`;
                ctx.fill();
            }
        }

        // Main animation function
        function animate() {
            // Clear canvas
            ctx.fillStyle = 'rgb(<?php echo implode(',', $background); ?>)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Get audio data if available
            let audioData = null;
            if (analyser) {
                analyser.getByteFrequencyData(dataArray);
                audioData = dataArray;
            }

            // Draw connecting lines first (behind circles)
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
            ctx.lineWidth = 1;

            for (let i = 0; i < circles.length - 1; i++) {
                for (let j = i + 1; j < circles.length; j++) {
                    // Skip connections with the center circle
                    if (circles[i].distance === 0 || circles[j].distance === 0) continue;

                    const ci = circles[i];
                    const cj = circles[j];

                    const xi = ci.x + Math.cos(ci.angle) * ci.distance;
                    const yi = ci.y + Math.sin(ci.angle) * ci.distance;
                    const xj = cj.x + Math.cos(cj.angle) * cj.distance;
                    const yj = cj.y + Math.sin(cj.angle) * cj.distance;

                    const dx = xi - xj;
                    const dy = yi - yj;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    // Only draw connections within range
                    if (distance < 200) {
                        const alpha = (1 - distance / 200) * 0.2;
                        ctx.strokeStyle = `rgba(255, 255, 255, ${alpha})`;

                        ctx.beginPath();
                        ctx.moveTo(xi, yi);
                        ctx.lineTo(xj, yj);
                        ctx.stroke();
                    }
                }
            }

            // Update and draw each circle
            circles.forEach((circle, index) => {
                // If audio is playing, update radius based on frequency data
                if (audioData && !audio.paused) {
                    const freqIndex = circle.frequency % audioData.length;
                    const audioValue = audioData[freqIndex] / 255;

                    // Center circle responds to bass frequencies
                    if (circle.distance === 0) {
                        const bassAvg = (audioData[0] + audioData[1] + audioData[2]) / (3 * 255);
                        circle.targetRadius = circle.baseRadius * (1 + bassAvg * 0.5);

                        // Create particles on strong bass beats
                        if (bassAvg > 0.7 && Math.random() > 0.7) {
                            for (let i = 0; i < 8; i++) {
                                const angle = Math.random() * Math.PI * 2;
                                const dist = circle.radius * 0.8;
                                const x = circle.x + Math.cos(angle) * dist;
                                const y = circle.y + Math.sin(angle) * dist;

                                createParticles(x, y, colors[Math.floor(Math.random() * colors.length)], 3);
                            }
                        }
                    } else {
                        circle.targetRadius = circle.baseRadius * (1 + audioValue * 0.8);

                        // Create particles on strong beats for outer circles
                        if (audioValue > 0.8 && Math.random() > 0.8) {
                            const x = circle.x + Math.cos(circle.angle) * circle.distance;
                            const y = circle.y + Math.sin(circle.angle) * circle.distance;
                            createParticles(x, y, circle.color, 2);
                        }
                    }
                } else {
                    // If no audio, animate based on time and phase
                    const time = Date.now() / 1000;
                    const waveValue = Math.sin(time * 2 + circle.phase) * 0.5 + 0.5;
                    circle.targetRadius = circle.baseRadius * (1 + waveValue * 0.3);
                }

                // Smoothly transition to target radius
                circle.radius += (circle.targetRadius - circle.radius) * 0.1;

                // Rotate outer circles slowly
                if (circle.distance > 0) {
                    circle.angle += 0.002 * (index % 2 === 0 ? 1 : -1);
                }

                // Calculate position
                const x = circle.x + Math.cos(circle.angle) * circle.distance;
                const y = circle.y + Math.sin(circle.angle) * circle.distance;

                // Draw circle
                ctx.beginPath();
                ctx.arc(x, y, circle.radius, 0, Math.PI * 2);

                // Fill with gradient
                const gradient = ctx.createRadialGradient(
                    x, y, 0,
                    x, y, circle.radius
                );

                const { r, g, b } = circle.color;
                gradient.addColorStop(0, `rgba(${r}, ${g}, ${b}, ${circle.alpha})`);
                gradient.addColorStop(1, `rgba(${r}, ${g}, ${b}, 0)`);

                ctx.fillStyle = gradient;
                ctx.fill();

                // Add light border
                ctx.strokeStyle = `rgba(${r + 50}, ${g + 50}, ${b + 50}, ${circle.alpha * 0.5})`;
                ctx.stroke();
            });

            // Update and draw particles
            updateParticles();

            // Draw center glow
            const centerCircle = circles[circles.length - 1];
            const glowGradient = ctx.createRadialGradient(
                centerCircle.x, centerCircle.y, 0,
                centerCircle.x, centerCircle.y, centerCircle.radius * 2
            );

            glowGradient.addColorStop(0, 'rgba(100, 100, 255, 0.2)');
            glowGradient.addColorStop(1, 'rgba(100, 100, 255, 0)');

            ctx.fillStyle = glowGradient;
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Continue animation
            animationId = requestAnimationFrame(animate);
        }

        // Event listeners for audio player
        audio.addEventListener('play', () => {
            if (!audioContext) {
                initAudio();
            }
            if (!animationId) {
                animate();
            }
        });

        audio.addEventListener('pause', () => {
            // Keep animation running even when paused
        });

        // Start animation even without audio
        createCircles();
        animate();

        // Initialize audio on first user interaction
        document.addEventListener('click', () => {
            if (!audioContext) {
                initAudio();
            }
        }, { once: true });
    </script>
    </body>
    </html>
<?php
// This function can be used for server-side frame generation if needed
function generateCircleFrame($frame, $width, $height) {
    // Create image
    $img = imagecreatetruecolor($width, $height);

    // Set background
    $bg = imagecolorallocate($img, 10, 10, 25);
    imagefill($img, 0, 0, $bg);

    // Colors for circles
    $colors = [
        ['r' => 41, 'g' => 121, 'b' => 255],   // Blue
        ['r' => 117, 'g' => 80, 'b' => 255],   // Purple
        ['r' => 200, 'g' => 62, 'b' => 255],   // Magenta
        ['r' => 255, 'g' => 83, 'b' => 153],   // Pink
        ['r' => 66, 'g' => 245, 'b' => 239]    // Cyan
    ];

    // Center point
    $centerX = $width / 2;
    $centerY = $height / 2;

    // Draw circles
    $numCircles = 12;

    // Draw connecting lines (for server-side rendering)
    // ...

    // Draw circles (for server-side rendering)
    // ...

    return $img;
}
?>