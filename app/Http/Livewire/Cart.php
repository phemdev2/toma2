<?php 
namespace App\Http\Livewire;

use Livewire\Component;

class Cart extends Component
{
    public $cart = [];
    public $total = 0;

    public function mount()
    {
        // Initialize cart and total
        $this->cart = session()->get('cart', []);
        $this->calculateTotal();
    }

    public function addToCart($product)
    {
        // Logic to add product to cart
        // Update cart session
        session()->put('cart', $this->cart);
        $this->calculateTotal();
    }

    public function removeFromCart($index)
    {
        // Logic to remove item from cart
        // Update cart session
        session()->put('cart', $this->cart);
        $this->calculateTotal();
    }

    public function clearCart()
    {
        session()->forget('cart');
        $this->cart = [];
        $this->calculateTotal();
    }

    private function calculateTotal()
    {
        $this->total = array_sum(array_column($this->cart, 'price'));
    }

    public function render()
    {
        return view('livewire.cart');
    }
}
