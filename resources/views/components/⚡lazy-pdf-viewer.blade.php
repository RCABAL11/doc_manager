<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold">PDF Viewer</h3>
            <div class="flex gap-2">
                <button wire:click="previousPage" class="px-3 py-1 bg-gray-500 text-white rounded">
                    Previous
                </button>
                <span>Page {{ $currentPage }} of {{ $totalPages }}</span>
                <button wire:click="nextPage" class="px-3 py-1 bg-gray-500 text-white rounded">
                    Next
                </button>
            </div>
        </div>
        
        <div class="border rounded-lg p-4">
            <iframe 
                src="{{ route('document.page', ['document' => $document->id, 'page' => $currentPage]) }}"
                class="w-full h-[600px]"
                loading="lazy">
            </iframe>
        </div>
    </div>
</div>