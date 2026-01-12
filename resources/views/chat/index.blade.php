@extends('layouts.app')

@section('content')
<!-- Load PeerJS Library -->
<script src="https://unpkg.com/peerjs@1.5.2/dist/peerjs.min.js"></script>

<div class="container mx-auto p-6 h-[85vh]">
    <div class="flex h-full bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
        
        <!-- SIDEBAR -->
        <div class="w-1/3 bg-gray-50 border-r border-gray-200 flex flex-col">
            <!-- Header -->
            <div class="p-4 bg-purple-700 text-white flex justify-between items-center">
                <h2 class="font-bold">Chats</h2>
                <button onclick="openGroupModal()" class="text-xs bg-purple-900 hover:bg-purple-800 px-2 py-1 rounded transition">
                    + Group
                </button>
            </div>
            
            <!-- Groups List -->
            <div class="overflow-y-auto max-h-48 border-b bg-purple-50">
                <div class="p-2 text-xs font-bold text-gray-500 uppercase">Groups</div>
                @foreach($groups as $group)
                <button onclick="loadChat('group', {{ $group->id }}, '{{ $group->name }}', {{ $group->owner_id }})" 
                        class="w-full text-left p-3 hover:bg-purple-100 flex items-center gap-3 chat-item transition"
                        id="group-btn-{{ $group->id }}">
                    <div class="h-8 w-8 rounded bg-purple-500 text-white flex items-center justify-center font-bold">#</div>
                    <span class="font-semibold text-gray-700">{{ $group->name }}</span>
                </button>
                @endforeach
            </div>

            <!-- Users List -->
            <div class="overflow-y-auto flex-1">
                <div class="p-2 text-xs font-bold text-gray-500 uppercase">Direct Messages</div>
                @foreach($users as $user)
                <button onclick="loadChat('user', {{ $user->id }}, '{{ $user->name }}')" 
                        class="w-full text-left p-3 hover:bg-gray-100 flex items-center gap-3 chat-item transition"
                        id="user-btn-{{ $user->id }}">
                    <div class="relative">
                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center font-bold text-gray-600">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <!-- Online Status Logic -->
                        @if($user->isOnline())
                            <span class="absolute bottom-0 right-0 h-2 w-2 rounded-full bg-green-500 border border-white"></span>
                        @endif
                    </div>
                    <span class="font-medium text-gray-700">{{ $user->name }}</span>
                </button>
                @endforeach
            </div>
        </div>

        <!-- MAIN CHAT AREA -->
        <div class="w-2/3 flex flex-col bg-white relative">
            
            <!-- Chat Header -->
            <div class="p-4 border-b border-gray-200 bg-gray-50 h-16 flex justify-between items-center">
                <div>
                    <h3 id="chat-header" class="text-lg font-bold text-gray-700">Select a chat</h3>
                    <!-- Connection Status Indicator -->
                    <p id="peer-status" class="text-xs text-red-500 font-bold">Phone: Connecting...</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Delete Group Button -->
                    <form id="delete-group-form" method="POST" action="" class="hidden" onsubmit="return confirm('Delete group?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-bold border border-red-200 bg-red-50 px-3 py-1 rounded transition">
                            Delete Group
                        </button>
                    </form>

                    <!-- Call Button (Phone Icon) -->
                    <button id="call-btn" onclick="startVideoCall()" class="hidden text-gray-600 hover:text-purple-600 transition p-2 rounded-full hover:bg-gray-100" title="Start Voice Call">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Messages Container -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4 bg-white pb-20">
                <div class="flex h-full items-center justify-center text-gray-400">Select a chat to start</div>
            </div>

            <!-- Input Area -->
            <div class="p-4 border-t border-gray-200 hidden bg-white absolute bottom-0 w-full" id="input-area">
                
                <!-- Edit Indicator -->
                <div id="edit-indicator" class="hidden text-xs text-purple-600 font-bold mb-1 flex justify-between px-2">
                    <span>Editing Message...</span>
                    <button onclick="cancelEdit()" class="text-gray-400 hover:text-gray-600">Cancel</button>
                </div>

                <div class="flex items-center gap-2">
                    <input type="text" id="message-input" class="flex-1 border border-gray-300 rounded-full py-2 px-4 focus:outline-none focus:border-purple-500 transition" placeholder="Type a message...">
                    
                    <button onclick="handleFormSubmit()" class="bg-purple-600 text-white rounded-full p-2 hover:bg-purple-700 transition shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    </button>

                    <button id="mic-btn" class="bg-gray-200 text-gray-700 rounded-full p-2 hover:bg-red-100 transition shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Recording Status -->
                <div id="recording-status" class="hidden absolute -top-12 left-0 w-full bg-red-500 text-white py-1 px-4 text-center text-sm font-bold animate-pulse rounded-t-lg">
                    Recording... Click Mic to Stop & Send
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================= CALL MODALS & OVERLAYS ======================= -->

