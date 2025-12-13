<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Scanner</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { background-color: #000; color: white; overflow: hidden; }
        #reader { width: 100%; border-radius: 12px; overflow: hidden; margin-bottom: 20px; }
        #reader video { object-fit: cover; }
    </style>
</head>
<body class="flex flex-col items-center justify-center h-screen p-4">
    
    <div class="w-full max-w-sm space-y-4">
        <div class="text-center">
            <h1 class="text-xl font-bold text-emerald-400 uppercase tracking-widest">POS Link</h1>
            <p class="text-gray-500 text-xs font-mono">ID: {{ substr($sessionId, -6) }}</p>
        </div>

        <div class="relative bg-gray-900 rounded-2xl p-1 border border-gray-800 shadow-2xl">
            <div id="reader"></div>
        </div>

        <div id="status" class="text-center font-mono text-sm h-10 flex items-center justify-center bg-gray-900 rounded-lg border border-gray-800 text-gray-400 transition-all duration-200">
            Camera Ready...
        </div>

        <div class="text-center">
            <button onclick="window.location.reload()" class="text-xs text-gray-600 underline">Refresh Camera</button>
        </div>
    </div>

    <script>
        const SESSION_ID = "{{ $sessionId }}";
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const statusEl = document.getElementById('status');
        let isScanning = true;

        function onScanSuccess(decodedText) {
            if (!isScanning) return;
            
            // Prevent double scans
            isScanning = false;
            
            // UI Feedback
            statusEl.className = "text-center font-mono text-sm h-10 flex items-center justify-center rounded-lg border border-emerald-500/50 bg-emerald-500/10 text-emerald-400 font-bold transition-all duration-200";
            statusEl.innerHTML = `Sent: ${decodedText}`;
            if(navigator.vibrate) navigator.vibrate(200);

            // Send to Backend
            fetch("{{ route('scanner.send') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ session_id: SESSION_ID, barcode: decodedText })
            }).then(() => {
                // Reset after delay
                setTimeout(() => { 
                    isScanning = true; 
                    statusEl.className = "text-center font-mono text-sm h-10 flex items-center justify-center bg-gray-900 rounded-lg border border-gray-800 text-gray-400 transition-all duration-200";
                    statusEl.innerText = "Camera Ready..."; 
                }, 1500);
            }).catch(err => {
                isScanning = true;
                statusEl.innerText = "Error sending data";
            });
        }

        const scanner = new Html5QrcodeScanner("reader", { 
            fps: 10, 
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        }, false);
        
        scanner.render(onScanSuccess);
    </script>
</body>
</html>