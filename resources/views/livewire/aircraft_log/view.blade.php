<?php
use App\Models\AircraftLog;
use Livewire\Volt\Component;
use Livewire\Attributes\On;

new class extends Component
{
    public int|null $id;

    public AircraftLog|null $aircraftLog;


    #[On('open_aircraft_log')]
    public function getAircraftLog($id): void
    {
        $this->id = $id;
        $this->aircraftLog = AircraftLog::with('user', 'image', 'airport')->where('id', $id)->first();
        $this->dispatch("open_aircraft_log_modal");
    }

    #[On('close_aircraft_log')]
    public function closeLog()
    {
        $this->id = null;
        $this->aircraftLog = null;
    }

}
?>
<div wore:model.change="aircraftLog">

<div x-data="{
    modalOpened: false,
    openModal() {
        this.modalOpened = true;
    },
    modalClose() {
        this.modalOpened = false;
        $wire.dispatch('close_aircraft_log');
    },
}"
@open-modal.window="openModal"
class="w-full h-full select-none">
@script
            <script>
                $wire.on('open_aircraft_log_modal', () => {
                    console.log("event caught");
                    $dispatch('open-modal');
                });
            </script>
        @endscript
<template x-teleport="body">

    <div
            x-show="modalOpened"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-80"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-80"
            x-transition:leave-end="opacity-0"
            class="flex fixed inset-0 z-[99] w-screen h-screen bg-white opacity-96 overflow-scroll"
            @keydown.window.escape="modalClose"
            >
            <button x-on:click="modalClose" class="absolute top-0 right-0 z-30 flex items-center justify-center px-3 py-2 mt-3 mr-3 space-x-1 text-xs font-medium uppercase border rounded-md border-neutral-200 text-neutral-600 hover:bg-neutral-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                <span>Close</span>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-3">

                <div class="relative top-0 bottom-0 right-0 flex-shrink-0 bg-cover md:col-span-2 md:border-r-2 overlow-scroll lg:block">
                    <img
                        class="object-contain object-center w-full h-full bg-opacity-100 cursor-pointer select-none"
                        src="{{ asset('storage/' . $aircraftLog?->image->path) }}"
                        alt=""
                    />
                </div>
                <div class="relative flex flex-wrap items-center w-full h-full px-8 md:col-span-1">

                    <div class="relative w-full max-w-sm mx-auto lg:mb-0">
                        <div class="relative text-center">
                            <div class="flex flex-col mb-6 space-y-2">
                                <h1 class="text-2xl font-semibold tracking-tight">{{ $aircraftLog?->airport->name }}</h1>
                                <p class="text-sm text-neutral-500">{{ $aircraftLog?->user->name }}</p>
                                <p class="text-sm text-neutral-500">{{ $aircraftLog?->logged_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>

</div>

</div>

