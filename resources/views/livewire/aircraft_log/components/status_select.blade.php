<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Modelable;
use App\Enums\FlyingStatus;

new class extends Component{

    #[Modelable]
    public ?string $status;
    public array $statuses;

    public function mount()
    {
        $this->statuses = [
            [
                "id" => FlyingStatus::DEPARTING->value,
                "name" => FlyingStatus::getNameByStatus(FlyingStatus::DEPARTING->value),
            ],
            [
                "id" => FlyingStatus::ARRIVING->value,
                "name" => FlyingStatus::getNameByStatus(FlyingStatus::ARRIVING->value),
            ],
            [
                "id" => FlyingStatus::IN_FLIGHT->value,
                "name" => FlyingStatus::getNameByStatus(FlyingStatus::IN_FLIGHT->value),
            ],
            [
                "id" => FlyingStatus::ON_STAND->value,
                "name" => FlyingStatus::getNameByStatus(FlyingStatus::ON_STAND->value),
            ],
            [
                "id" => FlyingStatus::TAXIING->value,
                "name" => FlyingStatus::getNameByStatus(FlyingStatus::TAXIING->value),
            ],
        ];
    }
}

?>

<div>
    <x-mary-select label="Status" :options="$statuses" wire:model="status" placeholder="Select a status" />
</div>
