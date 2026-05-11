<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class UsersComponent extends Component
{
    use WithPagination;
    public $deleteName, $deleteID, $deleter;
    public $dataField = 'name', $dataOrder = 'asc', $paginate = 10, $search = '';

    public function updatedSearch(){
        $this->resetPage();
    }
    public function closeDelete(){
        $this->deleter = false;
        $this->deleteName = null;
        $this->deleteID = null;
    }
    public function delete($id){

        //load data to delete function
        $dataDelete = DB::table('users')->where('id', $id)->first();
        $this->deleteName = $dataDelete->name;
        $this->deleteID = $dataDelete->id;

        $this->deleter = true;
    }
    public function deleting($id){
        DB::table('users')->where('id', $id)->delete();

       Toaster::success('Success deleting user');

        $this->closeDelete();
    }
    public function sortingField($field){
        $this->dataField = $field;
        $this->dataOrder = $this->dataOrder == 'asc' ? 'desc' : 'asc';
    }
    public function getDatabase(){
          $sc = '%' . $this->search . '%';
          try {
            return  DB::table('users')
                        ->select('name', 'email','contact', 'role_id', 'id', 'is_active')
                        ->where('name', 'like', $sc)
                        ->orderBy($this->dataField, $this->dataOrder)
                        ->paginate($this->paginate);
        } catch (\Throwable $th) {
            return [];
        }
    }
    public function render()
    {
        $databases = $this->getDatabase();
        return view('livewire.users-component', compact('databases'));
    }
}
