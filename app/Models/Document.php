<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Smalot\PdfParser\Parser;

class Document extends Model
{
    protected $fillable = [
        'person_id', 'filename', 'original_filename', 
        'file_path', 'mime_type', 'page_count', 'file_size'
    ];
    
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function getPageCount()
    {
        // If page_count is already set and greater than 0, return it
        if ($this->page_count && $this->page_count > 0) {
            return $this->page_count;
        }
        
        try {
            // Normalize path separators
            $filePath = str_replace('\\', '/', $this->file_path);
            
            // Try multiple locations
            $possiblePaths = [
                storage_path('app/' . $filePath),  // New location
                storage_path('app/private/' . pathinfo($filePath, PATHINFO_FILENAME)),  // Old temp location
            ];
            
            $fullPath = null;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $fullPath = $path;
                    break;
                }
            }
            
            if (!$fullPath) {
                \Log::warning('PDF file not found for document ' . $this->id . '. Tried paths: ' . json_encode($possiblePaths));
                return 0;
            }
            
            $parser = new Parser();
            $pdf = $parser->parseFile($fullPath);
            $pages = $pdf->getPages();
            
            if (!$pages || count($pages) === 0) {
                \Log::warning('No pages found in PDF file for document ' . $this->id . ': ' . $fullPath);
                return 0;
            }
            
            $count = count($pages);
            \Log::info('Page count for document ' . $this->id . ': ' . $count);
            
            // Update the record with the page count
            $this->update(['page_count' => $count]);
            return $count;
        } catch (\Exception $e) {
            \Log::error('Error parsing PDF for document ' . $this->id . ': ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
            return 0;
        }
    }
}