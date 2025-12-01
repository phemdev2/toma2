export default function registerCart() {
  const CART_KEY = 'pos_cart';
  const loadCart = () => { try { return JSON.parse(localStorage.getItem(CART_KEY)) || {}; } catch { return {}; } };

  Alpine.store('cart', {
    items: loadCart(),
    save() { localStorage.setItem(CART_KEY, JSON.stringify(this.items)); this.updateBadge(); },
    clear() { this.items = {}; this.save(); },
    updateBadge() {
      const count = Object.keys(this.items).length;
      document.title = count > 0 ? `(${count}) POS` : 'Modern POS System';
    },
    total() { return Object.values(this.items).reduce((s,i)=>s+(i.price*i.quantity),0); },
    add(product, variant=null) {
      const v = variant ?? { unit_type: product.unit, price: product.sale, unit_qty: 1 };
      const key = `${product.id}-${v.unit_type ?? 'default'}`;
      const stock = variant ? (Number(variant.stock)||0) : (Number(product.stock)||0);
      const current = this.items[key]?.quantity || 0;
      if(current >= stock && stock>0){
        Alpine.store('toast').show(`Max stock reached (${stock}) for ${product.name}`,'warning',4000);
        return false;
      }
      if(this.items[key]){ this.items[key].quantity++; }
      else{ this.items[key]={...v,name:product.name,product_id:product.id,quantity:1,price:v.price,max_stock:stock}; }
      this.save();
      Alpine.store('toast').show(`${product.name} added to cart`,'success');
      return true;
    },
    remove(key) {
      if(this.items[key]){ const n=this.items[key].name; delete this.items[key]; this.save(); Alpine.store('toast').show(`${n} removed`,'info'); }
    },
    canIncreaseQuantity(key){ const i=this.items[key]; return i && i.quantity < (i.max_stock||Infinity); }
  });

  Alpine.store('cart').updateBadge();
}
