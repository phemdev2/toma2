<!-- CREATE GROUP MODAL -->
<div id="groupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-lg p-6 w-96 shadow-2xl transform transition-all">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Create New Group</h2>
            <button onclick="closeGroupModal()" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('chat.createGroup') }}" method="POST">
            @csrf
            
            <!-- Group Name Input -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Group Name</label>
                <input type="text" name="name" required 
                       class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-purple-500 focus:outline-none" 
                       placeholder="e.g. Developers Team">
            </div>

            <!-- User Selection -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Add Members</label>
                <div class="border border-gray-300 rounded-lg max-h-40 overflow-y-auto bg-gray-50">
                    @foreach($users as $user)
                        <label class="flex items-center p-2 hover:bg-purple-50 cursor-pointer border-b border-gray-100 last:border-0">
                            <input type="checkbox" name="users[]" value="{{ $user->id }}" class="form-checkbox h-4 w-4 text-purple-600 rounded">
                            <span class="ml-3 text-gray-700 text-sm">{{ $user->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-2">Select the users you want to add to this group.</p>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeGroupModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium shadow-md transition">
                    Create Group
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openGroupModal() {
        const modal = document.getElementById('groupModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeGroupModal() {
        const modal = document.getElementById('groupModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close on click outside
    document.getElementById('groupModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeGroupModal();
        }
    });
</script>