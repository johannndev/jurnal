<x-filament-widgets::widget>
    <x-filament::section>
       <div class="text-lg font-semibold">Total Saldo</div>
        <div class="text-3xl font-bold text-primary-600">
            Rp {{ number_format($totalSaldo, 0, ',', '.') }}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
