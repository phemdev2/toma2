import React from 'react';

const Navbar = () => {
  return (
    <nav className="bg-white border-b px-6 py-4 shadow sticky top-0 z-30">
      <div className="flex justify-between items-center">
        <div className="text-lg font-bold text-purple-600">ModernPOS</div>
        <div className="text-sm text-gray-700">React Version</div>
      </div>
    </nav>
  );
};

export default Navbar;
