<?php
// PHP Music Visualization Animation with Audio Player
// This script generates a visualization and includes an audio player with blue.mp3

// If this is an AJAX request for just the visualization frame
if (isset($_GET['frame'])) {
    generateFrame(intval($_GET['frame']));
    exit;
}

// Otherwise, output the full HTML page with visualization and audio player
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Music Visualization</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                background-color: #0a0a1e;
                color: white;
                font-family: Arial, sans-serif;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
            }
            .container {
                width: 800px;
                margin: 0 auto;
                text-align: center;
            }
            #visualization {
                width: 800px;
                height: 400px;
                background-color: #0a0a1e;
                margin-bottom: 20px;
                border: 1px solid #333;
            }
            .audio-player {
                width: 100%;
                max-width: 800px;
                padding: 20px;
                background-color: rgba(0, 0, 102, 0.5);
                border-radius: 10px;
                margin-top: 20px;
            }
            audio {
                width: 100%;
            }
            h1 {
                color: #4080ff;
                text-shadow: 0 0 10px rgba(0, 128, 255, 0.8);
            }
        </style>
    </head>
    <body>
    <div class="container">
        <h1>Dynamic Music Visualization</h1>

        <canvas id="visualization" width="800" height="400"></canvas>

        <div class="audio-player">
            <h3>Now Playing: As it was.mp3</h3>
            <audio id="audio-player" controls autoplay>
                <source src="AsItWas.mp3" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        </div>
    </div>

    <script>
        // Canvas setup
        const canvas = document.getElementById('visualization');
        const ctx = canvas.getContext('2d');
        const audioPlayer = document.getElementById('audio-player');

        // Animation variables
        let frame = 0;
        let isPlaying = false;
        let animationId;

        // Create an audio context for analysis
        let audioContext;
        let analyser;
        let dataArray;

        // Initialize audio analyzer
        function initAudio() {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            analyser = audioContext.createAnalyser();
            const source = audioContext.createMediaElementSource(audioPlayer);

            // Connect the audio nodes
            source.connect(analyser);
            analyser.connect(audioContext.destination);

            // Set up the analyzer
            analyser.fftSize = 256;
            const bufferLength = analyser.frequencyBinCount;
            dataArray = new Uint8Array(bufferLength);
        }

        // Draw frame based on audio data
        function drawFrame() {
            // Clear the canvas
            ctx.fillStyle = 'rgb(10, 10, 30)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // If audio is analyzed, use real audio data
            if (analyser) {
                // Get frequency data
                analyser.getByteFrequencyData(dataArray);

                const barWidth = canvas.width / dataArray.length;
                const maxBarHeight = canvas.height * 0.8;

                // Draw bars for each frequency
                for (let i = 0; i < dataArray.length; i++) {
                    // Calculate bar height based on audio data
                    const value = dataArray[i] / 255.0;
                    const barHeight = Math.max(5, value * maxBarHeight);

                    // Color based on frequency and volume
                    const hue = i / dataArray.length * 260; // blue to purple to red
                    const saturation = 80 + value * 20;
                    const lightness = 40 + value * 30;

                    ctx.fillStyle = `hsl(${hue}, ${saturation}%, ${lightness}%)`;

                    // Draw the bar
                    const x = i * barWidth;
                    const y = canvas.height - barHeight;
                    ctx.fillRect(x, y, barWidth - 1, barHeight);

                    // Add highlight
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.3)';
                    ctx.fillRect(x, y, barWidth - 1, 2);
                }

                // Add grid lines
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
                ctx.lineWidth = 1;

                // Vertical grid
                for (let i = 0; i < canvas.width; i += canvas.width / 10) {
                    ctx.beginPath();
                    ctx.moveTo(i, 0);
                    ctx.lineTo(i, canvas.height);
                    ctx.stroke();
                }

                // Horizontal grid
                for (let i = canvas.height; i > 0; i -= canvas.height / 8) {
                    ctx.beginPath();
                    ctx.moveTo(0, i);
                    ctx.lineTo(canvas.width, i);
                    ctx.stroke();
                }

                // Add reflection effect
                ctx.save();
                ctx.globalAlpha = 0.2;
                ctx.translate(0, canvas.height);
                ctx.scale(1, -0.3);
                ctx.drawImage(canvas, 0, 0);
                ctx.restore();
            } else {
                // If no audio analysis, use fallback animation
                const bars = 40;
                const barWidth = Math.floor(canvas.width / bars);
                const maxBarHeight = canvas.height * 0.8;

                for (let i = 0; i < bars; i++) {
                    // Calculate the bar height using a sine wave and noise
                    const baseValue = Math.sin((frame + i) * 0.1) * 0.5 + 0.5;
                    let beatEffect = 0;

                    // Add "beats" every 10 frames
                    if (frame % 10 < 3 && i % 4 === 0) {
                        beatEffect = 0.3;
                    }

                    // Add some randomness
                    const noise = (Math.sin(i * 0.5 + frame * 0.2) + 1) * 0.15;

                    // Combine all factors
                    let value = baseValue + beatEffect + noise;
                    value = Math.max(0.1, Math.min(1, value)); // Clamp between 0.1 and 1

                    // Calculate bar height
                    const barHeight = Math.round(value * maxBarHeight);

                    // Determine bar color based on height
                    const hue = i / bars * 260; // blue to purple to red
                    ctx.fillStyle = `hsl(${hue}, 80%, ${50 + value * 30}%)`;

                    // Draw the bar
                    const x = i * barWidth;
                    const y = canvas.height - barHeight;
                    ctx.fillRect(x, y, barWidth - 1, barHeight);

                    // Add highlight
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.3)';
                    ctx.fillRect(x, y, barWidth - 1, 2);
                }
            }

            frame++;
            animationId = requestAnimationFrame(drawFrame);
        }

        // Event listeners for audio player
        audioPlayer.addEventListener('play', () => {
            if (!audioContext) {
                initAudio();
            }

            isPlaying = true;
            if (!animationId) {
                drawFrame();
            }
        });

        audioPlayer.addEventListener('pause', () => {
            isPlaying = false;
            if (animationId) {
                cancelAnimationFrame(animationId);
                animationId = null;
            }
        });

        audioPlayer.addEventListener('ended', () => {
            isPlaying = false;
            if (animationId) {
                cancelAnimationFrame(animationId);
                animationId = null;
            }
        });

        // Start with one frame for initial display
        drawFrame();

        // If audio is set to autoplay, we need to pause animation until it actually starts
        if (!audioPlayer.paused) {
            initAudio();
        } else {
            // Draw a single frame and pause animation until audio plays
            cancelAnimationFrame(animationId);
            animationId = null;
        }
    </script>
    </body>
    </html>

<?php
// Function to generate a single frame for the animation
function generateFrame($frameNumber) {
    // This function can be used for server-side frame generation if needed
    $width = 800;
    $height = 400;

    // Create a new image
    $img = imagecreatetruecolor($width, $height);

    // Fill the background
    $bgColor = imagecolorallocate($img, 10, 10, 30);
    imagefill($img, 0, 0, $bgColor);

    // Generate visualization for the current frame
    // (Similar to the JavaScript version but server-side)

    // Set the content type to PNG
    header('Content-Type: image/png');

    // Output the image
    imagepng($img);
    imagedestroy($img);
}
?>