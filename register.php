<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: user_home.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ignisense: Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@300;400;500;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Chakra Petch', 'sans-serif'],
                    },
                    colors: {
                        danger: {
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            900: '#7f1d1d',
                            950: '#450a0a',
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'fade-in': 'fadeIn 0.5s ease-out forwards',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            background-color: #020101; 
            color: #e2e8f0; 
            font-family: 'Inter', sans-serif;
            opacity: 0; 
            animation: fadeIn 0.8s ease-out forwards; 
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Page Transition Class */
        .page-transition {
            opacity: 0;
            transform: translateY(20px);
            animation: pageEnter 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes pageEnter {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        
        /* Scanline Overlay */
        .scanlines {
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,0) 50%, rgba(0,0,0,0.1) 50%, rgba(0,0,0,0.1));
            background-size: 100% 4px;
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none; z-index: 50; opacity: 0.3;
        }

        .input-group:focus-within label { color: #ef4444; }
        .input-group:focus-within i { color: #ef4444; }
    </style>
</head>
<body class="h-screen w-full overflow-hidden flex flex-col md:flex-row">

    <div class="scanlines"></div>

    <!-- LEFT SIDE: Branding & Logo -->
    <div class="relative w-full md:w-1/2 lg:w-[55%] h-32 md:h-full bg-neutral-950 overflow-hidden flex flex-col items-center justify-center p-8 border-b md:border-b-0 md:border-r border-red-900/30 page-transition" style="animation-delay: 0.1s;">
        <!-- Background Effects -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-red-900/20 via-black to-black"></div>
        <canvas id="bg-canvas" class="absolute top-0 left-0 w-full h-full opacity-40"></canvas>
        
        <!-- Content Container -->
        <div class="relative z-10 flex flex-col items-center text-center">
            
            <!-- MAIN LOGO (Left) -->
            <div class="w-32 h-32 md:w-48 md:h-48 mb-6 relative group cursor-default">
                <!-- Outer Ring -->
                <div class="absolute inset-0 rounded-full border-2 border-red-600/30 border-dashed animate-[spin_10s_linear_infinite]"></div>
                <!-- Inner Glow -->
                <div class="absolute inset-4 rounded-full bg-red-600/10 blur-xl group-hover:bg-red-600/20 transition-all duration-500"></div>
                <!-- Logo Circle -->
                <div class="absolute inset-2 bg-gradient-to-br from-neutral-900 to-black rounded-full border border-red-500/50 flex items-center justify-center shadow-[0_0_30px_rgba(220,38,38,0.3)]">
                    <i data-lucide="flame" class="w-16 h-16 md:w-24 md:h-24 text-red-600 drop-shadow-[0_0_10px_rgba(220,38,38,0.8)]"></i>
                </div>
            </div>

            <h1 class="text-3xl md:text-5xl font-display font-bold text-white tracking-wider mb-2">IGNISENSE</h1>
            <p class="text-red-500/80 font-mono text-xs md:text-sm tracking-[0.3em] uppercase">Fire Command & Control</p>
        </div>

        <!-- Footer Text (Desktop) -->
        <div class="absolute bottom-8 text-center hidden md:block">
            <p class="text-[10px] text-gray-600 font-mono">AUTHORIZED PERSONNEL ONLY • SECURE CONNECTION</p>
        </div>
    </div>

    <!-- RIGHT SIDE: Register Form -->
    <div class="w-full md:w-1/2 lg:w-[45%] h-full bg-[#050202] flex items-center justify-center p-6 relative overflow-y-auto page-transition" style="animation-delay: 0.2s;">
        <!-- Corner Accents -->
        <div class="absolute top-0 right-0 p-8 opacity-20 hidden md:block">
            <i data-lucide="shield-plus" class="w-24 h-24 text-red-900"></i>
        </div>

        <div class="w-full max-w-sm z-20 py-8">
            
            <!-- NEW LOGO PLACEHOLDER (Right/Form Side) -->
            <div class="flex justify-center mb-8">
                <div class="relative w-24 h-24 group">
                    <div class="absolute inset-0 bg-red-600/20 blur-xl rounded-full opacity-50 group-hover:opacity-80 transition-opacity"></div>
                    <!-- Image Tag Placeholder -->
                    <img src="img/tccc.png" 
                         alt="Company Logo" 
                         class="relative w-full h-full object-contain rounded-xl border border-red-500/30 bg-black/50 p-1 hover:border-red-500/60 transition-all shadow-lg shadow-red-900/10">
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-display font-semibold text-white mb-2 flex items-center gap-2">
                    <span class="w-2 h-6 bg-red-600 rounded-sm"></span>
                    New Operative
                </h2>
                <p class="text-gray-500 text-sm">Create your credentials for system access.</p>
            </div>

            <div id="error-box" class="hidden bg-red-950/30 border-l-4 border-red-600 p-4 mb-6 backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-circle" class="text-red-500 w-5 h-5"></i>
                    <p class="text-red-200 text-sm font-medium" id="error-msg">Registration Failed</p>
                </div>
            </div>

            <form id="register-form" class="space-y-5">
                <input type="hidden" name="action" value="register">
                
                <div class="input-group space-y-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider transition-colors">Full Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="user" class="h-5 w-5 text-gray-600 transition-colors"></i>
                        </div>
                        <input type="text" name="name" required 
                            class="w-full bg-neutral-900/50 text-white border border-gray-800 rounded-lg pl-10 pr-4 py-3 focus:outline-none focus:border-red-600 focus:ring-1 focus:ring-red-600/50 transition-all placeholder-gray-700 font-mono text-sm" 
                            placeholder="OPERATIVE NAME">
                    </div>
                </div>

                <div class="input-group space-y-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider transition-colors">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="h-5 w-5 text-gray-600 transition-colors"></i>
                        </div>
                        <input type="email" name="email" required 
                            class="w-full bg-neutral-900/50 text-white border border-gray-800 rounded-lg pl-10 pr-4 py-3 focus:outline-none focus:border-red-600 focus:ring-1 focus:ring-red-600/50 transition-all placeholder-gray-700 font-mono text-sm" 
                            placeholder="name@ignisense.com">
                    </div>
                </div>

                <div class="input-group space-y-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider transition-colors">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="h-5 w-5 text-gray-600 transition-colors"></i>
                        </div>
                        <input type="password" name="password" id="password" required 
                            class="w-full bg-neutral-900/50 text-white border border-gray-800 rounded-lg pl-10 pr-4 py-3 focus:outline-none focus:border-red-600 focus:ring-1 focus:ring-red-600/50 transition-all placeholder-gray-700 font-mono text-sm" 
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="input-group space-y-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider transition-colors">Confirm Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="check-circle" class="h-5 w-5 text-gray-600 transition-colors"></i>
                        </div>
                        <input type="password" name="confirm_password" id="confirm_password" required 
                            class="w-full bg-neutral-900/50 text-white border border-gray-800 rounded-lg pl-10 pr-4 py-3 focus:outline-none focus:border-red-600 focus:ring-1 focus:ring-red-600/50 transition-all placeholder-gray-700 font-mono text-sm" 
                            placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" id="reg-btn" class="w-full group relative overflow-hidden bg-red-700 hover:bg-red-600 text-white font-display font-bold py-4 px-4 rounded-lg transition-all shadow-[0_0_20px_rgba(185,28,28,0.2)] hover:shadow-[0_0_30px_rgba(220,38,38,0.4)] mt-2">
                    <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                    <span class="flex items-center justify-center gap-2 tracking-wide uppercase">
                        Create Account <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </span>
                </button>

                <div class="text-center pt-4 border-t border-white/5">
                    <p class="text-gray-500 text-sm">Already authorized? <a href="index.php" class="text-red-400 hover:text-red-300 font-semibold transition-colors">System Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        // --- Form Handling ---
        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('reg-btn');
            const errorBox = document.getElementById('error-box');
            const errorMsg = document.getElementById('error-msg');
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Password Match Validation
            if (password !== confirmPassword) {
                errorMsg.textContent = "Passwords do not match.";
                errorBox.classList.remove('hidden');
                errorBox.classList.add('animate-pulse');
                setTimeout(() => errorBox.classList.remove('animate-pulse'), 500);
                return;
            }
            
            // Loading State
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="animate-pulse tracking-widest">PROCESSING...</span>'; 
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');

            const formData = new FormData(form);

            try {
                // REAL FETCH CALL to api_auth.php
                const res = await fetch('api_auth.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.success) {
                    // Success Redirect
                    btn.innerHTML = '<span class="text-green-400 tracking-widest">CREATED</span>';
                    alert("Account created successfully! Please login.");
                    
                    // Simple page transition
                    document.body.style.opacity = '0';
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 500);
                } else {
                    // Show Error
                    errorMsg.textContent = data.message || "Registration failed.";
                    errorBox.classList.remove('hidden');
                    // Shake animation
                    errorBox.classList.add('animate-pulse');
                    setTimeout(() => errorBox.classList.remove('animate-pulse'), 500);
                    
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                    btn.classList.remove('opacity-75', 'cursor-not-allowed');
                }
            } catch (err) {
                console.error(err);
                errorMsg.textContent = "Connection Error: Is the server running?";
                errorBox.classList.remove('hidden');
                
                btn.innerHTML = originalContent;
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        });

        // --- Background Animation (Left Panel) ---
        const canvas = document.getElementById('bg-canvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            let particles = [];
            
            function resize() { 
                canvas.width = canvas.parentElement.offsetWidth; 
                canvas.height = canvas.parentElement.offsetHeight; 
            }
            window.addEventListener('resize', resize);
            resize();
            
            for(let i=0; i<40; i++) {
                particles.push({
                    x: Math.random()*canvas.width, 
                    y: Math.random()*canvas.height, 
                    vx: (Math.random()-0.5) * 0.5, 
                    vy: (Math.random()-0.5) * 0.5, 
                    r: Math.random()*2
                });
            }
            
            function animate() {
                ctx.clearRect(0,0,canvas.width,canvas.height);
                particles.forEach(p => {
                    p.x+=p.vx; p.y+=p.vy;
                    if(p.x<0||p.x>canvas.width) p.vx*=-1; 
                    if(p.y<0||p.y>canvas.height) p.vy*=-1;
                    ctx.beginPath(); 
                    ctx.arc(p.x,p.y,p.r,0,Math.PI*2); 
                    ctx.fillStyle='rgba(220, 38, 38, 0.4)'; // Red particles
                    ctx.fill();
                });
                requestAnimationFrame(animate);
            }
            animate();
        }
    </script>
</body>
</html>