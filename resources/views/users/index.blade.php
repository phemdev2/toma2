@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4 text-gray-800">User Management</h1>

    @if(auth()->user()->hasRole('admin'))
        <a href="{{ route('users.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition duration-150 ease-in-out">
           + Create User
        </a>
    @endif

    <!-- Responsive Hoverable Table -->
    <div class="overflow-x-auto mt-6 rounded-lg shadow-md bg-white">
        <table class="min-w-full border-collapse w-full text-left">
            <thead class="bg-purple-600 text-white uppercase text-sm">
    <tr>
        <th class="py-3 px-4 border-b border-purple-700">Name</th>
        <th class="py-3 px-4 border-b border-purple-700">Email</th>
        <th class="py-3 px-4 border-b border-purple-700">Role</th>
        <th class="py-3 px-4 border-b border-purple-700">Store</th>
        <th class="py-3 px-4 border-b border-purple-700 text-center">Actions</th>
    </tr>
</thead>

            <tbody>
                @foreach($users as $user)
                <tr class="hoverable-row">
                    <td class="py-3 px-4 font-medium text-gray-800">{{ $user->name }}</td>
                    <td class="py-3 px-4 text-gray-600">{{ $user->email }}</td>
                    <td class="py-3 px-4 text-gray-700">{{ $user->getRoleNames()->join(', ') }}</td>
                    <td class="py-3 px-4 text-gray-700">{{ $user->store ? $user->store->name : 'N/A' }}</td>
                    <td class="py-3 px-4 text-center space-x-2">
                        <a href="{{ route('users.edit', $user) }}" 
                           class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded transition duration-150 ease-in-out">
                           Edit
                        </a>

                        @if(auth()->user()->hasRole('admin'))
                            <form action="{{ route('users.destroy', $user) }}" 
                                  method="POST" 
                                  class="inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded transition duration-150 ease-in-out">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" 
     class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50"
     role="dialog" aria-modal="true" aria-labelledby="confirmTitle" aria-describedby="confirmMessage">
    <div class="bg-white rounded-lg p-6 w-80 text-center shadow-lg transform transition-all scale-95 hover:scale-100">
        <h2 id="confirmTitle" class="text-lg font-semibold text-gray-800 mb-3">Confirm Delete</h2>
        <p id="confirmMessage" class="text-gray-600 mb-5">Are you sure you want to delete this user?</p>
        <div class="flex justify-center gap-3">
            <button id="cancelDelete" 
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded transition duration-150 ease-in-out">
                Cancel
            </button>
            <button id="confirmDelete" 
                    class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded transition duration-150 ease-in-out">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
/* General Table Styling */
table {
    border-radius: 0.5rem;
    overflow: hidden;
}

/* Hover Row */
.hoverable-row {
    transition: all 0.25s ease;
}
.hoverable-row:hover {
    background-color: #7c3aed; /* 💜 vibrant purple */
    box-shadow: 0 4px 10px rgba(124, 58, 237, 0.25); /* soft purple glow */
    transform: scale(1.01);
    cursor: pointer;
}
.hoverable-row:hover td {
    color: white !important; /* Text turns white on hover */
    background-color: #7c3aed;
    font-weight: 600;
}

/* Table borders */
thead th {
    border-bottom: 2px solid #e5e7eb;
    background-color: #7c3aed;
}

/* Cursor for delete button */
.delete-form button {
    cursor: pointer;
}

/* Modal animation */
#confirmModal.flex .bg-white {
    transform: scale(1);
    transition: transform 0.2s ease-in-out;
}
#confirmModal.hidden .bg-white {
    transform: scale(0.95);
}
</style>

<!-- JavaScript for Modal Behavior -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('confirmModal');
    const cancelBtn = document.getElementById('cancelDelete');
    const confirmBtn = document.getElementById('confirmDelete');
    let targetForm = null;

    // Intercept delete form submissions
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            targetForm = form;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    // Helper function to close modal
    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        targetForm = null;
    };

    // Cancel delete
    cancelBtn.addEventListener('click', closeModal);

    // Confirm delete
    confirmBtn.addEventListener('click', () => {
        if (targetForm) {
            closeModal();
            targetForm.submit();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // Close modal if clicked outside the dialog
    modal.addEventListener('click', e => {
        if (e.target === modal) {
            closeModal();
        }
    });
});
</script>
@endsection
