export default function CartSidebar() {
  return {
    activeIndex:0,
    get cart(){ return Alpine.store('cart').items; },
    get keys(){ return Object.keys(this.cart); },
    cartTotal(){ return Alpine.store('cart').total(); },
    saveCart(){ Alpine.store('cart').save(); },
    removeItem(k){ Alpine.store('cart').remove(k); },
    clearCart(){ if(confirm('Clear cart?')) Alpine.store('cart').clear(); },

    // Checkout logic
    checkout(method){
      const cart=Alpine.store('cart').items;
      if(!Object.keys(cart).length){ Alpine.store('toast').show('Cart empty','error'); return; }
      const payload={id:'POS-'+Date.now(),cart:Object.values(cart),paymentMethod:method,total:this.cartTotal(),created_at:new Date().toISOString()};
      // (same as before: handle offline/online checkout...)
    },

    moveSelection(d){ if(!this.keys.length) return; this.activeIndex=(this.activeIndex+d+this.keys.length)%this.keys.length; },
    removeActive(){ const k=this.keys[this.activeIndex]; if(k) this.removeItem(k); },
    increaseQty(){ const k=this.keys[this.activeIndex]; if(k&&Alpine.store('cart').canIncreaseQuantity(k)){ this.cart[k].quantity++; this.saveCart(); } },
    decreaseQty(){ const k=this.keys[this.activeIndex]; if(k&&this.cart[k].quantity>1){ this.cart[k].quantity--; this.saveCart(); } }
  }
}
