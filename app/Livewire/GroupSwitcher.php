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
        // Ambil grup yang saat ini ditandai default di database (tabel groups)
        $defaultGroup = Group::where('is_default', 1)->first();
        $this->activeGroupId = $defaultGroup ? $defaultGroup->id : null;
    }

    public function updatedActiveGroupId($value)
    {
        // Reset semua grup agar tidak default
        \App\Models\Group::query()->update(['is_default' => 0]);

        // Set grup yang dipilih menjadi default di database
        \App\Models\Group::where('id', $value)->update(['is_default' => 1]);
        
        // Refresh halaman
        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        // Tetap tampilkan agar admin bisa ganti-ganti grup kapan saja
        $groups = Group::all();

        return view('livewire.group-switcher', [
            'groups' => $groups,
        ]);
    }
}
