<?php
use Livewire\Volt\Component;

new class extends Component
{
    public int $id;

    public function mount(int $id){
        $this->id = $id;
    }
}

?>

<x-app-layout>
    <div class="p-4 mx-auto sm:p-6 lg:p-8">
        <livewire:aircraft_log.view :id='$id'/>
    </div>
</x-app-layout>
