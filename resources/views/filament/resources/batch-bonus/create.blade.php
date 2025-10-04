<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Form Filament --}}
        {{ $this->form }}      

        {{-- Preview hasil cek file --}}
        @if($show)
            <x-filament::card class="bg-yellow-100 border-yellow-400 text-yellow-700">
                <p class="text-sm">Valid Member: {{ $countExists }}</p>
                <p class="text-sm">Invalid Member: {{ $countNotExists }}</p>
             </x-filament::card>
          
       
        <x-filament::button
            type="button"
            wire:click="submitBonus"
            wire:loading.attr="disabled"  {{-- tombol disable saat loading --}}
            wire:target="submitBonus,attachment"     {{-- target loading hanya untuk aksi ini --}}
        >
            <span wire:loading.remove wire:target="submitBonus,attachment">
                Simpan
            </span>
            <span wire:loading wire:target="submitBonus,attachment">
                Memproses...
            </span>
        </x-filament::button>

         @endif


       
    </div>
</x-filament-panels::page>
