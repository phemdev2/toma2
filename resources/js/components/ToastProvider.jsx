import React, { createContext, useState, useContext } from 'react';

const ToastContext = createContext();

export const useToast = () => useContext(ToastContext);

const ToastProvider = ({ children }) => {
  const [toasts, setToasts] = useState([]);

  const showToast = (message, type = 'success', timeout = 3000) => {
    const id = Date.now();
    const toast = { id, message, type };
    setToasts(prev => [...prev, toast]);
    setTimeout(() => {
      setToasts(prev => prev.filter(t => t.id !== id));
    }, timeout);
  };

  return (
    <ToastContext.Provider value={{ showToast }}>
      {children}
      <div className="fixed bottom-4 right-4 space-y-2 z-50 max-w-sm">
        {toasts.map(toast => (
          <div key={toast.id} className={
            `rounded-xl p-4 text-sm shadow-lg border ${toast.type === 'success' ? 'bg-green-50 border-green-200 text-green-700' : ''}
             ${toast.type === 'error' ? 'bg-red-50 border-red-200 text-red-700' : ''}
             ${toast.type === 'info' ? 'bg-blue-50 border-blue-200 text-blue-700' : ''}
             ${toast.type === 'warning' ? 'bg-yellow-50 border-yellow-200 text-yellow-700' : ''}`
          }>
            {toast.message}
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
};

export default ToastProvider;
