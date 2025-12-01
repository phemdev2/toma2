<template>
  <div class="bg-white shadow rounded-lg p-4">
    <h2 class="font-bold mb-2">Cart</h2>
    <div v-if="items.length">
      <div
        v-for="item in items"
        :key="item.id"
        class="flex justify-between items-center mb-2"
      >
        <div>
          <p>{{ item.name }}</p>
          <p class="text-sm text-gray-600">₦{{ (item.price * item.quantity).toFixed(2) }}</p>
        </div>
        <div class="flex items-center">
          <input
            type="number"
            v-model.number="item.quantity"
            @change="updateQuantity(item)"
            min="1"
            class="border rounded w-16 text-sm text-center"
          />
          <button @click="remove(item)" class="ml-2 text-red-500">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
      <div class="font-bold text-lg mt-4">Total: ₦{{ total.toFixed(2) }}</div>
    </div>
    <p v-else>Your cart is empty.</p>
  </div>
</template>

<script>
export default {
  props: ['cart'],
  computed: {
    items() {
      return Object.values(this.cart);
    },
    total() {
      return this.items.reduce((sum, i) => sum + i.price * i.quantity, 0);
    },
  },
  methods: {
    updateQuantity(item) {
      this.$emit('update-quantity', item);
    },
    remove(item) {
      this.$emit('remove-item', item);
    },
  },
};
</script>
