<?php
session_start();
// Admin authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
   header("Location: index.php");
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ignisense: Admin Command (Local)</title>
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
            background-color: #050202; 
            background-image: 
                radial-gradient(circle at 50% 0%, #2a0a0a 0%, transparent 50%),
                radial-gradient(circle at 0% 100%, #1f0505 0%, transparent 30%);
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
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f0505; }
        ::-webkit-scrollbar-thumb { background: #450a0a; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #7f1d1d; }

        .map-container { 
            height: 500px; 
            width: 100%; 
            border-radius: 0.75rem; 
            border: 1px solid rgba(220, 38, 38, 0.3);
            box-shadow: inset 0 0 20px rgba(0,0,0,0.8);
            z-index: 10;
        }

        /* Leaflet Dark Mode Filter */
        .leaflet-layer,
        .leaflet-control-zoom-in,
        .leaflet-control-zoom-out,
        .leaflet-control-attribution {
            filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
        }

        .glass-panel {
            background: rgba(10, 5, 5, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Animations */
        .view-section { display: none; }
        .view-section.active { display: block; animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .nav-item.active { 
            background: linear-gradient(90deg, rgba(127, 29, 29, 0.3) 0%, transparent 100%);
            color: #fca5a5; 
            border-left: 3px solid #dc2626;
        }
        
        .spinner { width: 56px; height: 56px; border: 5px solid #450a0a; border-bottom-color: #ef4444; border-radius: 50%; animation: rotation 1s linear infinite; }
        @keyframes rotation { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        .scanlines {
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,0) 50%, rgba(0,0,0,0.1) 50%, rgba(0,0,0,0.1));
            background-size: 100% 4px;
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none; z-index: 9999; opacity: 0.2;
        }
    </style>
</head>
<body class="antialiased font-sans selection:bg-red-900 selection:text-white overflow-hidden">

    <div class="scanlines"></div>

    <div id="loading-overlay" class="fixed inset-0 bg-[#050202] z-[99999] flex flex-col items-center justify-center transition-opacity duration-500">
        <div class="relative">
            <div class="spinner"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-2 h-2 bg-red-600 rounded-full animate-ping"></div>
            </div>
        </div>
        <p id="loading-message" class="text-red-500 font-display tracking-widest text-sm mt-6 uppercase animate-pulse">Initializing Command Protocol...</p>
    </div>

    <div class="flex h-screen">
        <aside class="w-72 glass-panel border-r border-red-900/20 hidden md:flex flex-col z-50">
            <div class="p-8 border-b border-white/5 relative overflow-hidden page-transition" style="animation-delay: 0.1s;">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-red-600 to-transparent opacity-50"></div>
                
                <h1 class="text-2xl font-display font-bold text-white flex items-center gap-3">
                    <i data-lucide="shield-alert" class="text-red-600 drop-shadow-lg"></i> 
                    <span class="tracking-wide">ADMIN</span>
                </h1>
                <p class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1 pl-1">Ignisense Systems</p>
            </div>
            
            <nav class="flex-1 p-4 space-y-1 page-transition" style="animation-delay: 0.2s;">
                <p class="px-4 text-[10px] font-bold text-gray-600 uppercase tracking-widest mb-2 mt-4">Monitoring</p>
                <button onclick="switchView('dashboard')" id="nav-dashboard" class="nav-item active w-full flex items-center gap-3 text-gray-400 hover:text-white px-4 py-3 rounded-r-lg transition-all text-sm font-medium group">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 group-hover:text-red-500 transition-colors"></i> Dashboard
                </button>
                
                <p class="px-4 text-[10px] font-bold text-gray-600 uppercase tracking-widest mb-2 mt-6">Administration</p>
                <button onclick="switchView('users')" id="nav-users" class="nav-item w-full flex items-center gap-3 text-gray-400 hover:text-white px-4 py-3 rounded-r-lg transition-all text-sm font-medium group">
                    <i data-lucide="users" class="w-4 h-4 group-hover:text-red-500 transition-colors"></i> User Database
                </button>
            </nav>

            <div class="p-4 border-t border-white/5 page-transition" style="animation-delay: 0.3s;">
                <button onclick="confirmAction('System Logout', 'Terminate administrative session?', logout)" class="group flex items-center gap-3 text-red-500/70 hover:text-red-500 hover:bg-red-950/30 w-full px-4 py-3 rounded-lg transition-all border border-transparent hover:border-red-900/30">
                    <i data-lucide="log-out" class="w-4 h-4"></i> 
                    <span class="text-sm font-semibold">Terminate Session</span>
                </button>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto relative flex flex-col">
            <header class="sticky top-0 z-40 glass-panel border-b border-red-900/20 px-8 py-4 flex justify-between items-center page-transition" style="animation-delay: 0.1s;">
                <div>
                    <h2 class="text-2xl font-display font-bold text-white tracking-wide" id="page-title">Command Center</h2>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        <span class="text-[10px] text-gray-400 uppercase tracking-wider">Tanauan Sector Net: <span class="text-green-500 font-mono">ONLINE</span></span>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="text-right">
                        <p class="text-xs text-gray-500 uppercase tracking-widest">Server Time</p>
                        <p class="text-sm font-mono text-red-400" id="clock">00:00:00</p>
                    </div>
                </div>
            </header>

            <div class="p-6 md:p-8 flex-1 page-transition" style="animation-delay: 0.2s;">

                <div id="view-dashboard" class="view-section active">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-gradient-to-br from-neutral-900 to-black p-6 rounded-xl border border-white/5 relative group overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity"><i data-lucide="flame" class="w-16 h-16 text-white"></i></div>
                            <p class="text-gray-500 text-xs uppercase tracking-widest font-bold">Total Incidents</p>
                            <p id="stat-total" class="text-5xl font-display font-bold text-white mt-2 group-hover:text-red-100 transition-colors">0</p>
                            <div class="w-full h-1 bg-gray-800 mt-4 rounded-full overflow-hidden">
                                <div class="h-full bg-white/20 w-1/2"></div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-neutral-900 to-black p-6 rounded-xl border border-white/5 border-l-4 border-l-amber-600 relative overflow-hidden">
                            <p class="text-amber-600/70 text-xs uppercase tracking-widest font-bold">Civilian Reports</p>
                            <p id="stat-user" class="text-5xl font-display font-bold text-amber-500 mt-2">0</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-neutral-900 to-black p-6 rounded-xl border border-white/5 border-l-4 border-l-red-600 relative overflow-hidden">
                            <div class="absolute inset-0 bg-red-900/5 animate-pulse"></div>
                            <p class="text-red-600/70 text-xs uppercase tracking-widest font-bold">System Alerts</p>
                            <p id="stat-system" class="text-5xl font-display font-bold text-red-500 mt-2 text-shadow-glow">0</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 space-y-4">
                            <div class="flex justify-between items-center px-1">
                                <h3 class="text-lg font-display font-bold text-white flex items-center gap-2">
                                    <i data-lucide="map" class="w-4 h-4 text-red-500"></i> Geospatial View
                                </h3>
                                <button onclick="simulateFire()" class="bg-red-900/20 hover:bg-red-900/40 text-red-400 border border-red-900/50 text-xs px-4 py-2 rounded flex items-center gap-2 transition-all hover:shadow-glow-sm">
                                    <i data-lucide="zap" class="w-3 h-3"></i> TEST SENSOR
                                </button>
                            </div>
                            
                            <div class="relative p-1 rounded-xl bg-gradient-to-b from-gray-800 to-black border border-gray-700/50">
                                <div id="admin-map" class="map-container bg-neutral-900"></div>
                                <div class="absolute top-4 right-4 z-[400] bg-black/80 backdrop-blur border border-red-900/50 px-3 py-1 text-[10px] font-mono text-red-400">
                                    <span class="animate-pulse">●</span> LIVE FEED
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-lg font-display font-bold text-white flex items-center gap-2 px-1">
                                <i data-lucide="radio" class="w-4 h-4 text-red-500"></i> Incoming Feed
                            </h3>
                            <div class="bg-neutral-900/50 border border-white/5 rounded-xl p-2 h-[500px] flex flex-col relative">
                                <div class="absolute top-0 left-0 w-full h-1 bg-red-500/20 z-10 animate-scan"></div>
                                
                                <div id="report-list" class="space-y-2 overflow-y-auto custom-scrollbar p-2 flex-1">
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="view-users" class="view-section">
                    <div class="bg-neutral-900/80 rounded-xl border border-white/10 overflow-hidden shadow-2xl">
                        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-black/40">
                            <div>
                                <h3 class="text-xl font-display font-bold text-white">Personnel Database</h3>
                                <p class="text-xs text-gray-500 mt-1">Authorized access credentials</p>
                            </div>
                            <div class="flex gap-2">
                                <input type="text" placeholder="Search ID..." class="bg-black border border-white/10 rounded px-3 py-1 text-sm text-gray-300 focus:border-red-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-gray-400">
                                <thead class="bg-red-950/20 text-red-200/80 uppercase text-xs tracking-wider font-display">
                                    <tr>
                                        <th class="px-6 py-4">Identity</th>
                                        <th class="px-6 py-4">Contact Protocol</th>
                                        <th class="px-6 py-4">Clearance</th>
                                        <th class="px-6 py-4">Timestamp</th>
                                        <th class="px-6 py-4 text-right">Admin Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="users-table-body" class="divide-y divide-white/5">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="mt-auto border-t border-red-900/20 bg-[#020101] py-8 px-10">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-xs text-gray-600 font-mono">
                        SYSTEM_ID: IGNIS_TANAUAN_01<br>
                        BUILD: 2026.04.12
                    </div>
                    <div class="text-center md:text-right">
                        <h4 class="text-white font-display font-bold mb-1">Ignisense Systems</h4>
                        <p class="text-gray-600 text-xs">Restricted Access. Unauthorized use is a criminal offense.</p>
                    </div>
                </div>
            </footer>
        </main>
    </div>

    <div id="custom-modal" class="fixed inset-0 bg-black/90 backdrop-blur-md hidden z-[10000] flex items-center justify-center">
        <div class="bg-[#0f0505] p-1 rounded-2xl border border-red-900/50 max-w-sm w-full shadow-[0_0_50px_rgba(220,38,38,0.2)] modal-enter relative overflow-hidden">
            <div class="h-1 w-full bg-gradient-to-r from-red-600 via-orange-500 to-red-600"></div>
            
            <div class="p-8">
                <div class="w-12 h-12 rounded-full bg-red-900/20 flex items-center justify-center mb-4 mx-auto border border-red-900/50">
                    <i data-lucide="alert-triangle" class="text-red-500 w-6 h-6"></i>
                </div>
                
                <h3 id="modal-title" class="text-xl font-display font-bold text-white mb-2 text-center tracking-wide">CONFIRMATION REQUIRED</h3>
                <p id="modal-message" class="text-gray-400 mb-8 text-center text-sm leading-relaxed">Action pending authorization.</p>
                
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="closeModal()" class="px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-all text-sm font-medium border border-transparent">CANCEL</button>
                    <button id="modal-confirm-btn" class="px-4 py-3 bg-red-700 hover:bg-red-600 text-white rounded-lg font-bold shadow-lg shadow-red-900/30 transition-all text-sm tracking-wide border border-red-500/50">PROCEED</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        // Clock
        setInterval(() => {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('en-US', {hour12: false});
        }, 1000);

        let map;
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

        // --- Initialization ---
        document.addEventListener('DOMContentLoaded', async () => {
            initMap();
            
            // Fetch initial data
            try {
                await fetchReports();
                
                // Hide loading screen after fetch
                document.getElementById('loading-overlay').classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => { 
                    document.getElementById('loading-overlay').style.display = 'none'; 
                }, 500);

            } catch (error) {
                console.error("Connection Failed:", error);
                // Even on error, hide loading screen to show empty UI
                document.getElementById('loading-overlay').style.display = 'none';
            }
        });

        // --- Map & Data Logic ---
        function initMap() {
            // Tanauan City Coordinates
            const tanauanLat = 14.0855;
            const tanauanLng = 121.1475;
            
            // Bounds for Tanauan
            const southWest = L.latLng(14.0300, 121.0800);
            const northEast = L.latLng(14.1500, 121.2000);
            const bounds = L.latLngBounds(southWest, northEast);

            map = L.map('admin-map', {
                center: [tanauanLat, tanauanLng],
                zoom: 14,
                minZoom: 13,
                maxBounds: bounds,
                maxBoundsViscosity: 1.0,
                zoomControl: false
            });
            
            L.control.zoom({ position: 'bottomright' }).addTo(map);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            
            // Start polling for real updates
            setInterval(() => { 
                if(document.getElementById('view-dashboard').classList.contains('active')) fetchReports(); 
            }, 5000);
        }

        async function fetchReports() {
            try {
                // REAL API CALL
                const res = await fetch('api_reports.php');
                const data = await res.json();
                
                if(Array.isArray(data)) updateDashboard(data);
                else if (data.data && Array.isArray(data.data)) updateDashboard(data.data);
                
            } catch(e) { console.error("Error fetching reports:", e); }
        }

        function updateDashboard(data) {
            let total = 0, users = 0, system = 0;
            const list = document.getElementById('report-list');
            list.innerHTML = '';
            
            // Clean markers
            map.eachLayer(l => { if(l instanceof L.Marker) map.removeLayer(l); });

            data.forEach(r => {
                total++;
                if(r.type === 'user') users++; else system++;

                const lat = parseFloat(r.lat);
                const lng = parseFloat(r.lng);
                const intensity = r.intensity ? r.intensity.toLowerCase() : 'unknown';
                
                // Grab the reporter's name or default to UNKNOWN
                const reporterName = r.reporter_name ? r.reporter_name.toUpperCase() : 'UNKNOWN CIVILIAN';
                const displayTitle = r.type === 'system' ? 'AUTO-SENSOR ALERT' : `REPORT BY: ${reporterName}`;

                if (!isNaN(lat) && !isNaN(lng)) {
                    // Update the Map Popup to show the name
                    L.marker([lat, lng], {icon: getIcon(r.type, intensity)})
                    .addTo(map).bindPopup(`
                        <div class="text-black font-bold border-b border-gray-300 pb-1 mb-1">${displayTitle}</div>
                        <div class="text-black text-xs font-normal">${r.location_name}<br><b>Intensity:</b> ${intensity.toUpperCase()}</div>
                    `);
                }

                const el = document.createElement('div');
                el.className = 'group bg-black/40 p-3 rounded border border-white/5 flex justify-between items-center hover:bg-white/5 hover:border-red-500/30 transition-all cursor-pointer';
                
                el.onclick = (e) => {
                    if(!e.target.closest('button')) map.flyTo([lat, lng], 16);
                };

                let badgeClass = 'bg-gray-800 text-gray-400 border-gray-700';
                if (intensity === 'high') badgeClass = 'bg-red-900/50 text-red-400 border-red-800';
                else if (intensity === 'medium') badgeClass = 'bg-amber-900/50 text-amber-400 border-amber-800';
                else if (intensity === 'low') badgeClass = 'bg-green-900/50 text-green-400 border-green-800';

                const intensityBadge = r.intensity 
                    ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase border ${badgeClass}">${r.intensity}</span>`
                    : '';

                const listIconClass = intensity === 'low' ? 'text-green-500' : (intensity === 'medium' ? 'text-amber-500' : 'text-red-500');

                // Update the Sidebar List to show the name
                el.innerHTML = `
                    <div class="flex items-start gap-3">
                        <div class="mt-1 ${listIconClass}">
                            ${r.type === 'system' ? '<i data-lucide="siren" class="w-5 h-5 animate-pulse"></i>' : '<i data-lucide="user" class="w-5 h-5"></i>'}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-200 group-hover:text-white flex items-center gap-2">
                                ${displayTitle}
                                ${intensityBadge}
                            </div>
                            <div class="text-xs text-gray-500 font-mono mt-0.5">${r.location_name}</div>
                        </div>
                    </div>
                    <button onclick="requestDelReport(${r.id})" class="text-gray-600 hover:text-red-500 hover:bg-red-500/10 p-2 rounded transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                `;
                list.appendChild(el);
            });

            document.getElementById('stat-total').textContent = total;
            document.getElementById('stat-user').textContent = users;
            document.getElementById('stat-system').textContent = system;
            lucide.createIcons();
        }

        // --- User Management ---
        async function fetchUsers() {
            try {
                // REAL FETCH: Connects to your PHP backend
                const res = await fetch('api_users.php');
                
                // Handle HTTP errors
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }

                const users = await res.json();

                // Check if API returned an error object instead of an array
                if (users.success === false) {
                    console.error("API Error:", users.message);
                    return;
                }

                const tbody = document.getElementById('users-table-body');
                tbody.innerHTML = '';

                // Ensure we have an array before trying to loop
                const userList = Array.isArray(users) ? users : (users.data || []);

                userList.forEach(u => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-red-900/10 transition-colors group';
                    
                    // Render real data (Email, Role, etc.)
                    tr.innerHTML = `
                        <td class="px-6 py-4 font-medium text-white group-hover:text-red-200">
                            ${u.name || 'Unknown User'}
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                            ${u.email || 'No Email'}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded border ${u.role === 'admin' ? 'border-red-500/50 bg-red-500/10 text-red-400' : 'border-gray-700 bg-gray-800 text-gray-400'} text-[10px] uppercase font-bold tracking-wider">
                                ${u.role || 'user'}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 text-xs">${u.created_at || '-'}</td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="requestDelUser(${u.id}, '${u.email}')" class="text-gray-600 hover:text-white hover:bg-red-600 px-3 py-1 rounded text-xs font-bold transition-all border border-transparent hover:border-red-400 uppercase">
                                REVOKE
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch(e) { 
                console.error("Failed to load users", e);
                document.getElementById('users-table-body').innerHTML = `<tr><td colspan="5" class="text-center py-4 text-red-500 text-sm">Error loading database. Ensure api_users.php is running.</td></tr>`;
            }
        }

        // --- Navigation ---
        function switchView(viewName) {
            document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));

            document.getElementById(`view-${viewName}`).classList.add('active');
            document.getElementById(`nav-${viewName}`).classList.add('active');
            
            const title = document.getElementById('page-title');
            if (viewName === 'dashboard') {
                title.textContent = 'Command Center';
                if(map) setTimeout(() => map.invalidateSize(), 100);
            } else {
                title.textContent = 'Personnel Database';
                fetchUsers();
            }
        }

        // --- Modal Logic ---
        let confirmCallback = null;
        const modal = document.getElementById('custom-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalMsg = document.getElementById('modal-message');

        function confirmAction(title, message, callback) {
            modalTitle.textContent = title;
            modalMsg.textContent = message;
            confirmCallback = callback;
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
            confirmCallback = null;
        }

        document.getElementById('modal-confirm-btn').addEventListener('click', () => {
            if (confirmCallback) confirmCallback();
            closeModal();
        });

        // --- Actions ---
        function requestDelReport(id) {
            confirmAction('CONFIRM DELETION', 'Remove this incident record from the database? This action cannot be undone.', async () => {
                try {
                    await fetch(`api_reports.php?id=${id}`, { method: 'DELETE' });
                    fetchReports();
                } catch(e) {
                    console.error("Deletion failed", e);
                    alert("Failed to delete report.");
                }
            });
        }

        function requestDelUser(id, email) {
            confirmAction('REVOKE CREDENTIALS', `Permanently remove access for ${email}?`, async () => {
                try {
                    const res = await fetch(`api_users.php?id=${id}`, { method: 'DELETE' });
                    const data = await res.json();
                    
                    if(data.success) {
                        fetchUsers();
                    } else {
                        alert("Error: " + (data.message || "Could not delete user."));
                    }
                } catch(e) {
                    console.error("User deletion failed", e);
                }
            });
        }

        async function simulateFire() {
            try {
                await fetch('api_reports.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        type: 'system',
                        lat: 14.0885, // Example
                        lng: 121.1495, // Example
                        location_name: 'Simulated Sensor Trigger'
                    })
                });
                fetchReports(); 
            } catch(e) {
                console.error("Simulation failed", e);
            }
        }

        async function logout() {
            try {
                const formData = new FormData();
                formData.append('action', 'logout');
                await fetch('api_auth.php', { method: 'POST', body: formData });
                window.location.href = 'index.php';
            } catch(e) {
                console.error("Logout failed", e);
                window.location.href = 'index.php';
            }
        }
    </script>
</body>
</html>