@extends('layouts.main')

@section('content')
    <div x-data="cartComponent()">
        <table class="min-w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2">Product</th>
                    <th class="border border-gray-300 px-4 py-2">Quantity</th>
                    <th class="border border-gray-300 px-4 py-2">Total</th>
                    <th class="border border-gray-300 px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="item in cart" :key="item.name">
                    <tr>
                        <td class="border border-gray-300 px-2 py-1" x-text="item.name"></td>
                        <td class="border border-gray-300 px-2 py-1" x-text="item.quantity"></td>
                        <td class="border border-gray-300 px-2 py-1" x-text="'₦' + (item.price * item.quantity).toFixed(2)"></td>
                        <td class="border border-gray-300 px-2 py-1">
                            <button @click="removeFromCart(item.name)" class="bg-red-500 text-white px-2 py-1 rounded">
                                Remove
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <p class="text-lg font-bold mt-2">Total: ₦<span x-text="total"></span></p>
    </div>

    <script>
        function cartComponent() {
            return {
                cart: @json($cart),
                get total() {
                    return this.cart.reduce((acc, item) => acc + (item.price * item.quantity), 0).toFixed(2);
                },
                removeFromCart(itemName) {
                    const updatedCart = this.cart.filter(item => item.name !== itemName);
                    Livewire.emit('updateCart', updatedCart);
                }
            };
        }
    </script>
@endsection
