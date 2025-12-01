<template>
  <div>
    <input
      v-model="search"
      placeholder="Search..."
      class="border rounded px-2 py-1 text-sm mb-4 w-full"
    />
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div
        v-for="product in filtered"
        :key="product.id"
        class="product-card bg-white shadow hover:scale-105 cursor-pointer p-4"
        @click="add(product)"
      >
        <p class="font-bold text-sm">{{ product.name }}</p>
        <p class="text-xs text-gray-500 mb-2">Sale: ₦{{ product.sale.toFixed(2) }}</p>
        <button
          @click.stop="add(product)"
          class="bg-purple-500 text-white rounded px-3 py-1 text-xs"
          aria-label="Add to Cart"
        >
          Add to Cart
        </button>
      </div>
    </div>
    <!-- Load more button if needed -->
  </div>
</template>

<script>
export default {
  props: ['products'],
  data() {
    return { search: '' };
  },
  computed: {
    filtered() {
      return this.products.filter(p =>
        p.name.toLowerCase().includes(this.search.toLowerCase())
      );
    },
  },
  methods: {
    add(product) {
      this.$emit('add-to-cart', product);
    },
  },
};
</script>
