<?php
// PHP Circle Music Visualization - Enhanced Color Edition

header('Content-Type: text/html');

// Configuration
$width = 800;
$height = 600;
$background = [15, 10, 30]; // Slightly brighter dark purple background
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Colorful Circle Visualization</title>
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
            filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.3));
        }

        .audio-controls {
            margin-top: 20px;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            text-align: center;
        }

        audio {
            width: 100%;
            max-width: 500px;
            filter: hue-rotate(180deg);
        }

        h1 {
            text-shadow: 0 0 15px rgba(255, 100, 255, 0.9);
            animation: titleGlow 2s infinite alternate;
        }

        @keyframes titleGlow {
            from { text-shadow: 0 0 15px rgba(255, 100, 255, 0.9); }
            to { text-shadow: 0 0 25px rgba(100, 255, 255, 0.9); }
        }
    </style>
</head>
<body>
<h1>Enhanced Colorful Visualization</h1>

<div class="container">
    <canvas id="visualization" width="<?php echo $width; ?>" height="<?php echo $height; ?>"></canvas>
</div>

<div class="audio-controls">
    <h3>Audio Player</h3>
    <audio id="audio" controls>
        <source src="Espresso.mp3" type="audio/mpeg">
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

    // Expanded color palette with more vibrant options
    const colors = [
        { r: 255, g: 65, b: 65 },    // Vivid Red
        { r: 255, g: 128, b: 0 },   // Bright Orange
        { r: 255, g: 255, b: 0 },   // Electric Yellow
        { r: 0, g: 255, b: 128 },   // Neon Green
        { r: 0, g: 255, b: 255 },   // Cyan
        { r: 65, g: 128, b: 255 },  // Sky Blue
        { r: 128, g: 0, b: 255 },   // Vibrant Purple
        { r: 255, g: 0, b: 255 }    // Hot Pink
    ];

    function initAudio() {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
        analyser = audioContext.createAnalyser();
        source = audioContext.createMediaElementSource(audio);
        source.connect(analyser);
        analyser.connect(audioContext.destination);
        analyser.fftSize = 512; // Increased for more frequency detail
        const bufferLength = analyser.frequencyBinCount;
        dataArray = new Uint8Array(bufferLength);
        createCircles();
    }

    function createCircles() {
        circles = [];
        const numCircles = 16; // More circles for richer visuals

        // Outer ring
        for (let i = 0; i < numCircles; i++) {
            const angle = (i / numCircles) * Math.PI * 2;
            circles.push({
                x: canvas.width / 2,
                y: canvas.height / 2,
                radius: 60,
                baseRadius: 60,
                targetRadius: 60,
                angle: angle,
                distance: 200,
                color: colors[i % colors.length],
                alpha: 0.8,
                frequency: i * 3,
                phase: Math.random() * Math.PI * 2,
                hueShift: 0
            });
        }

        // Middle ring
        for (let i = 0; i < numCircles / 2; i++) {
            const angle = (i / (numCircles / 2)) * Math.PI * 2;
            circles.push({
                x: canvas.width / 2,
                y: canvas.height / 2,
                radius: 40,
                baseRadius: 40,
                targetRadius: 40,
                angle: angle,
                distance: 100,
                color: colors[(i + 2) % colors.length],
                alpha: 0.9,
                frequency: i * 5,
                phase: Math.random() * Math.PI * 2,
                hueShift: 0
            });
        }

        // Center circle
        circles.push({
            x: canvas.width / 2,
            y: canvas.height / 2,
            radius: 120,
            baseRadius: 120,
            targetRadius: 120,
            angle: 0,
            distance: 0,
            color: { r: 255, g: 255, b: 255 },
            alpha: 0.2,
            frequency: 0,
            phase: 0,
            hueShift: 0
        });
    }

    function createParticles(x, y, color, count) {
        for (let i = 0; i < count; i++) {
            const angle = Math.random() * Math.PI * 2;
            const speed = 2 + Math.random() * 4;

            particles.push({
                x: x,
                y: y,
                radius: 2 + Math.random() * 4,
                color: color,
                alpha: 1,
                speed: speed,
                angle: angle,
                life: 1.5,
                hueShift: Math.random() * 360
            });
        }
    }

    function updateParticles() {
        for (let i = particles.length - 1; i >= 0; i--) {
            const p = particles[i];
            p.x += Math.cos(p.angle) * p.speed;
            p.y += Math.sin(p.angle) * p.speed;
            p.life -= 0.015;
            p.alpha = p.life;

            if (p.life <= 0) {
                particles.splice(i, 1);
                continue;
            }

            const { r, g, b } = p.color;
            const hueRotated = `hsl(${p.hueShift}, 100%, 50%)`;
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius * p.life, 0, Math.PI * 2);
            ctx.fillStyle = hueRotated;
            ctx.fill();
        }
    }

    function animate() {
        ctx.fillStyle = 'rgba(<?php echo implode(',', $background); ?>, 0.9)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        let audioData = null;
        if (analyser) {
            analyser.getByteFrequencyData(dataArray);
            audioData = dataArray;
        }

        // Dynamic background gradient
        const bgGradient = ctx.createRadialGradient(
            canvas.width / 2, canvas.height / 2, 0,
            canvas.width / 2, canvas.height / 2, canvas.width / 2
        );
        const time = Date.now() / 1000;
        bgGradient.addColorStop(0, `hsl(${time % 360}, 50%, 20%)`);
        bgGradient.addColorStop(1, `rgba(<?php echo implode(',', $background); ?>, 1)`);
        ctx.fillStyle = bgGradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Connecting lines with color
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

                if (distance < 250) {
                    const alpha = (1 - distance / 250) * 0.3;
                    const hue = (distance / 250) * 360;
                    ctx.strokeStyle = `hsla(${hue}, 80%, 70%, ${alpha})`;
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.moveTo(xi, yi);
                    ctx.lineTo(xj, yj);
                    ctx.stroke();
                }
            }
        }

        circles.forEach((circle, index) => {
            if (audioData && !audio.paused) {
                const freqIndex = circle.frequency % audioData.length;
                const audioValue = audioData[freqIndex] / 255;

                if (circle.distance === 0) {
                    const bassAvg = (audioData[0] + audioData[1] + audioData[2]) / (3 * 255);
                    circle.targetRadius = circle.baseRadius * (1 + bassAvg * 0.7);
                    if (bassAvg > 0.6 && Math.random() > 0.6) {
                        for (let i = 0; i < 12; i++) {
                            const angle = Math.random() * Math.PI * 2;
                            const dist = circle.radius * 0.9;
                            const x = circle.x + Math.cos(angle) * dist;
                            const y = circle.y + Math.sin(angle) * dist;
                            createParticles(x, y, colors[Math.floor(Math.random() * colors.length)], 5);
                        }
                    }
                } else {
                    circle.targetRadius = circle.baseRadius * (1 + audioValue * 1.2);
                    if (audioValue > 0.7 && Math.random() > 0.7) {
                        const x = circle.x + Math.cos(circle.angle) * circle.distance;
                        const y = circle.y + Math.sin(circle.angle) * circle.distance;
                        createParticles(x, y, circle.color, 4);
                    }
                }
            } else {
                const waveValue = Math.sin(time * 3 + circle.phase) * 0.5 + 0.5;
                circle.targetRadius = circle.baseRadius * (1 + waveValue * 0.5);
            }

            circle.radius += (circle.targetRadius - circle.radius) * 0.15;
            if (circle.distance > 0) {
                circle.angle += 0.004 * (index % 2 === 0 ? 1 : -1);
            }

            // Hue shifting for extra vibrancy
            circle.hueShift = (circle.hueShift + 1) % 360;

            const x = circle.x + Math.cos(circle.angle) * circle.distance;
            const y = circle.y + Math.sin(circle.angle) * circle.distance;

            // Enhanced gradient
            const gradient = ctx.createRadialGradient(x, y, 0, x, y, circle.radius * 1.5);
            const { r, g, b } = circle.color;
            const hslColor = `hsl(${circle.hueShift}, 100%, 60%)`;
            gradient.addColorStop(0, hslColor);
            gradient.addColorStop(0.5, `rgba(${r}, ${g}, ${b}, ${circle.alpha})`);
            gradient.addColorStop(1, `hsla(${circle.hueShift}, 80%, 20%, 0)`);

            ctx.beginPath();
            ctx.arc(x, y, circle.radius, 0, Math.PI * 2);
            ctx.fillStyle = gradient;
            ctx.fill();

            // Glowing border
            ctx.strokeStyle = `hsla(${circle.hueShift}, 100%, 70%, 0.7)`;
            ctx.lineWidth = 3;
            ctx.stroke();
        });

        updateParticles();

        // Enhanced center glow with color rotation
        const centerCircle = circles[circles.length - 1];
        const glowGradient = ctx.createRadialGradient(
            centerCircle.x, centerCircle.y, 0,
            centerCircle.x, centerCircle.y, centerCircle.radius * 2.5
        );
        glowGradient.addColorStop(0, `hsla(${(time * 30) % 360}, 70%, 50%, 0.3)`);
        glowGradient.addColorStop(1, 'rgba(100, 100, 255, 0)');
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