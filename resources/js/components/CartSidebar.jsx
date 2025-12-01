import React, { useContext } from 'react';
import { CartContext } from './contexts/CartContext';

const CartSidebar = () => {
  const { cart, clearCart, removeFromCart, getTotal } = useContext(CartContext);

  return (
    <div className="w-full lg:w-2/5 h-screen bg-white shadow-lg p-4 overflow-y-auto">
      <h2 className="text-lg font-bold mb-4">Shopping Cart ({Object.keys(cart).length} items)</h2>
      {Object.keys(cart).length === 0 ? (
        <div className="text-gray-500 text-center">Your cart is empty</div>
      ) : (
        <>
          {Object.entries(cart).map(([key, item]) => (
            <div key={key} className="flex justify-between items-center mb-2">
              <div>
                <p className="font-medium">{item.name}</p>
                <p className="text-sm text-gray-500">Qty: {item.quantity} @ ₦{item.price}</p>
              </div>
              <button onClick={() => removeFromCart(key)} className="text-red-500 text-sm">Remove</button>
            </div>
          ))}
          <div className="mt-4">
            <p className="font-bold">Total: ₦{getTotal().toFixed(2)}</p>
            <button onClick={clearCart} className="mt-2 px-4 py-2 bg-red-500 text-white rounded">Clear Cart</button>
          </div>
        </>
      )}
    </div>
  );
};

export default CartSidebar;
