import React, { createContext, useState, useContext } from 'react';

export const CartContext = createContext();

export const useCart = () => useContext(CartContext);

export const CartProvider = ({ children }) => {
  const [cart, setCart] = useState({});

  const addToCart = (product, variant = null) => {
    const key = variant ? `${product.id}-${variant.unit_type}` : `${product.id}`;
    const existing = cart[key];
    const newItem = {
      name: product.name,
      price: variant ? variant.price : product.sale,
      quantity: existing ? existing.quantity + 1 : 1,
      ...(variant ? { variant: variant.unit_type, unit_qty: variant.unit_qty } : {})
    };
    setCart(prev => ({ ...prev, [key]: newItem }));
  };

  const removeFromCart = (key) => {
    setCart(prev => {
      const newCart = { ...prev };
      delete newCart[key];
      return newCart;
    });
  };

  const clearCart = () => {
    setCart({});
  };

  const getTotal = () => {
    return Object.values(cart).reduce((sum, item) => sum + item.price * item.quantity, 0);
  };

  return (
    <CartContext.Provider value={{ cart, addToCart, removeFromCart, clearCart, getTotal }}>
      {children}
    </CartContext.Provider>
  );
};
