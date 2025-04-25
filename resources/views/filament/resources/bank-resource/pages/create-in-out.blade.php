<x-filament-panels::page>

<div class="space-y-6">

        <form wire:submit.prevent="save" class="space-y-6">
            {{ $this->form }}
            <div class="mt-4 flex justify-end">
                <x-filament::button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-75">
                    <span wire:loading.remove> Simpan Transaksi </span>
                    <span wire:loading> Menyimpan... </span>
                </x-filament::button>
            </div>
        </form>
      
    </div>
</x-filament-panels::page>
