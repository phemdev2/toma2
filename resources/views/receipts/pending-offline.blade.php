<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pending Offline Receipt</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    @media print {
      .no-print { display: none; }
      body { margin: 0; font-size: 14px; }
      .receipt { width: 100%; max-width: 100%; }
    }
  </style>
</head>
<body class="bg-gray-100 p-4">

<div class="receipt mx-auto bg-white p-4 border rounded-lg shadow-md sm:w-80 md:w-96 lg:w-1/2">
  <!-- Store Info -->
  <div class="text-center mb-3">
    <h1 class="text-lg font-bold">[Store Name]</h1>
    <p class="text-sm">Tel: [Store Phone]</p>
    <p class="text-sm">Email: [Store Email]</p>
    <h3 class="text-base mt-2">Pending Receipt</h3>
    <p class="text-xs text-red-500 font-semibold">⚠ OFFLINE ORDER - TO BE SYNCED</p>
  </div>

  <!-- Order Info -->
  <div class="mb-3 text-sm">
    <p><strong>Order ID:</strong> Offline-<span id="orderId"></span></p>
    <p><strong>Date:</strong> <span id="orderDate"></span></p>
    <p><strong>Payment:</strong> <span id="paymentMethod"></span></p>
  </div>

  <!-- Items -->
  <table class="w-full border-collapse mb-4 text-sm">
    <thead>
      <tr>
        <th class="border-b text-left p-1">Item</th>
        <th class="border-b text-right p-1">Qty</th>
        <th class="border-b text-right p-1">Price</th>
        <th class="border-b text-right p-1">Total</th>
      </tr>
    </thead>
    <tbody id="itemsTable"></tbody>
  </table>

  <!-- Total -->
  <div class="text-right font-bold text-base mb-4">
    Total: ₦<span id="grandTotal"></span>
  </div>

  <!-- Footer -->
  <div class="text-center text-sm text-gray-600">
    <p>Thank you for your purchase!</p>
    <p>Please keep this receipt — your order will sync when online.</p>
  </div>

  <!-- Buttons -->
  <div class="flex justify-end space-x-4 mt-6 no-print">
    <button onclick="window.print()" class="px-4 py-2 bg-green-500 text-white rounded">Print</button>
    <button onclick="window.history.back()" class="px-4 py-2 bg-blue-500 text-white rounded">New Order</button>
  </div>
</div>

<script>
// Load last offline order from localStorage
const offlineOrders = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
const lastOrder = offlineOrders[offlineOrders.length - 1];

if (lastOrder) {
  document.getElementById('orderId').textContent = lastOrder.id;
  document.getElementById('orderDate').textContent = new Date(lastOrder.created_at).toLocaleString();
  document.getElementById('paymentMethod').textContent = lastOrder.paymentMethod;

  const itemsTable = document.getElementById('itemsTable');
  let total = 0;
  lastOrder.cart.forEach(item => {
    const row = document.createElement('tr');
    const lineTotal = Number(item.price) * Number(item.quantity);
    total += lineTotal;
    row.innerHTML = `
      <td class="p-1">${item.name}</td>
      <td class="p-1 text-right">${item.quantity}</td>
      <td class="p-1 text-right">₦${Number(item.price).toFixed(2)}</td>
      <td class="p-1 text-right">₦${lineTotal.toFixed(2)}</td>
    `;
    itemsTable.appendChild(row);
  });
  document.getElementById('grandTotal').textContent = total.toFixed(2);
}
</script>

</body>
</html>
