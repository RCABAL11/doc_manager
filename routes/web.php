<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\DocumentPrintController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/document/preview/{document}', [DocumentController::class, 'preview'])->name('document.preview');

Route::get('/document/print/{document}', [DocumentPrintController::class, 'print'])->name('document.print');

Route::get('/document/page/{document}/{page}', function($document, $page) {
    $doc = App\Models\Document::findOrFail($document);
    $pdf = new \setasign\Fpdi\Fpdi();
    $pdf->setSourceFile(storage_path('app/' . $doc->file_path));
    $templateId = $pdf->importPage($page);
    $pdf->AddPage();
    $pdf->useTemplate($templateId);
    return response($pdf->Output('S'))->header('Content-Type', 'application/pdf');
})->name('document.page');

Route::get('/scan/form', function() {
    return view('scan.form');
})->name('scan.form');
