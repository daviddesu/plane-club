<?php

use Livewire\Volt\Component;

new class extends Component {

    public $paymentMethod;

    public function updatePaymentMethod()
    {
        $user = auth()->user();

        $this->validate([
            'paymentMethod' => 'required',
        ]);

        $user->updateDefaultPaymentMethod($this->paymentMethod);

        session()->flash('message', 'Payment method updated successfully.');
    }
};

?>

<div>
    <h2 class="text-xl font-semibold">Update Payment Method</h2>
    <x-mary-form wire:submit.prevent="updatePaymentMethod" class="mt-4">
        <div>
            <label for="paymentMethod" class="block">Payment Method ID</label>
            <input wire:model="paymentMethod" id="paymentMethod" type="text" class="w-full p-2 border rounded-md">
            @error('paymentMethod') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>
        <x-mary-button type="submit" class="px-4 py-2 mt-4 text-white bg-blue-500 rounded-md">Update Payment Method</x-mary-button>
    </x-mary-form>
</div>

