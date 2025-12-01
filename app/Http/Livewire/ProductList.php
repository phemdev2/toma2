<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Collection;

class ProductList extends Component
{
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 15;
    public $page = 1;
    public $search = '';

    // Holds loaded product data (as arrays, not models)
    public array $products = [];

    public function mount()
    {
        $this->loadProducts();
    }

    protected function loadProducts()
    {
        $query = Product::with('variants')  // eager-load variants
                        ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                        ->orderBy($this->sortField, $this->sortDirection);

        $chunk = $query->forPage($this->page, $this->perPage)->get();

        $arr = $chunk->map(function (Product $p) {
            // Compute profit etc.
            $profit = $p->sale - $p->cost;
            $profitPercent = $p->cost > 0 ? ($profit / $p->cost) * 100 : 0;

            $expiryDate = $p->expiry_date ? \Carbon\Carbon::parse($p->expiry_date) : null;
            $daysUntilExpiry = $expiryDate ? now()->diffInDays($expiryDate, false) : null;

            return [
                'id' => $p->id,
                'name' => $p->name,
                'barcode' => $p->barcode,
                'cost' => $p->cost,
                'sale' => $p->sale,
                'expiry_date' => $p->expiry_date,
                'days_until_expiry' => $daysUntilExpiry,
                'variants' => $p->variants->map(fn($v) => [
                    'unit_type' => $v->unit_type,
                    'unit_qty' => $v->unit_qty,
                    'price' => $v->price,
                ])->toArray(),
                'profit' => $profit,
                'profit_percent' => $profitPercent,
            ];
        })->toArray();

        // Append
        $this->products = array_merge($this->products, $arr);
    }

    public function loadMore()
    {
        $this->page++;
        $this->loadProducts();
    }

    public function updatedSortField()
    {
        $this->resetLoaded();
    }

    public function updatedSortDirection()
    {
        $this->resetLoaded();
    }

    public function updatedSearch()
    {
        $this->resetLoaded();
    }

    protected function resetLoaded()
    {
        $this->products = [];
        $this->page = 1;
        $this->loadProducts();
    }

    public function hasMore()
    {
        $q = Product::when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
        return count($this->products) < $q->count();
    }

    public function render()
    {
        return view('livewire.product-list', [
            'hasMore' => $this->hasMore(),
        ]);
    }
}
