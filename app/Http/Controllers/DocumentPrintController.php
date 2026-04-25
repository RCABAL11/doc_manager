<?php

namespace App\Http\Controllers;

use App\Models\Document;
use setasign\Fpdi\Fpdi;

class DocumentPrintController extends Controller
{
    public function print($document)
    {
        $document = Document::findOrFail($document);
        $pages = explode(',', request('pages'));
        
        // Normalize path separators
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
            \Log::error('Print file not found. Document ID: ' . $document->id);
            abort(404, 'File not found');
        }
        
        try {
            $pdf = new FPDI();
            
            foreach ($pages as $page) {
                $page = intval(trim($page));
                if ($page <= 0) continue;
                
                $pdf->setSourceFile($actualPath);
                $templateId = $pdf->importPage($page);
                $pdf->AddPage();
                $pdf->useTemplate($templateId);
            }
            
            $output = $pdf->Output('S');
            
            return response($output, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="print_selected.pdf"');
        } catch (\Exception $e) {
            // If FPDI fails (e.g., due to compression), fall back to serving the full PDF
            \Log::warning('FPDI could not process PDF (likely due to compression). Document ID: ' . $document->id . '. Error: ' . $e->getMessage());
            
            return response()->file($actualPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $document->original_filename . '.pdf"'
            ]);
        }
    }
}