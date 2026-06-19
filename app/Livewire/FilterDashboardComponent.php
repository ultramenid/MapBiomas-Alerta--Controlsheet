<?php

namespace App\Livewire;

use Livewire\Component;

class FilterDashboardComponent extends Component
{
    public $yearAlert;

    public function mount(){
        $this->yearAlert = 'all';
    }

    public function filter(){
        $this->dispatch('filterYear', year: $this->yearAlert);
    }
    public function render()
    {
        return view('livewire.filter-dashboard-component');
    }
}
