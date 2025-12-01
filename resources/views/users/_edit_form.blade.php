<form x-ref="editForm" method="POST" :action="updateUrl"
    class="bg-white dark:bg-gray-900 shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
    @csrf
    @method('PUT')

    <!-- Name -->
    <div class="mb-4">
        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Name</label>
        <input type="text" name="name" x-model="form.name"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:outline-none focus:shadow-outline"
            required>
    </div>

    <!-- Email -->
    <div class="mb-4">
        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Email</label>
        <input type="email" name="email" x-model="form.email"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:outline-none focus:shadow-outline"
            required>
    </div>

    <!-- Role -->
    <div class="mb-4">
        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Role</label>
        <select name="role" x-model="form.role"
            class="shadow border rounded w-full py-2 px-3 text-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:outline-none focus:shadow-outline"
            required>
            @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
            @endforeach
        </select>
    </div>

    <!-- Store -->
    <div class="mb-6">
        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Store</label>
        <select name="store_id" x-model="form.store_id"
            class="shadow border rounded w-full py-2 px-3 text-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:outline-none focus:shadow-outline">
            <option value="">Select a store (optional)</option>
            @foreach($stores as $store)
                <option value="{{ $store->id }}">{{ $store->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Submit -->
    <div class="flex justify-end gap-2">
        <button type="button" class="btn btn-secondary" @click="closeEdit()">Cancel</button>
        <button type="submit" class="btn btn-primary">Update User</button>
    </div>
</form>