<!-- 1. Incoming Call Modal -->
<div id="incoming-call-modal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-80 text-center shadow-2xl animate-bounce">
        <div class="h-16 w-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-1">Incoming Call...</h3>
        <p id="caller-name-display" class="text-base font-semibold text-gray-700 mb-6">Unknown User is calling you</p>
        <div class="flex justify-center gap-4">
            <button onclick="rejectCall()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full font-bold">Reject</button>
            <button onclick="acceptCall()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full font-bold">Accept</button>
        </div>
    </div>
</div>

<!-- 2. Active Call Controls -->
<div id="active-call-overlay" class="hidden fixed top-20 left-1/2 transform -translate-x-1/2 bg-purple-600 text-white px-6 py-2 rounded-full z-50 shadow-xl flex items-center gap-4">
    <div class="flex items-center gap-2">
        <span class="animate-pulse h-3 w-3 bg-red-400 rounded-full"></span>
        <span class="font-bold">On Call</span>
    </div>
    <button onclick="endCall()" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded-full text-xs font-bold transition">
        End Call
    </button>
</div>

<!-- 3. Audio Elements -->
<audio id="remote-audio" autoplay></audio>

<!-- NEW: Ringtone Audio -->
<audio id="incoming-ringtone" loop>
    <!-- Simple digital phone ring sound -->
    <source src="https://assets.mixkit.co/active_storage/sfx/2354/2354-preview.mp3" type="audio/mpeg">
</audio>

@include('chat.partials.group_modal') 

