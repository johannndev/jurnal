<div class="flex items-center gap-x-2 px-3">
    <span class="text-sm font-semibold text-gray-700 dark:text-white">
        Groups:
    </span>
    <select 
        wire:model.live="activeGroupId"
        class="bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 block w-full p-1.5 transition duration-75"
    >
        @foreach($groups as $group)
            <option value="{{ $group->id }}" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                {{ $group->name }}
            </option>
        @endforeach
    </select>
</div>
