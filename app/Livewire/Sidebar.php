<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Sidebar extends Component
{
    public string $active = 'dashboard';

    public function render()
    {
        $user = Auth::user();
        return view('livewire.sidebar', compact('user'));
    }
}
