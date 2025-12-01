<!-- resources/views/receipt_form.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-4">

<div class="container mx-auto bg-white p-4 border border-gray-300 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Generate Receipt</h2>

    <form action="{{ route('generate.receipt') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Company Name</label>
            <input type="text" name="companyName" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Phone</label>
            <input type="text" name="companyPhone" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="companyEmail" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Website</label>
            <input type="url" name="companyWebsite" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Thank You Message</label>
            <input type="text" name="thankYouMessage" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Visit Again Message</label>
            <input type="text" name="visitAgainMessage" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded shadow">Generate Receipt</button>
    </form>
</div>

</body>
</html>
