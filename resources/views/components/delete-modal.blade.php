<!-- Inside your view -->
<x-filament::modal id="delete-confirmation-modal" width="lg" :autofocus="false" alignment="end" :close-button="false">
  <x-slot name="heading">
    Confirm Deletion
  </x-slot>

  <x-slot name="description">
    Are you sure you want to permanently delete this item?
  </x-slot>

  <!-- Modal Footer Actions managed entirely in JS -->
  <x-slot name="footerActions">
    <x-filament::button color="gray" x-on:click="close()">
      Cancel
    </x-filament::button>

    <x-filament::button color="danger" x-on:click="$dispatch('confirmed-instant-delete'); close()">
      Confirm Delete
    </x-filament::button>
  </x-slot>
</x-filament::modal>
