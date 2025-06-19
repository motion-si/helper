<x-filament::page>
    <div class="w-full flex flex-col gap-5">
        <form wire:submit.prevent="search" class="w-full">
            {{ $this->form }}
            <div class="mt-4 w-full flex justify-end">
                <x-filament::button type="submit">{{ __('Search') }}</x-filament::button>
            </div>
        </form>

        <div class="w-full">
            {{ $this->table }}
        </div>
    </div>
</x-filament::page>

