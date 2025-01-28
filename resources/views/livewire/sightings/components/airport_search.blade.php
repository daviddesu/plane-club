<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Modelable;
use App\Models\Airport;

new class extends Component{

    #[Modelable]
    public ?string $airport = null;
    public array $airports;
    public string $label;

    public function mount(string $label)
    {
        $this->search();
        $this->label = $label;
    }

    public function search(string $value = '')
    {
        if(empty($value)){
            $airports = Airport::where('featured', 1)->get();
        }else{
            $searchTerms = explode(' ', strtolower($value));

            $airports = DB::table('airports')
                ->where(function($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->where(function($q) use ($term) {
                            $q->whereRaw("LOWER(name) LIKE ?", ["% {$term}%"])
                            ->orWhereRaw("LOWER(name) LIKE ?", ["{$term}%"])
                            ->orWhereRaw("LOWER(iata_code) LIKE ?", ["{$term}%"]);
                        });
                    }
                })
                ->get();
        }

        if($this->airport){
            $selectedAirport = Airport::where('id', $this->airport)->get();
            $airports->merge($selectedAirport);
        }

        $this->airports = array_map(function ($airport) {
                return ['id' => $airport->id, 'name' => "$airport->name ($airport->iata_code)"];
            },
            $airports->all()
        );
    }

}

?>

<div>
<x-mary-choices
    label="{{ $label }}"
    wire:model='airport'
    placeholder="Search airport or IATA code"
    :options="$airports"
    search-function="search"
    debounce="300ms"
    min-chars="3"
    single
    searchable
/>
</div>
