<?php

namespace App\Livewire;

use Livewire\Component;

class Navbar extends Component
{
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->dispatch('searchUpdated', $this->search);
    }

    public function render()
    {
        return view('livewire.navbar');
    }
}
