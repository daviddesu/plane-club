<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Modelable;
use App\Models\Airline;

new class extends Component{

    #[Modelable]
    public ?string $airline;
    public array $airlines;

    public function mount()
    {
        $this->search();
    }

    public function search(string $value = '')
    {
        if(empty($value)){
            $airlines = Airline::where('featured', 1)->get();
        }else{
            $searchTerms = explode(' ', strtolower($value));

            $airlines = DB::table('airlines')
                ->where(function($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->where(function($q) use ($term) {
                            $q->whereRaw("LOWER(name) LIKE ?", ["% {$term}%"])
                            ->orWhereRaw("LOWER(name) LIKE ?", ["{$term}%"]);
                        });
                    }
                })
                ->get();
        }

        if($this->airline){
            $selectedAirline = Airline::where('id', $this->airline)->get();
            $airlines->merge($selectedAirline);
        }

        $this->airlines = array_map(function ($airline) {
            return ['id' => $airline->id, 'name' => $airline->name];
        }, $airlines->all());
    }

}

?>

<div>
<x-mary-choices
    label="Airline"
    wire:model='airline'
    placeholder="Search Airline"
    :options="$airlines"
    search-function="search"
    debounce="300ms"
    min-chars="3"
    single
    searchable
/>
</div>
