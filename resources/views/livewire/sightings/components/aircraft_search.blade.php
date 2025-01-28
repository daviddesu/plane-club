<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Modelable;
use App\Models\Aircraft;

new class extends Component{

    #[Modelable]
    public ?string $aircraft = null;
    public array $aircrafts;

    public function mount()
    {
        $this->search();
    }

    public function search(string $value = '')
    {
        if(empty($value)){
            $aircraft = Aircraft::where('featured', 1)->get();
        }else{
            $searchTerms = explode(' ', strtolower($value));

            $aircraft = Aircraft::where(function($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->where(function($q) use ($term) {
                        $q->whereRaw("LOWER(manufacturer) LIKE ?", ["% {$term}%"])
                            ->orWhereRaw("LOWER(manufacturer) LIKE ?", ["{$term}%"])
                            ->orWhereRaw("LOWER(model) LIKE ?", ["% {$term}%"])
                            ->orWhereRaw("LOWER(model) LIKE ?", ["{$term}%"])
                            ->orWhereRaw("LOWER(varient) LIKE ?", ["% {$term}%"])
                            ->orWhereRaw("LOWER(varient) LIKE ?", ["{$term}%"]);
                    });
                }
            })
            ->get();
        }

        if($this->aircraft){
            $selectedAircraft = Aircraft::where('id', $this->aircraft)->get();
            $aircraft->merge($selectedAircraft);
        }

        $this->aircrafts = array_map(function ($plane) {
            return ['id' => $plane->id, 'name' => $plane->getFormattedName()];
        },
        $aircraft->all());
    }

}

?>

<div>
<x-mary-choices
    label="Aircraft"
    wire:model='aircraft'
    placeholder="Search Aircraft"
    :options="$aircrafts"
    search-function="search"
    debounce="300ms"
    min-chars="3"
    single
    searchable
/>
</div>