<!-- ======================= JAVASCRIPT ======================= -->
<script>
    // Global Vars
    let currentContext = null; 
    let currentId = null;
    let pollingInterval = null;
    const authUserId = {{ auth()->id() }};
    const authUserName = "{{ auth()->user()->name }}"; 
    const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};
    
    // PeerJS & Call Vars
    let peer = null;
    let currentCall = null;
    let localStream = null;
    
    // Audio Elements
    const ringtone = document.getElementById('incoming-ringtone');

    // --- 1. PEERJS INITIALIZATION ---
    function initPeer() {
        peer = new Peer('user_' + authUserId, {
            debug: 2,
            secure: true 
        });

        peer.on('open', (id) => {
            console.log('My Peer ID is: ' + id);
            const statusEl = document.getElementById('peer-status');
            statusEl.innerText = "Phone: Connected (Ready)";
            statusEl.classList.remove('text-red-500');
            statusEl.classList.add('text-green-500');
        });

        // 2. Incoming Call Handler
        peer.on('call', (call) => {
            if (currentCall) {
                // We are busy
                return; 
            }
            
            // Extract Metadata
            const callerName = (call.metadata && call.metadata.callerName) ? call.metadata.callerName : "Unknown User";
            
            // Update UI
            document.getElementById('caller-name-display').innerText = callerName + " is calling you...";
            document.getElementById('incoming-call-modal').classList.remove('hidden');
            document.getElementById('incoming-call-modal').classList.add('flex');
            
            // PLAY RINGTONE
            ringtone.play().catch(error => {
                console.log("Autoplay prevented by browser:", error);
            });

            currentCall = call;
        });

        peer.on('error', (err) => {
            console.error('PeerJS Error:', err);
            if (err.type === 'peer-unavailable') {
                alert("The user you are calling is Offline.");
                endCall();
            } else if (err.type === 'network') {
                document.getElementById('peer-status').innerText = "Phone: Network Error";
            }
        });

        peer.on('disconnected', () => {
            document.getElementById('peer-status').innerText = "Phone: Disconnected";
            document.getElementById('peer-status').classList.add('text-red-500');
            peer.reconnect();
        });
    }

    initPeer();

    // --- 2. CALL FUNCTIONS ---

    async function startVideoCall() {
        if(currentContext === 'group') {
            alert("Group calling is not supported.");
            return;
        }

        const statusText = document.getElementById('peer-status').innerText;
        if(statusText.includes('Disconnected') || statusText.includes('Connecting')) {
            alert("Phone service is not ready. Please wait.");
            return;
        }

        try {
            // Get Mic
            localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            
            const targetPeerId = 'user_' + currentId;
            
            const options = {
                metadata: {
                    "callerName": authUserName 
                }
            };
            
            console.log("Calling: " + targetPeerId + " as " + authUserName);
            
            const call = peer.call(targetPeerId, localStream, options); 
            
            if(!call) {
                alert("Could not initiate call.");
                return;
            }

            handleCallStream(call);
            showActiveCallUI();

        } catch (err) {
            console.error(err);
            alert("Microphone access denied or HTTPS missing.");
        }
    }

    async function acceptCall() {
        // STOP RINGTONE
        ringtone.pause();
        ringtone.currentTime = 0;

        document.getElementById('incoming-call-modal').classList.add('hidden');
        document.getElementById('incoming-call-modal').classList.remove('flex');

        try {
            localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            currentCall.answer(localStream);
            handleCallStream(currentCall);
            showActiveCallUI();
        } catch (err) {
            alert("Could not access microphone.");
            endCall();
        }
    }

    function rejectCall() {
        // STOP RINGTONE
        ringtone.pause();
        ringtone.currentTime = 0;

        document.getElementById('incoming-call-modal').classList.add('hidden');
        document.getElementById('incoming-call-modal').classList.remove('flex');
        if(currentCall) {
            currentCall.close();
            currentCall = null;
        }
    }

    function endCall() {
        // Just in case, stop ringtone
        ringtone.pause();
        ringtone.currentTime = 0;

        if (currentCall) currentCall.close();
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
        }
        
        currentCall = null;
        localStream = null;
        document.getElementById('active-call-overlay').classList.add('hidden');
    }

    function handleCallStream(call) {
        currentCall = call;

        call.on('stream', (remoteStream) => {
            const audio = document.getElementById('remote-audio');
            audio.srcObject = remoteStream;
            audio.play().catch(e => console.log("Autoplay blocked:", e));
        });

        call.on('close', () => {
            endCall();
            alert("Call Ended");
        });
        
        call.on('error', (err) => {
            endCall();
        });
    }

    function showActiveCallUI() {
        document.getElementById('active-call-overlay').classList.remove('hidden');
    }

    // --- 3. CHAT LOGIC ---

    let isEditing = false;
    let editingMessageId = null;
    let mediaRecorder;
    let audioChunks = [];
    let isRecording = false;

    function loadChat(context, id, name, ownerId = null) {
        currentContext = context;
        currentId = id;
        
        cancelEdit();
        document.getElementById('chat-header').innerText = name;
        document.getElementById('input-area').classList.remove('hidden');
        
        const callBtn = document.getElementById('call-btn');
        if(context === 'user') callBtn.classList.remove('hidden');
        else callBtn.classList.add('hidden');

        const delForm = document.getElementById('delete-group-form');
        if (context === 'group' && (ownerId === authUserId || isAdmin)) {
            delForm.action = `/chat/group/${id}`;
            delForm.classList.remove('hidden');
        } else {
            delForm.classList.add('hidden');
        }

        document.querySelectorAll('.chat-item').forEach(el => el.classList.remove('bg-purple-100'));
        if(context === 'group') document.getElementById(`group-btn-${id}`).classList.add('bg-purple-100');
        else document.getElementById(`user-btn-${id}`).classList.add('bg-purple-100');

        fetchMessages();
        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(fetchMessages, 3000);
    }

    async function fetchMessages() {
        if (!currentId) return;
        if(isEditing) return; 

        try {
            const response = await fetch(`/chat/messages/${currentContext}/${currentId}`);
            const messages = await response.json();
            const container = document.getElementById('messages-container');
            container.innerHTML = '';

            messages.forEach(msg => {
                const isMe = msg.from_id === authUserId;
                let actionsHtml = '';
                if (isMe) {
                    actionsHtml = `
                        <div class="absolute top-0 right-0 -mt-2 -mr-2 hidden group-hover:flex gap-1 z-10">
                            ${msg.type === 'text' ? `
                                <button onclick="startEdit(${msg.id}, '${msg.message.replace(/'/g, "\\'")}')" class="bg-gray-100 hover:bg-white text-gray-600 rounded-full p-1 shadow border">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            ` : ''}
                            <button onclick="deleteMessage(${msg.id})" class="bg-gray-100 hover:bg-red-50 text-red-500 rounded-full p-1 shadow border">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    `;
                }

                let contentHtml = `<p class="text-sm">${msg.message}</p>`;
                if (msg.type === 'audio') contentHtml = `<audio controls class="w-48 h-8"><source src="/storage/${msg.attachment}" type="audio/webm"></audio>`;
                if (msg.type === 'call') contentHtml = `<span class="text-xs text-gray-400 italic">Call Log: Video/Voice Call</span>`;

                const html = `
                    <div class="flex ${isMe ? 'justify-end' : 'justify-start'} mb-4 group relative">
                        <div class="max-w-xs md:max-w-md rounded-lg px-4 py-2 shadow relative ${isMe ? 'bg-purple-100 border border-purple-200' : 'bg-white border border-gray-200'}">
                            ${(!isMe && currentContext === 'group' && msg.sender) ? `<span class="text-[10px] text-purple-600 font-bold block">${msg.sender.name}</span>` : ''}
                            ${contentHtml}
                            <span class="text-[10px] block text-right mt-1 opacity-50">${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                            ${actionsHtml}
                        </div>
                    </div>
                `;
                container.innerHTML += html;
            });
            container.scrollTop = container.scrollHeight;
        } catch (error) { console.error(error); }
    }

    async function handleFormSubmit() {
        const input = document.getElementById('message-input');
        const message = input.value;
        if (!message.trim()) return;

        if (isEditing) {
            await fetch(`/chat/message/${editingMessageId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ message: message })
            });
            cancelEdit();
        } else {
            await fetch('/chat/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ target_id: currentId, context: currentContext, message: message, type: 'text' })
            });
        }
        
        input.value = '';
        fetchMessages();
    }

    function startEdit(id, text) {
        isEditing = true;
        editingMessageId = id;
        const input = document.getElementById('message-input');
        input.value = text;
        input.focus();
        document.getElementById('edit-indicator').classList.remove('hidden');
        input.classList.add('border-purple-500', 'bg-purple-50');
    }

    function cancelEdit() {
        isEditing = false;
        editingMessageId = null;
        const input = document.getElementById('message-input');
        input.value = '';
        input.classList.remove('border-purple-500', 'bg-purple-50');
        document.getElementById('edit-indicator').classList.add('hidden');
    }

    async function deleteMessage(id) {
        if(!confirm('Delete this message?')) return;
        await fetch(`/chat/message/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        fetchMessages();
    }

    document.getElementById('message-input').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') handleFormSubmit();
    });

    // Voice Note Logic
    const micBtn = document.getElementById('mic-btn');
    const mediaMimeType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus') ? 'audio/webm;codecs=opus' : 'audio/webm';

    micBtn.onclick = () => {
        if(!isRecording) startRecording();
        else stopRecording();
    };

    async function startRecording() {
        if(isRecording) return;
        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            alert("Microphone only works on HTTPS or Localhost.");
            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream, { mimeType: mediaMimeType });
            audioChunks = [];

            mediaRecorder.ondataavailable = event => {
                if (event.data.size > 0) audioChunks.push(event.data);
            };

            mediaRecorder.onstop = sendVoiceNote;
            mediaRecorder.start();
            isRecording = true;
            
            document.getElementById('recording-status').classList.remove('hidden');
            micBtn.classList.remove('bg-gray-200', 'text-gray-700');
            micBtn.classList.add('bg-red-500', 'text-white', 'animate-pulse');
        } catch(err) {
            console.error(err);
            alert('Microphone access denied.');
        }
    }

    function stopRecording() {
        if(!isRecording || !mediaRecorder) return;
        mediaRecorder.stop(); 
        mediaRecorder.stream.getTracks().forEach(track => track.stop());
        isRecording = false;
        
        document.getElementById('recording-status').classList.add('hidden');
        micBtn.classList.remove('bg-red-500', 'text-white', 'animate-pulse');
        micBtn.classList.add('bg-gray-200', 'text-gray-700');
    }

    async function sendVoiceNote() {
        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
        if (audioBlob.size < 1000) return; 

        const formData = new FormData();
        formData.append('attachment', audioBlob, 'voice_note.webm'); 
        formData.append('target_id', currentId);
        formData.append('context', currentContext);
        formData.append('type', 'audio');

        const originalText = document.getElementById('chat-header').innerText;
        document.getElementById('chat-header').innerText = "Sending Audio...";

        try {
            await fetch('/chat/send', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData 
            });
            fetchMessages();
        } catch (error) {
            console.error("Upload failed", error);
            alert("Failed to send voice note.");
        } finally {
            document.getElementById('chat-header').innerText = originalText;
        }
    }
</script>
@endsection