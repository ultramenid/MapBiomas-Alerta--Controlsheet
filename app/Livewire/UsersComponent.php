<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class UsersComponent extends Component
{
    use WithPagination;

    public $deleteName, $deleteID, $deleter;

    #[Locked]
    public $dataField = 'name';

    public $dataOrder = 'asc', $paginate = 25, $search = '';

    // 'all' or a role_id (0 admin, 1 auditor, 2 validator)
    public $selectRole = 'all';

    // 'all', '1' active, '0' inactive
    public $selectActive = 'all';

    private array $sortColumns = ['name', 'email', 'role_id', 'last_active'];

    public function mount()
    {
        $this->guard();
    }

    // the page route is admin-only, but Livewire actions post to their own endpoint
    protected function guard()
    {
        abort_unless((int) session('role_id') === 0, 403);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectRole()
    {
        $this->resetPage();
    }

    public function updatedSelectActive()
    {
        $this->resetPage();
    }

    public function resetScope()
    {
        $this->search = '';
        $this->selectRole = 'all';
        $this->selectActive = 'all';
        $this->resetPage();
    }

    public function sortingField($field)
    {
        if (! in_array($field, $this->sortColumns, true)) {
            return;
        }
        // same column flips direction; a new column starts ascending
        $this->dataOrder = $this->dataField === $field && $this->dataOrder === 'asc' ? 'desc' : 'asc';
        $this->dataField = $field;
    }

    // rows that reference the user; deleting a user with any would orphan them
    // (every alert list joins users), so those users can only be deactivated
    protected function workCount($id)
    {
        return DB::table('alerts')->where('analisId', $id)->count()
            + DB::table('auditorlog')->where('auditorId', $id)->count();
    }

    public function toggleActive($id)
    {
        $this->guard();
        if ((int) $id === (int) session('id')) {
            Toaster::error('You cannot deactivate your own account');
            return;
        }

        $user = DB::table('users')->where('id', $id)->first(['name', 'is_active']);
        if (! $user) {
            return;
        }

        $active = (int) $user->is_active === 1 ? 0 : 1;
        DB::table('users')->where('id', $id)->update(['is_active' => $active, 'updated_at' => now('Asia/Jakarta')]);
        Toaster::success($user->name.($active ? ' activated' : ' deactivated'));
    }

    public function closeDelete()
    {
        $this->deleter = false;
        $this->deleteName = null;
        $this->deleteID = null;
    }

    public function delete($id)
    {
        $this->guard();
        $dataDelete = DB::table('users')->where('id', $id)->first();
        if (! $dataDelete || (int) $id === (int) session('id') || $this->workCount($id) > 0) {
            Toaster::error('This user has work on record — deactivate instead');
            return;
        }

        $this->deleteName = $dataDelete->name;
        $this->deleteID = $dataDelete->id;
        $this->deleter = true;
    }

    public function deleting($id)
    {
        $this->guard();
        if ((int) $id === (int) session('id') || $this->workCount($id) > 0) {
            abort(403);
        }

        DB::table('users')->where('id', $id)->delete();
        Toaster::success('Success deleting user');
        $this->closeDelete();
    }

    // name/email search only, so the role chips count within the search
    protected function searchedQuery()
    {
        $query = DB::table('users');
        if (filled($this->search)) {
            $sc = '%'.$this->search.'%';
            $query->where(fn ($q) => $q->where('users.name', 'like', $sc)->orWhere('users.email', 'like', $sc));
        }

        return $query;
    }

    public function getDatabase()
    {
        $query = $this->searchedQuery()
            ->select('users.id', 'users.name', 'users.email', 'users.contact', 'users.role_id', 'users.is_active', 'users.created_at', 'users.last_seen_at')
            ->selectSub(DB::table('alerts')->selectRaw('COUNT(*)')->whereColumn('alerts.analisId', 'users.id')->where('alerts.isActive', 1), 'alerts_count')
            ->selectSub(DB::table('auditorlog')->selectRaw('COUNT(*)')->whereColumn('auditorlog.auditorId', 'users.id')->where('auditorlog.ngapain', 'auditing'), 'audits_count')
            ->selectSub(DB::table('auditorlog')->selectRaw('MAX(created_at)')->whereColumn('auditorlog.auditorId', 'users.id'), 'last_work');

        // latest of any request (TrackLastSeen) and logged work; GREATEST() returns NULL if either side is NULL
        $query = DB::query()->fromSub($query, 'users')
            ->select('*')
            ->selectRaw('GREATEST(COALESCE(last_seen_at, last_work), COALESCE(last_work, last_seen_at)) as last_active');

        if ($this->selectRole !== 'all') {
            $query->where('users.role_id', (int) $this->selectRole);
        }
        if ($this->selectActive !== 'all') {
            $query->where('users.is_active', (int) $this->selectActive);
        }

        $field = in_array($this->dataField, $this->sortColumns, true) ? $this->dataField : 'name';
        $order = $this->dataOrder === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($field, $order)->orderBy('users.name')->paginate($this->paginate);
    }

    public function render()
    {
        $databases = $this->getDatabase();

        $roleStats = $this->searchedQuery()
            ->selectRaw('role_id, COUNT(*) as total, SUM(is_active = 1) as active')
            ->groupBy('role_id')
            ->get()
            ->keyBy('role_id');

        return view('livewire.users-component', compact('databases', 'roleStats'));
    }
}
