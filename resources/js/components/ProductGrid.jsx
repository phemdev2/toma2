import React, { useContext, useState } from 'react';
import { CartContext } from './contexts/CartContext';

const ProductGrid = () => {
  const { addToCart } = useContext(CartContext);
  const [query, setQuery] = useState('');
  const products = []; // Replace with actual data or props

  const filtered = products.filter(p =>
    p.name.toLowerCase().includes(query.toLowerCase()) ||
    (p.barcode && p.barcode.includes(query))
  );

  return (
    <div className="flex-1 p-4 overflow-y-auto">
      <input
        type="text"
        placeholder="Search products"
        className="mb-4 p-2 w-full border rounded"
        value={query}
        onChange={e => setQuery(e.target.value)}
      />
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        {filtered.map(product => (
          <div key={product.id} className="p-4 border rounded shadow-sm">
            <h3 className="font-semibold">{product.name}</h3>
            <p className="text-sm">₦{product.sale}</p>
            <button onClick={() => addToCart(product)} className="mt-2 px-2 py-1 bg-purple-600 text-white rounded text-sm">
              Add to Cart
            </button>
          </div>
        ))}
      </div>
    </div>
  );
};

export default ProductGrid;
