<?php 

namespace App\Http\Livewire;

use Livewire\Component;

class CartComponent extends Component
{
    public $cart = [];

    protected $listeners = ['updateCart', 'addToCart'];

    public function mount()
    {
        $this->cart = session()->get('cart', []);
    }

    public function updateCart($cart)
    {
        $this->cart = $cart;
        session()->put('cart', $cart);
    }

    public function addToCart($product)
    {
        $cart = session()->get('cart', []);

        $found = false;
        foreach ($cart as &$item) {
            if ($item['name'] === $product['name']) {
                $item['quantity'] += 1;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $product['quantity'] = 1;
            $cart[] = $product;
        }

        session()->put('cart', $cart);
        $this->cart = $cart;
    }

    public function render()
    {
        return view('livewire.cart-component');
    }
}
