<?php

namespace App\Livewire;

use App\Models\Document;
use Livewire\Component;

class LazyPdfViewer extends Component
{
    public Document $document;
    public int $currentPage = 1;
    public int $totalPages = 0;
    
    public function mount()
    {
        $this->totalPages = $this->document->getPageCount();
    }
    
    public function nextPage()
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
        }
    }
    
    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }
    
    public function render()
    {
        return view('components.lazy-pdf-viewer');
    }
}