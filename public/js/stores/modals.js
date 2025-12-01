export default function registerModals() {
  // Variant modal
  Alpine.store('variantModal',{
    show:false,product:null,selected:0,
    open(p){this.product=p;this.show=true;},close(){this.show=false;},select(i){this.selected=i;},
    add(){const v=this.product.variants[this.selected];Alpine.store('cart').add(this.product,v);this.close();}
  });
  Alpine.data('VariantModal',()=>Alpine.store('variantModal'));

  // Receipt modal
  Alpine.store('receiptModal',{
    show:false,url:null,lastOrderUrl:null,
    open(u){this.url=u;this.show=true;},close(){this.show=false;},
    reprint(){if(this.lastOrderUrl)this.open(this.lastOrderUrl);}
  });
  Alpine.data('ReceiptModal',()=>Alpine.store('receiptModal'));
}
