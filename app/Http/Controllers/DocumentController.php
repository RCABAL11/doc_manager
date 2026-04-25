<?php

namespace App\Http\Controllers;

use App\Models\Document;

class DocumentController extends Controller
{
    public function preview(Document $document)
    {
        // Normalize path separators to forward slashes
        $filePath = str_replace('\\', '/', $document->file_path);
        
        // Try to find the file in multiple locations
        $possiblePaths = [
            storage_path('app/' . $filePath),  // New location
            storage_path('app/private/' . pathinfo($filePath, PATHINFO_FILENAME)),  // Old temp location
        ];
        
        $actualPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $actualPath = $path;
                break;
            }
        }
        
        if (!$actualPath) {
            \Log::error('Preview file not found. Document ID: ' . $document->id . ', Stored path: ' . $filePath);
            abort(404, 'File not found');
        }
        
        \Log::info('Previewing file: ' . $actualPath);
        
        return response()->file($actualPath);
    }
}