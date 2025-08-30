<x-filament-panels::page>
    <div class="space-y-4">
        
        {{ $this->table }}
        
    </div>

    @push('scripts')
<script>
    document.addEventListener('livewire:load', () => {
        Livewire.hook('message.processed', (message, component) => {
            const groupFilter = document.querySelector('[name="tableFilters.group_id"]');
            if (groupFilter) {
                groupFilter.addEventListener('change', function () {
                    const selectedGroup = this.value;
                    const url = new URL(window.location.href);
                    url.searchParams.set('group_id', selectedGroup);
                    window.location.href = url.toString();
                }, { once: true });
            }
        });
    });
</script>
@endpush
</x-filament-panels::page>
