<?php

namespace App\Livewire;

use Livewire\Component;

class FilterDashboardComponent extends Component
{
    public $yearAlert;
    public $monthAlert;

    public function mount(){
        $this->yearAlert = 'all';
        $this->monthAlert = 'all';
    }

    public function filter(){
        $this->dispatch('filterYear', year: $this->yearAlert, month: $this->monthAlert);
    }
    public function render()
    {
        return view('livewire.filter-dashboard-component');
    }
}
