<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ignisense: Fire Command Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@300;400;500;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
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
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                            950: '#450a0a',
                        }
                    },
                    boxShadow: {
                        'glow': '0 0 20px rgba(220, 38, 38, 0.5)',
                        'glow-sm': '0 0 10px rgba(220, 38, 38, 0.3)',
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            background-color: #050202; 
            background-image: 
                radial-gradient(circle at 50% 0%, #2a0a0a 0%, transparent 50%),
                radial-gradient(circle at 80% 10%, #1f0505 0%, transparent 30%);
            color: #e2e8f0; 
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f0505; }
        ::-webkit-scrollbar-thumb { background: #450a0a; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #7f1d1d; }

        .map-container { 
            height: 600px; 
            width: 100%; 
            border-radius: 0.75rem; 
            border: 1px solid rgba(220, 38, 38, 0.3);
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
            z-index: 10;
        }

        /* Leaflet Dark Mode Filter */
        .leaflet-layer,
        .leaflet-control-zoom-in,
        .leaflet-control-zoom-out,
        .leaflet-control-attribution {
            filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
        }
        
        /* CRT Scanline Effect (Optional subtle overlay) */
        .scanlines {
            background: linear-gradient(
                to bottom,
                rgba(255,255,255,0),
                rgba(255,255,255,0) 50%,
                rgba(0,0,0,0.1) 50%,
                rgba(0,0,0,0.1)
            );
            background-size: 100% 4px;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            pointer-events: none;
            z-index: 9999;
            opacity: 0.2;
        }

        .glass-panel {
            background: rgba(10, 5, 5, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* New styles for added sections to match Command Center theme */
        .feature-card {
            background: rgba(20, 10, 10, 0.6);
            border: 1px solid rgba(220, 38, 38, 0.1);
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            border-color: rgba(220, 38, 38, 0.4);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(220, 38, 38, 0.1);
        }

        /* Search Results Styles */
        #search-results {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background-color: rgba(10, 5, 5, 0.95);
            border: 1px solid rgba(220, 38, 38, 0.3);
            border-radius: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            margin-top: 0.25rem;
            backdrop-filter: blur(10px);
        }
        .search-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: background-color 0.2s;
            font-size: 0.875rem;
            color: #d1d5db;
        }
        .search-item:last-child {
            border-bottom: none;
        }
        .search-item:hover {
            background-color: rgba(220, 38, 38, 0.2);
            color: white;
        }
    </style>
</head>
<body class="antialiased font-sans selection:bg-red-900 selection:text-white">

    <div class="scanlines"></div>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 glass-panel border-b border-red-900/30">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="#" class="text-2xl font-display font-bold text-white flex items-center gap-3 tracking-wider group">
                <div class="relative">
                    <div class="absolute inset-0 bg-red-600 blur-lg opacity-20 group-hover:opacity-40 transition-opacity"></div>
                    <i data-lucide="flame" class="text-red-500 relative z-10 w-8 h-8"></i>
                </div>
                IGNISENSE <span class="text-xs px-2 py-0.5 rounded bg-red-900/50 text-red-200 border border-red-800/50 font-sans font-normal tracking-normal">TANAUAN SECTOR</span>
            </a>
            <div class="flex items-center gap-6">
                <!-- PHP: Replace 'Guest' with <?php echo htmlspecialchars($_SESSION['user_name']); ?> -->
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-gray-400 text-xs uppercase tracking-widest">Operator</span>
                    <span class="text-white font-semibold text-sm"><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Guest User'; ?></span> 
                </div>
                <div class="h-8 w-[1px] bg-white/10 hidden md:block"></div>
                <button onclick="confirmLogout()" class="group flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
                    <i data-lucide="log-out" class="w-5 h-5 group-hover:text-red-500 transition-colors"></i>
                </button>
            </div>
        </div>
    </header>

    <main class="pt-28 pb-12 px-4 md:px-6">
        <section class="container mx-auto max-w-7xl">
            
            <!-- Dashboard Header -->
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-display font-bold text-white mb-2 flex items-center gap-3">
                        <span class="w-3 h-8 bg-red-600 rounded-sm inline-block shadow-glow"></span>
                        Status Overview
                    </h1>
                    <p class="text-gray-500">Live monitoring of fire incidents within Tanauan City jurisdiction.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-600"></span>
                    </span>
                    <span class="text-red-500 font-mono text-sm tracking-wider">SYSTEM ONLINE</span>
                </div>
            </div>

            <!-- Original Grid: Map & Report -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-24">
                
                <!-- Map Section -->
                <div class="lg:col-span-8 space-y-4">
                    <!-- Search Bar Overlay -->
                    <div class="relative z-20 mb-2">
                        <div class="flex items-center bg-neutral-900 border border-red-900/30 rounded-lg shadow-lg">
                            <i data-lucide="search" class="ml-3 text-gray-400 w-5 h-5"></i>
                            <input type="text" id="map-search" placeholder="Search location in Tanauan..." 
                                class="w-full bg-transparent text-white px-4 py-3 focus:outline-none placeholder-gray-500 text-sm font-mono"
                                autocomplete="off">
                            <button id="search-btn" class="bg-red-900/30 hover:bg-red-900/50 text-red-400 px-4 py-2 m-1 rounded transition-colors text-xs font-bold uppercase tracking-wider">
                                Locate
                            </button>
                        </div>
                        <div id="search-results" class="hidden"></div>
                    </div>

                    <div class="relative group">
                        <!-- Corner accents -->
                        <div class="absolute -top-1 -left-1 w-4 h-4 border-t-2 border-l-2 border-red-600 z-20"></div>
                        <div class="absolute -top-1 -right-1 w-4 h-4 border-t-2 border-r-2 border-red-600 z-20"></div>
                        <div class="absolute -bottom-1 -left-1 w-4 h-4 border-b-2 border-l-2 border-red-600 z-20"></div>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 border-b-2 border-r-2 border-red-600 z-20"></div>
                        
                        <div id="main-map" class="map-container bg-neutral-900"></div>
                        
                        <!-- Map Overlay Info -->
                        <div class="absolute bottom-4 left-4 z-[400] bg-black/80 backdrop-blur-md px-4 py-2 border-l-4 border-red-600 rounded-r text-xs font-mono text-gray-300">
                            COORD: TANAUAN_SEC_01
                        </div>
                    </div>
                </div>
                
                <!-- Controls & Reporting -->
                <div class="lg:col-span-4 flex flex-col gap-6">
                    
                    <!-- Report Card -->
                    <div class="bg-gradient-to-b from-neutral-900 to-black p-1 rounded-2xl border border-neutral-800 shadow-xl">
                        <div class="bg-[#0A0505] rounded-xl p-6 h-full relative overflow-hidden">
                            <!-- Background accent -->
                            <div class="absolute -right-10 -top-10 w-32 h-32 bg-red-900/20 blur-3xl rounded-full pointer-events-none"></div>

                            <h2 class="text-xl font-display font-bold text-white mb-6 flex items-center gap-2 border-b border-white/5 pb-4">
                                <i data-lucide="siren" class="text-red-500 animate-pulse"></i> 
                                Emergency Report
                            </h2>
                            
                            <p class="text-gray-400 text-sm mb-6 leading-relaxed">
                                1. Locate the incident on the map.<br>
                                2. Select Fire Intensity.<br>
                                3. Confirm to alert authorities.
                            </p>
                            
                            <div class="space-y-6">
                                <div class="bg-neutral-900/50 p-4 rounded-lg border border-red-900/20">
                                    <div class="flex justify-between items-center mb-2">
                                        <p class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">Target Location</p>
                                        <i data-lucide="crosshair" class="w-4 h-4 text-red-600"></i>
                                    </div>
                                    <p id="lat-lng-display" class="text-white font-mono text-lg tracking-tight">- - . - - , - - . - -</p>
                                </div>

                                <!-- Fire Intensity Selector -->
                                <div class="bg-neutral-900/50 p-4 rounded-lg border border-red-900/20">
                                    <p class="text-[10px] text-gray-500 uppercase tracking-widest font-bold mb-3">Fire Intensity Level</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button onclick="selectIntensity('low')" id="intensity-low" class="intensity-btn py-2 rounded text-xs font-bold border border-green-900/30 bg-green-900/10 text-green-500 hover:bg-green-900/30 transition-all">LOW</button>
                                        <button onclick="selectIntensity('medium')" id="intensity-medium" class="intensity-btn py-2 rounded text-xs font-bold border border-orange-900/30 bg-orange-900/10 text-orange-500 hover:bg-orange-900/30 transition-all">MEDIUM</button>
                                        <button onclick="selectIntensity('high')" id="intensity-high" class="intensity-btn py-2 rounded text-xs font-bold border border-red-900/30 bg-red-900/10 text-red-500 hover:bg-red-900/30 transition-all">HIGH</button>
                                    </div>
                                    <input type="hidden" id="selected-intensity" value="">
                                </div>

                                <button id="submit-report-btn" disabled class="group relative w-full overflow-hidden rounded-xl bg-red-700 p-4 transition-all hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <div class="absolute inset-0 w-3 bg-white transition-all duration-[250ms] ease-out group-hover:w-full opacity-5"></div>
                                    <span class="relative flex items-center justify-center gap-2 text-sm font-bold uppercase tracking-wider text-white">
                                        <i data-lucide="send" class="w-4 h-4"></i>
                                        Transmit Alert
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics / Info (Optional) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-neutral-900/80 border border-neutral-800 p-4 rounded-xl text-center">
                            <p class="text-2xl font-display font-bold text-white">Active</p>
                            <p class="text-xs text-gray-500 uppercase mt-1">Status</p>
                        </div>
                        <div class="bg-neutral-900/80 border border-neutral-800 p-4 rounded-xl text-center">
                            <p class="text-2xl font-display font-bold text-red-500">High</p>
                            <p class="text-xs text-gray-500 uppercase mt-1">Priority</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- NEW SECTIONS ADDED FROM OTHER CODE (Styled to Match) -->
            <div class="border-t border-red-900/20 pt-16">
                
                <!-- Intro / About Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-20">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-display font-bold text-white mb-4 flex items-center gap-3">
                            <span class="w-2 h-6 bg-red-600 rounded-sm"></span>
                            System Overview
                        </h2>
                        <p class="text-gray-400 leading-relaxed mb-6">
                            Ignisense leverages photonic-based early detection to revolutionize fire safety in Tanauan City. By analyzing light spectra at the moment of ignition, we provide instantaneous alerts to users and authorities, drastically reducing response times.
                        </p>
                        <p class="text-gray-500 text-sm">
                            Mission: To enhance public safety through affordable, reliable, and high-speed detection technology integrated seamlessly with local emergency services.
                        </p>
                    </div>
                    <div class="bg-neutral-900/50 rounded-xl border border-red-900/20 p-6 flex flex-col justify-center">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="bg-red-500/10 p-3 rounded-lg"><i data-lucide="zap" class="w-6 h-6 text-red-500"></i></div>
                            <div>
                                <h3 class="font-display font-bold text-white">Photonic Detection</h3>
                                <p class="text-sm text-gray-500">Analyzes light signatures instantly.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="bg-red-500/10 p-3 rounded-lg"><i data-lucide="shield-check" class="w-6 h-6 text-red-500"></i></div>
                            <div>
                                <h3 class="font-display font-bold text-white">False Alarm Filtration</h3>
                                <p class="text-sm text-gray-500">Ignores non-fire triggers like dust or steam.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features Section -->
                <div class="mb-20">
                    <div class="text-center mb-10">
                        <h2 class="text-2xl md:text-3xl font-display font-bold text-white mb-2">Core Capabilities</h2>
                        <p class="text-gray-500">Advanced component breakdown.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Feature 1 -->
                        <div class="feature-card p-6 rounded-xl text-center">
                            <div class="inline-block bg-neutral-900 p-4 rounded-full mb-4 border border-red-900/30 shadow-[0_0_15px_rgba(220,38,38,0.1)]">
                                <i data-lucide="scan-eye" class="w-8 h-8 text-red-500"></i>
                            </div>
                            <h3 class="text-lg font-display font-bold text-white mb-2">Multi-Spectral</h3>
                            <p class="text-xs text-gray-400">Detects unique light signatures of flames with high precision.</p>
                        </div>
                        <!-- Feature 2 -->
                        <div class="feature-card p-6 rounded-xl text-center">
                            <div class="inline-block bg-neutral-900 p-4 rounded-full mb-4 border border-red-900/30 shadow-[0_0_15px_rgba(220,38,38,0.1)]">
                                <i data-lucide="cpu" class="w-8 h-8 text-red-500"></i>
                            </div>
                            <h3 class="text-lg font-display font-bold text-white mb-2">Smart Analysis</h3>
                            <p class="text-xs text-gray-400">Onboard microcontroller processes data in milliseconds.</p>
                        </div>
                        <!-- Feature 3 -->
                        <div class="feature-card p-6 rounded-xl text-center">
                            <div class="inline-block bg-neutral-900 p-4 rounded-full mb-4 border border-red-900/30 shadow-[0_0_15px_rgba(220,38,38,0.1)]">
                                <i data-lucide="radio" class="w-8 h-8 text-red-500"></i>
                            </div>
                            <h3 class="text-lg font-display font-bold text-white mb-2">Instant Alert</h3>
                            <p class="text-xs text-gray-400">Real-time notification to command center and owners.</p>
                        </div>
                        <!-- Feature 4 -->
                        <div class="feature-card p-6 rounded-xl text-center">
                            <div class="inline-block bg-neutral-900 p-4 rounded-full mb-4 border border-red-900/30 shadow-[0_0_15px_rgba(220,38,38,0.1)]">
                                <i data-lucide="globe" class="w-8 h-8 text-red-500"></i>
                            </div>
                            <h3 class="text-lg font-display font-bold text-white mb-2">GPS Pinpoint</h3>
                            <p class="text-xs text-gray-400">Exact coordinates provided to first responders.</p>
                        </div>
                    </div>
                </div>

                <!-- Emergency Hotlines -->
                <div class="mb-12">
                    <div class="bg-gradient-to-r from-red-900/20 via-neutral-900 to-red-900/20 border border-red-900/30 rounded-2xl p-8 relative overflow-hidden">
                        <!-- BG Pattern -->
                        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#dc2626_1px,transparent_1px)] [background-size:16px_16px]"></div>
                        
                        <div class="relative z-10">
                            <h2 class="text-2xl font-display font-bold text-white mb-8 text-center flex items-center justify-center gap-3">
                                <i data-lucide="phone-call" class="text-red-500"></i> Emergency Hotlines (Tanauan)
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="bg-black/40 border border-red-500/20 p-6 rounded-xl text-center hover:border-red-500/50 transition-colors">
                                    <p class="text-gray-500 text-xs uppercase tracking-widest mb-1">Fire Response</p>
                                    <h3 class="text-lg font-bold text-white mb-2">BFP Tanauan</h3>
                                    <p class="text-2xl font-mono text-red-500 font-bold">(043) 778-2111</p>
                                </div>
                                <div class="bg-black/40 border border-blue-500/20 p-6 rounded-xl text-center hover:border-blue-500/50 transition-colors">
                                    <p class="text-gray-500 text-xs uppercase tracking-widest mb-1">Police Assistance</p>
                                    <h3 class="text-lg font-bold text-white mb-2">PNP Tanauan</h3>
                                    <p class="text-2xl font-mono text-blue-400 font-bold">(043) 778-1166</p>
                                </div>
                                <div class="bg-black/40 border border-green-500/20 p-6 rounded-xl text-center hover:border-green-500/50 transition-colors">
                                    <p class="text-gray-500 text-xs uppercase tracking-widest mb-1">Disaster / Medical</p>
                                    <h3 class="text-lg font-bold text-white mb-2">CDRRMO</h3>
                                    <p class="text-2xl font-mono text-green-500 font-bold">(043) 706-7222</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </section>

        <!-- Footer -->
        <footer class="mt-12 border-t border-red-900/20 pt-8 text-center">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-gray-600 text-xs font-mono">
                <p>&copy; 2026 Ignisense Systems. Secure Access Terminal.</p>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-red-500 transition-colors">Privacy Protocol</a>
                    <a href="#" class="hover:text-red-500 transition-colors">System Status</a>
                    <a href="#" class="hover:text-red-500 transition-colors">Contact Admin</a>
                </div>
            </div>
        </footer>
    </main>

    <!-- GENERIC MODAL -->
    <div id="custom-modal" class="fixed inset-0 bg-black/90 backdrop-blur-md hidden z-[10000] flex items-center justify-center">
        <div id="modal-content" class="bg-[#0f0505] p-1 rounded-2xl border border-red-900/50 max-w-sm w-full shadow-[0_0_50px_rgba(220,38,38,0.2)] relative overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
            <!-- Alert Stripe -->
            <div id="modal-stripe" class="h-1 w-full bg-gradient-to-r from-red-600 via-orange-500 to-red-600"></div>
            
            <div class="p-8 text-center">
                <!-- Dynamic Icon -->
                <div id="modal-icon-container" class="w-16 h-16 rounded-full bg-red-900/20 flex items-center justify-center mb-6 mx-auto border border-red-900/50">
                    <i data-lucide="alert-triangle" class="text-red-500 w-8 h-8"></i>
                </div>
                
                <h3 id="modal-title" class="text-xl font-display font-bold text-white mb-2 tracking-wide">TITLE</h3>
                <p id="modal-message" class="text-gray-400 mb-8 text-sm leading-relaxed">Message goes here.</p>
                
                <!-- Dynamic Buttons -->
                <div id="modal-actions" class="grid grid-cols-2 gap-3">
                    <!-- Injected by JS -->
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        let map, reportMarker;

        // --- MODAL SYSTEM ---
        const modal = document.getElementById('custom-modal');
        const modalContent = document.getElementById('modal-content');
        
        function showModal({ title, message, type, onConfirm }) {
            const iconContainer = document.getElementById('modal-icon-container');
            const stripe = document.getElementById('modal-stripe');
            
            // Set Text
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-message').textContent = message;
            
            // Styles based on Type
            if (type === 'success') {
                iconContainer.innerHTML = `<i data-lucide="check" class="text-green-500 w-8 h-8"></i>`;
                iconContainer.className = "w-16 h-16 rounded-full bg-green-900/20 flex items-center justify-center mb-6 mx-auto border border-green-900/50";
                stripe.className = "h-1 w-full bg-gradient-to-r from-green-600 via-emerald-500 to-green-600";
            } else {
                // Danger/Default
                iconContainer.innerHTML = `<i data-lucide="alert-triangle" class="text-red-500 w-8 h-8"></i>`;
                iconContainer.className = "w-16 h-16 rounded-full bg-red-900/20 flex items-center justify-center mb-6 mx-auto border border-red-900/50";
                stripe.className = "h-1 w-full bg-gradient-to-r from-red-600 via-orange-500 to-red-600";
            }

            // Actions
            const actions = document.getElementById('modal-actions');
            actions.innerHTML = ''; // clear

            if (type === 'success') {
                actions.className = "flex justify-center";
                const btn = document.createElement('button');
                btn.className = "w-full px-4 py-3 bg-green-700 hover:bg-green-600 text-white rounded-lg font-bold shadow-lg shadow-green-900/30 transition-all text-sm tracking-wide";
                btn.textContent = "ACKNOWLEDGE";
                btn.onclick = closeModal;
                actions.appendChild(btn);
            } else {
                actions.className = "grid grid-cols-2 gap-3";
                
                const cancelBtn = document.createElement('button');
                cancelBtn.className = "px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-all text-sm font-medium border border-transparent";
                cancelBtn.textContent = "CANCEL";
                cancelBtn.onclick = closeModal;
                
                const confirmBtn = document.createElement('button');
                confirmBtn.className = "px-4 py-3 bg-red-700 hover:bg-red-600 text-white rounded-lg font-bold shadow-lg shadow-red-900/30 transition-all text-sm tracking-wide border border-red-500/50";
                confirmBtn.textContent = "CONFIRM";
                confirmBtn.onclick = () => {
                    if(onConfirm) onConfirm();
                    closeModal();
                };
                
                actions.appendChild(cancelBtn);
                actions.appendChild(confirmBtn);
            }

            // Show
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
            lucide.createIcons();
        }

        function closeModal() {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Custom Markers
        // Icons based on intensity
        function getIcon(type, intensity) {
            let colorClass = 'text-red-500';
            let bgClass = 'bg-red-600';
            
            if (intensity === 'low') {
                colorClass = 'text-green-500';
                bgClass = 'bg-green-600';
            } else if (intensity === 'medium') {
                colorClass = 'text-amber-500';
                bgClass = 'bg-amber-600';
            }

            if (type === 'system') {
                return L.divIcon({ 
                    html: `<div class="relative"><div class="animate-ping absolute h-full w-full rounded-full ${bgClass} opacity-40"></div><svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="${colorClass} fill-black drop-shadow-[0_0_8px_rgba(0,0,0,1)]"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg></div>`, 
                    className: 'bg-transparent', iconSize: [30, 30], iconAnchor: [15, 30]
                });
            } else {
                return L.divIcon({ 
                    html: `<div class="relative"><div class="animate-ping absolute h-full w-full rounded-full ${bgClass} opacity-20"></div><svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="${colorClass} fill-black/50 drop-shadow-md"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></div>`, 
                    className: 'bg-transparent', iconSize: [30, 30], iconAnchor: [15, 30]
                });
            }
        }

        const userIcon = L.divIcon({ 
            html: `<div class="relative"><div class="animate-ping absolute h-full w-full rounded-full bg-amber-500 opacity-20"></div><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-500 fill-black drop-shadow-lg relative z-10"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></div>`, 
            className: 'bg-transparent', 
            iconSize: [36, 36],
            iconAnchor: [18, 36]
        });

        const systemIcon = L.divIcon({ 
            html: `<div class="relative"><div class="animate-ping absolute h-full w-full rounded-full bg-red-600 opacity-40"></div><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-600 fill-black drop-shadow-[0_0_10px_rgba(220,38,38,0.8)] relative z-10"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg></div>`, 
            className: 'bg-transparent', 
            iconSize: [40, 40],
            iconAnchor: [20, 40]
        });

        function initMap() {
            // Tanauan City Coordinates
            const tanauanLat = 14.0855;
            const tanauanLng = 121.1475;
            
            // Bounds for Tanauan (Approximate square to limit dragging)
            const southWest = L.latLng(14.0300, 121.0800);
            const northEast = L.latLng(14.1500, 121.2000);
            const bounds = L.latLngBounds(southWest, northEast);

            map = L.map('main-map', {
                center: [tanauanLat, tanauanLng],
                zoom: 14,
                minZoom: 13, 
                maxBounds: bounds, 
                maxBoundsViscosity: 1.0, 
                zoomControl: false 
            });
            
            L.control.zoom({ position: 'bottomright' }).addTo(map);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            
            map.on('click', (e) => {
                if(reportMarker) map.removeLayer(reportMarker);
                reportMarker = L.marker(e.latlng, { draggable: true, icon: userIcon }).addTo(map);
                updateReportUI(e.latlng);
                reportMarker.on('dragend', (ev) => updateReportUI(ev.target.getLatLng()));
            });

            // Initial fetch
            fetchReports();
            
            // NOTE: In production, uncomment the interval
            // setInterval(fetchReports, 5000); 
        }

        async function fetchReports() {
            try {
                // REAL API CALL
                const res = await fetch('api_reports.php');
                const reports = await res.json();
                
                // Clear markers except current report marker
                map.eachLayer((layer) => {
                    if (layer instanceof L.Marker && layer !== reportMarker) map.removeLayer(layer);
                });

                if (Array.isArray(reports)) {
                    reports.forEach(r => {
                        const icon = r.type === 'system' ? systemIcon : userIcon;
                        // Use parseFloat to ensure valid coordinates
                        const lat = parseFloat(r.lat);
                        const lng = parseFloat(r.lng);
                        const intensity = r.intensity ? r.intensity.toLowerCase() : 'unknown';

                        if (!isNaN(lat) && !isNaN(lng)) {
                            L.marker([lat, lng], {icon: getIcon(r.type, intensity)})
                                .bindPopup(`<div class='text-black font-sans'><b>${r.type.toUpperCase()} ALERT</b><br>${r.location_name}<br>Intensity: ${r.intensity ? r.intensity.toUpperCase() : 'N/A'}</div>`)
                                .addTo(map);
                        }
                    });
                }
            } catch (e) {
                console.error("Error fetching reports", e);
            }
        }

        function updateReportUI(latlng) {
            document.getElementById('lat-lng-display').textContent = `${latlng.lat.toFixed(5)}, ${latlng.lng.toFixed(5)}`;
            document.getElementById('lat-lng-display').classList.add('text-red-400', 'animate-pulse');
            setTimeout(() => document.getElementById('lat-lng-display').classList.remove('animate-pulse'), 500);
            
            checkSubmitStatus();
        }

        // --- Intensity Selection Logic ---
        function selectIntensity(level) {
            document.querySelectorAll('.intensity-btn').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-white', 'opacity-100');
                btn.classList.add('opacity-50');
            });
            
            const selectedBtn = document.getElementById(`intensity-${level}`);
            selectedBtn.classList.remove('opacity-50');
            selectedBtn.classList.add('ring-2', 'ring-white', 'opacity-100');
            
            document.getElementById('selected-intensity').value = level;
            checkSubmitStatus();
            
            // Change report marker color dynamically
            if(reportMarker) {
                const icon = getIcon('user', level);
                reportMarker.setIcon(icon);
            }
        }

        function checkSubmitStatus() {
            const hasLocation = document.getElementById('lat-lng-display').textContent !== "- - . - - , - - . - -";
            const hasIntensity = document.getElementById('selected-intensity').value !== "";
            const btn = document.getElementById('submit-report-btn');
            
            if (hasLocation && hasIntensity) {
                btn.disabled = false;
            } else {
                btn.disabled = true;
            }
        }

        document.getElementById('submit-report-btn').addEventListener('click', async () => {
            if(!reportMarker) return;
            const ll = reportMarker.getLatLng();
            const intensity = document.getElementById('selected-intensity').value;
            const btn = document.getElementById('submit-report-btn');
            const originalContent = btn.innerHTML;
            
            btn.innerHTML = '<span class="animate-pulse">TRANSMITTING...</span>'; 
            btn.disabled = true;

            try {
                // REAL API CALL
                const res = await fetch('api_reports.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        type: 'user',
                        lat: ll.lat,
                        lng: ll.lng,
                        location_name: `User Report (${ll.lat.toFixed(4)}, ${ll.lng.toFixed(4)})`,
                        intensity: intensity
                    })
                });
                const data = await res.json();

                if(data.success) {
                    btn.classList.remove('bg-red-700', 'hover:bg-red-600');
                    btn.classList.add('bg-green-600', 'hover:bg-green-500');
                    btn.innerHTML = '<span class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4"></i> SENT</span>';
                    lucide.createIcons();
                    
                    showModal({
                        title: "TRANSMISSION SUCCESS",
                        message: "Emergency report successfully transmitted to Tanauan Command Center. Assets are being deployed.",
                        type: "success"
                    });
                    
                    map.removeLayer(reportMarker);
                    reportMarker = null;
                    
                    // Reset Intensity Selection
                    document.querySelectorAll('.intensity-btn').forEach(btn => {
                        btn.classList.remove('ring-2', 'ring-white', 'opacity-100');
                        btn.classList.add('opacity-50'); 
                    });
                    document.getElementById('selected-intensity').value = "";

                    setTimeout(() => {
                        btn.disabled = true;
                        btn.classList.add('bg-red-700', 'hover:bg-red-600');
                        btn.classList.remove('bg-green-600', 'hover:bg-green-500');
                        btn.innerHTML = originalContent;
                        document.getElementById('lat-lng-display').textContent = "- - . - - , - - . - -";
                        document.getElementById('lat-lng-display').classList.remove('text-red-400');
                        lucide.createIcons();
                    }, 2000);
                    
                    fetchReports();
                } else {
                    alert("Error: " + data.message);
                }
            } catch(e) { 
                console.error(e); 
                alert("Connection failed");
            }
        });

        // LOGOUT CONFIRMATION
        function confirmLogout() {
            showModal({
                title: "TERMINATE SESSION?",
                message: "You are about to disconnect from the secure network. Re-authentication will be required.",
                type: "danger",
                onConfirm: performLogout
            });
        }

        async function performLogout() {
            const formData = new FormData();
            formData.append('action', 'logout');

            try {
                await fetch('api_auth.php', { method: 'POST', body: formData });
                window.location.href = 'index.php';
            } catch (error) {
                console.error("Logout failed:", error);
                window.location.href = 'index.php';
            }
        }

        // --- SEARCH FUNCTIONALITY ---
        const searchInput = document.getElementById('map-search');
        const searchResults = document.getElementById('search-results');
        const searchBtn = document.getElementById('search-btn');
        const tanauanViewbox = '121.08,14.04,121.20,14.14'; // Tanauan bounding box for Nominatim

        // Debounce helper
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        async function searchLocation(query) {
            if (!query || query.length < 3) {
                searchResults.classList.add('hidden');
                return;
            }

            try {
                // Search restricted to Tanauan area via viewbox and bounded=1
                const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&viewbox=${tanauanViewbox}&bounded=1&limit=5`);
                const data = await response.json();

                searchResults.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(place => {
                        const item = document.createElement('div');
                        item.className = 'search-item';
                        item.textContent = place.display_name;
                        item.onclick = () => {
                            const lat = parseFloat(place.lat);
                            const lon = parseFloat(place.lon);
                            
                            // Center map and add report marker
                            map.setView([lat, lon], 16);
                            if(reportMarker) map.removeLayer(reportMarker);
                            
                            // When searching, use default user icon until intensity is selected
                            reportMarker = L.marker([lat, lon], { draggable: true, icon: userIcon }).addTo(map);
                            updateReportUI({ lat: lat, lng: lon });
                            
                            // Reset search
                            searchInput.value = place.display_name;
                            searchResults.classList.add('hidden');
                        };
                        searchResults.appendChild(item);
                    });
                    searchResults.classList.remove('hidden');
                } else {
                    searchResults.innerHTML = '<div class="p-3 text-sm text-gray-500">No locations found in Tanauan.</div>';
                    searchResults.classList.remove('hidden');
                }
            } catch (error) {
                console.error("Search error:", error);
            }
        }

        // Event Listeners for Search
        searchInput.addEventListener('input', debounce((e) => searchLocation(e.target.value), 300));
        
        searchBtn.addEventListener('click', () => searchLocation(searchInput.value));

        // Hide results when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });

        initMap();
    </script>
</body>
</html>