<?php

namespace App\Livewire;

use App\Models\Group;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class GroupSwitcher extends Component
{
    public $activeGroupId;

    public function mount()
    {
        $this->activeGroupId = Group::getActiveGroupId();
    }

    public function updatedActiveGroupId($value)
    {
        Session::put('active_group_id', $value);
        
        // Refresh the page to apply changes across all resources
        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        // Only show for admins (group_id == 0)
        if (auth()->user()->group_id > 0) {
            return <<<'blade'
                <div></div>
            blade;
        }

        $groups = Group::all();

        return view('livewire.group-switcher', [
            'groups' => $groups,
        ]);
    }
}
