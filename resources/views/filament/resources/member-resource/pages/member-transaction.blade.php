<x-filament::page>
    <div class="space-y-4">
        
        <!-- Informasi Saldo -->
        <!-- Informasi Saldo -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Total Deposit -->
            <div class="p-4 bg-green-100 text-green-800 rounded-lg shadow">
                <h3 class="text-lg font-semibold">Total Deposit</h3>
                <p class="text-xl font-bold">Rp {{ number_format($this->totalDeposit, 2) }}</p>
            </div>

            <!-- Total Withdraw -->
            <div class="p-4 bg-yellow-100 text-yellow-800 rounded-lg shadow">
                <h3 class="text-lg font-semibold">Total Withdraw</h3>
                <p class="text-xl font-bold">Rp {{ number_format($this->totalWithdraw, 2) }}</p>
            </div>

            <!-- Balance -->
            <div class="p-4 rounded-lg shadow-md text-white"
                style="background-color: {{ $this->balance < 0 ? '#EF4444' : '#10B981' }};">
                <h3 class="text-lg font-semibold">Balance</h3>
                <p class="text-xl font-bold">Rp {{ number_format($this->balance, 2) }}</p>
            </div>
        </div>
        
        {{ $this->table }}
        
    </div>
</x-filament::page>