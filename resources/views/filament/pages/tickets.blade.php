<x-filament::page>
    <div class="w-full flex flex-col gap-5">
        <form wire:submit.prevent class="w-full">
            {{ $this->form }}
        </form>

        <div class="w-full">
            {{ $this->table }}
        </div>
    </div>
</x-filament::page>

