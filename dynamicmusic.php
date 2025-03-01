<?php
// Enhanced Audio-Reactive Visualization and Dynamic Aesthetics Music Visualizer
// Built with PHP, HTML5 Canvas, and Web Audio API

header('Content-Type: text/html');

// Configuration
$width = 900;  // Increased canvas size for more visual impact
$height = 700;
$background = [20, 15, 40]; // Richer dark purple base
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enhanced Audio-Reactive & Dynamic Aesthetics Visualizer</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                background: radial-gradient(circle, rgba(40, 30, 80, 1), rgba(<?php echo implode(',', $background); ?>, 1));
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                font-family: 'Arial', sans-serif;
                color: #fff;
                flex-direction: column;
                overflow: hidden;
            }

            .container {
                position: relative;
                width: <?php echo $width; ?>px;
                height: <?php echo $height; ?>px;
                filter: drop-shadow(0 0 25px rgba(255, 255, 255, 0.4));
                border-radius: 15px;
                overflow: hidden;
            }

            .audio-controls {
                margin-top: 25px;
                padding: 20px;
                background: linear-gradient(45deg, rgba(255, 100, 255, 0.2), rgba(100, 255, 255, 0.2));
                border-radius: 15px;
                text-align: center;
                backdrop-filter: blur(5px);
            }

            audio {
                width: 100%;
                max-width: 600px;
                filter: hue-rotate(90deg) brightness(1.2);
            }

            h1 {
                font-size: 2.5em;
                text-shadow: 0 0 20px rgba(255, 150, 255, 0.9), 0 0 40px rgba(100, 255, 255, 0.7);
                animation: glowPulse 3s infinite alternate;
            }

            @keyframes glowPulse {
                from { text-shadow: 0 0 20px rgba(255, 150, 255, 0.9), 0 0 40px rgba(100, 255, 255, 0.7); }
                to { text-shadow: 0 0 30px rgba(255, 255, 100, 0.9), 0 0 60px rgba(255, 100, 255, 0.7); }
            }
        </style>
    </head>
    <body>
    <h1>Dynamic Aesthetics Music Visualizer</h1>

    <div class="container">
        <canvas id="visualization" width="<?php echo $width; ?>" height="<?php echo $height; ?>"></canvas>
    </div>

    <div class="audio-controls">
        <h3>Play Your Music</h3>
        <audio id="audio" controls>
            <source src="blue.mp3" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
    </div>

    <script>
        const canvas = document.getElementById('visualization');
        const ctx = canvas.getContext('2d');
        const audio = document.getElementById('audio');

        let audioContext = null;
        let analyser = null;
        let dataArray = null;
        let source = null;

        let animationId = null;
        let circles = [];
        let particles = [];
        let trails = [];

        // Vibrant and varied color palette
        const colors = [
            { r: 255, g: 80, b: 80 },   // Coral Red
            { r: 255, g: 160, b: 0 },  // Amber Orange
            { r: 255, g: 255, b: 80 }, // Lemon Yellow
            { r: 80, g: 255, b: 160 }, // Mint Green
            { r: 80, g: 255, b: 255 }, // Sky Cyan
            { r: 160, g: 80, b: 255 }, // Lavender Purple
            { r: 255, g: 80, b: 255 }, // Magenta Pink
            { r: 255, g: 200, b: 80 }  // Peach
        ];

        function initAudio() {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            analyser = audioContext.createAnalyser();
            source = audioContext.createMediaElementSource(audio);
            source.connect(analyser);
            analyser.connect(audioContext.destination);
            analyser.fftSize = 1024; // Higher resolution for detailed response
            const bufferLength = analyser.frequencyBinCount;
            dataArray = new Uint8Array(bufferLength);
            createCircles();
        }

        function createCircles() {
            circles = [];
            const numCircles = 20; // More circles for complexity

            // Outer ring
            for (let i = 0; i < numCircles; i++) {
                const angle = (i / numCircles) * Math.PI * 2;
                circles.push({
                    x: canvas.width / 2,
                    y: canvas.height / 2,
                    radius: 50,
                    baseRadius: 50,
                    targetRadius: 50,
                    angle: angle,
                    distance: 250,
                    color: colors[i % colors.length],
                    alpha: 0.85,
                    frequency: i * 4,
                    phase: Math.random() * Math.PI * 2,
                    hueShift: Math.random() * 360,
                    speed: 0.005 * (i % 2 === 0 ? 1 : -1)
                });
            }

            // Middle ring with offset
            for (let i = 0; i < numCircles / 2; i++) {
                const angle = (i / (numCircles / 2)) * Math.PI * 2;
                circles.push({
                    x: canvas.width / 2,
                    y: canvas.height / 2,
                    radius: 35,
                    baseRadius: 35,
                    targetRadius: 35,
                    angle: angle,
                    distance: 150,
                    color: colors[(i + 3) % colors.length],
                    alpha: 0.9,
                    frequency: i * 6,
                    phase: Math.random() * Math.PI * 2,
                    hueShift: Math.random() * 360,
                    speed: 0.007 * (i % 2 === 0 ? -1 : 1)
                });
            }

            // Inner ring
            for (let i = 0; i < numCircles / 4; i++) {
                const angle = (i / (numCircles / 4)) * Math.PI * 2;
                circles.push({
                    x: canvas.width / 2,
                    y: canvas.height / 2,
                    radius: 25,
                    baseRadius: 25,
                    targetRadius: 25,
                    angle: angle,
                    distance: 80,
                    color: colors[(i + 5) % colors.length],
                    alpha: 0.95,
                    frequency: i * 8,
                    phase: Math.random() * Math.PI * 2,
                    hueShift: Math.random() * 360,
                    speed: 0.009 * (i % 2 === 0 ? 1 : -1)
                });
            }

            // Center orb
            circles.push({
                x: canvas.width / 2,
                y: canvas.height / 2,
                radius: 140,
                baseRadius: 140,
                targetRadius: 140,
                angle: 0,
                distance: 0,
                color: { r: 255, g: 255, b: 255 },
                alpha: 0.25,
                frequency: 0,
                phase: 0,
                hueShift: 0,
                speed: 0
            });
        }

        function createParticles(x, y, color, count) {
            for (let i = 0; i < count; i++) {
                const angle = Math.random() * Math.PI * 2;
                const speed = 2.5 + Math.random() * 5;
                particles.push({
                    x: x,
                    y: y,
                    radius: 3 + Math.random() * 5,
                    color: color,
                    alpha: 1,
                    speed: speed,
                    angle: angle,
                    life: 2,
                    hueShift: Math.random() * 360
                });
            }
        }

        function createTrail(x, y, color, radius) {
            trails.push({
                x: x,
                y: y,
                radius: radius * 0.8,
                color: color,
                alpha: 0.6,
                life: 0.8
            });
        }

        function updateParticlesAndTrails() {
            // Particles
            for (let i = particles.length - 1; i >= 0; i--) {
                const p = particles[i];
                p.x += Math.cos(p.angle) * p.speed;
                p.y += Math.sin(p.angle) * p.speed;
                p.life -= 0.02;
                p.alpha = p.life;

                if (p.life <= 0) {
                    particles.splice(i, 1);
                    continue;
                }

                const hslColor = `hsla(${p.hueShift + (Date.now() / 50) % 360}, 100%, 60%, ${p.alpha})`;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius * p.life, 0, Math.PI * 2);
                ctx.fillStyle = hslColor;
                ctx.fill();
            }

            // Trails
            for (let i = trails.length - 1; i >= 0; i--) {
                const t = trails[i];
                t.life -= 0.05;
                t.alpha = t.life;
                t.radius *= 0.95;

                if (t.life <= 0) {
                    trails.splice(i, 1);
                    continue;
                }

                const { r, g, b } = t.color;
                ctx.beginPath();
                ctx.arc(t.x, t.y, t.radius, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(${r}, ${g}, ${b}, ${t.alpha * 0.3})`;
                ctx.fill();
            }
        }

        function animate() {
            ctx.fillStyle = 'rgba(<?php echo implode(',', $background); ?>, 0.85)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            let audioData = null;
            if (analyser) {
                analyser.getByteFrequencyData(dataArray);
                audioData = dataArray;
            }

            // Dynamic background with subtle waves
            const time = Date.now() / 1000;
            const bgGradient = ctx.createRadialGradient(
                canvas.width / 2, canvas.height / 2, 0,
                canvas.width / 2, canvas.height / 2, canvas.width / 1.5
            );
            bgGradient.addColorStop(0, `hsla(${(time * 20) % 360}, 60%, 25%, 0.8)`);
            bgGradient.addColorStop(1, `rgba(<?php echo implode(',', $background); ?>, 1)`);
            ctx.fillStyle = bgGradient;
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Connecting lines with dynamic coloring
            for (let i = 0; i < circles.length - 1; i++) {
                for (let j = i + 1; j < circles.length; j++) {
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

                    if (distance < 300) {
                        const alpha = (1 - distance / 300) * 0.4;
                        const hue = ((time * 50 + distance) % 360);
                        ctx.strokeStyle = `hsla(${hue}, 85%, 65%, ${alpha})`;
                        ctx.lineWidth = 2.5;
                        ctx.beginPath();
                        ctx.moveTo(xi, yi);
                        ctx.lineTo(xj, yj);
                        ctx.stroke();
                    }
                }
            }

            circles.forEach((circle, index) => {
                let audioValue = 0;
                if (audioData && !audio.paused) {
                    const freqIndex = circle.frequency % audioData.length;
                    audioValue = audioData[freqIndex] / 255;

                    if (circle.distance === 0) {
                        const bassAvg = (audioData[0] + audioData[1] + audioData[2] + audioData[3]) / (4 * 255);
                        circle.targetRadius = circle.baseRadius * (1 + bassAvg * 0.9);
                        if (bassAvg > 0.65 && Math.random() > 0.5) {
                            for (let i = 0; i < 15; i++) {
                                const angle = Math.random() * Math.PI * 2;
                                const dist = circle.radius * (0.7 + Math.random() * 0.3);
                                const x = circle.x + Math.cos(angle) * dist;
                                const y = circle.y + Math.sin(angle) * dist;
                                createParticles(x, y, colors[Math.floor(Math.random() * colors.length)], 6);
                            }
                        }
                    } else {
                        circle.targetRadius = circle.baseRadius * (1 + audioValue * 1.5);
                        if (audioValue > 0.75 && Math.random() > 0.6) {
                            const x = circle.x + Math.cos(circle.angle) * circle.distance;
                            const y = circle.y + Math.sin(circle.angle) * circle.distance;
                            createParticles(x, y, circle.color, 5);
                            createTrail(x, y, circle.color, circle.radius);
                        }
                    }
                } else {
                    const waveValue = Math.sin(time * 4 + circle.phase) * 0.5 + 0.5;
                    circle.targetRadius = circle.baseRadius * (1 + waveValue * 0.6);
                }

                circle.radius += (circle.targetRadius - circle.radius) * 0.2;
                circle.angle += circle.speed;
                circle.hueShift = (circle.hueShift + 1.5) % 360;

                const x = circle.x + Math.cos(circle.angle) * circle.distance;
                const y = circle.y + Math.sin(circle.angle) * circle.distance;

                // Multi-layer gradient for depth
                const gradient = ctx.createRadialGradient(x, y, 0, x, y, circle.radius * 2);
                const { r, g, b } = circle.color;
                const hslColor = `hsla(${circle.hueShift}, 100%, 65%, ${circle.alpha})`;
                gradient.addColorStop(0, `rgba(${r}, ${g}, ${b}, 1)`);
                gradient.addColorStop(0.3, hslColor);
                gradient.addColorStop(0.7, `hsla(${circle.hueShift + 180}, 80%, 50%, 0.5)`);
                gradient.addColorStop(1, `hsla(${circle.hueShift}, 70%, 30%, 0)`);

                ctx.beginPath();
                ctx.arc(x, y, circle.radius, 0, Math.PI * 2);
                ctx.fillStyle = gradient;
                ctx.fill();

                // Neon border
                ctx.strokeStyle = `hsla(${circle.hueShift}, 100%, 75%, 0.8)`;
                ctx.lineWidth = 4;
                ctx.stroke();
            });

            updateParticlesAndTrails();

            // Central multi-color glow
            const centerCircle = circles[circles.length - 1];
            const glowGradient = ctx.createRadialGradient(
                centerCircle.x, centerCircle.y, 0,
                centerCircle.x, centerCircle.y, centerCircle.radius * 3
            );
            glowGradient.addColorStop(0, `hsla(${(time * 40) % 360}, 80%, 60%, 0.4)`);
            glowGradient.addColorStop(0.5, `hsla(${(time * 40 + 120) % 360}, 70%, 50%, 0.2)`);
            glowGradient.addColorStop(1, `rgba(100, 100, 255, 0)`);
            ctx.fillStyle = glowGradient;
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            animationId = requestAnimationFrame(animate);
        }

        audio.addEventListener('play', () => {
            if (!audioContext) initAudio();
            if (!animationId) animate();
        });

        createCircles();
        animate();

        document.addEventListener('click', () => {
            if (!audioContext) initAudio();
        }, { once: true });
    </script>
    </body>
    </html>
<?php
// Optional server-side frame generation (placeholder)
function generateStaticFrame($width, $height) {
    $img = imagecreatetruecolor($width, $height);
    $bg = imagecolorallocate($img, 20, 15, 40);
    imagefill($img, 0, 0, $bg);
    // Additional server-side rendering logic could be added here
    return $img;
}
?>