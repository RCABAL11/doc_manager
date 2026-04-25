<?php

namespace App\Filament\Resources\People\PersonResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    protected static ?string $recordTitleAttribute = 'original_filename';
    
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('original_filename')->label('File Name')->searchable()->sortable(),
                TextColumn::make('created_at')->label('Uploaded')->dateTime()->sortable(),
                TextColumn::make('page_count')
                    ->label('Pages')
                    ->formatStateUsing(fn ($record) => $record->getPageCount())
                    ->sortable(),
            ])
            ->paginated([10, 25, 50])
            ->deferLoading()
            ->filters([
                \Filament\Tables\Filters\Filter::make('original_filename')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('original_filename')
                            ->placeholder('Search files...'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query->when(
                            $data['original_filename'],
                            fn (\Illuminate\Database\Eloquent\Builder $query, $search) => $query->where('original_filename', 'like', "%{$search}%")
                        );
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('file')
                            ->label('Upload Document')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('local')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('custom_filename')
                            ->label('File Name')
                            ->placeholder('Enter a name for this document')
                            ->helperText('The .pdf extension will be added automatically')
                            ->required(),
                    ])
                    ->using(function (array $data, $livewire) {
                        $person = $livewire->getOwnerRecord();
                        
                        if (!isset($data['file']) || !$data['file']) {
                            throw new \Exception('No file uploaded');
                        }
                        
                        if (!isset($data['custom_filename']) || !$data['custom_filename']) {
                            throw new \Exception('Please enter a filename');
                        }
                        
                        $filename = $data['file'];
                        if (is_array($filename)) {
                            $filename = $filename[0];
                        }
                        
                        // FileUpload stores files in private disk by default
                        $tempPath = storage_path('app/private/' . $filename);
                        
                        \Log::info('File at temp location: ' . $tempPath);
                        
                        if (!file_exists($tempPath)) {
                            throw new \Exception('Uploaded file not found at ' . $tempPath);
                        }
                        
                        // Use custom filename provided by user
                        $customFilename = trim($data['custom_filename']);
                        // Remove .pdf if user already added it
                        if (str_ends_with(strtolower($customFilename), '.pdf')) {
                            $customFilename = substr($customFilename, 0, -4);
                        }
                        // Sanitize filename
                        $customFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $customFilename);
                        $savedName = $customFilename . '.pdf';
                        
                        $finalRelativePath = $person->folder_path . '/' . $savedName;
                        $finalPath = storage_path('app/' . $finalRelativePath);
                        
                        // Ensure destination directory exists
                        $destDir = dirname($finalPath);
                        if (!is_dir($destDir)) {
                            mkdir($destDir, 0755, true);
                        }
                        
                        // Check if file already exists
                        if (file_exists($finalPath)) {
                            throw new \Exception('A file with this name already exists. Please choose a different name.');
                        }
                        
                        // Copy the file (move can fail with cross-disk issues on Windows)
                        if (!copy($tempPath, $finalPath)) {
                            throw new \Exception('Failed to copy file to ' . $finalPath);
                        }
                        
                        // Delete the temp file
                        unlink($tempPath);
                        
                        if (!file_exists($finalPath)) {
                            throw new \Exception('File does not exist after copy to ' . $finalPath);
                        }
                        
                        \Log::info('File successfully copied to: ' . $finalPath);
                        
                        // Get file size
                        $size = filesize($finalPath);
                        
                        // Parse PDF to get page count
                        $pageCount = 0;
                        try {
                            $parser = new \Smalot\PdfParser\Parser();
                            $pdf = $parser->parseFile($finalPath);
                            $pages = $pdf->getPages();
                            $pageCount = count($pages);
                            \Log::info("PDF parsed: $pageCount pages");
                        } catch (\Exception $e) {
                            \Log::warning("PDF parse error: " . $e->getMessage());
                        }
                        
                        return Document::create([
                            'person_id' => $person->id,
                            'filename' => $savedName,
                            'original_filename' => $customFilename,
                            'file_path' => $finalRelativePath,
                            'mime_type' => 'application/pdf',
                            'file_size' => $size,
                            'page_count' => $pageCount,
                        ]);
                    }),
                Action::make('scan')
                    ->label('Scan Document')
                    ->icon('heroicon-o-camera')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('filename')
                            ->label('Save as')
                            ->required()
                            ->default('scanned_' . date('Y-m-d_His') . '.pdf'),
                    ])
                    ->action(function (array $data, $livewire) {
                        session(['scan_filename' => $data['filename']]);
                        session(['scan_person_id' => $livewire->getOwnerRecord()->id]);
                        return redirect()->route('scan.form');
                    }),
            ])
            ->actions([
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Document $record) => route('document.preview', $record))
                    ->openUrlInNewTab(),

                Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->modalHeading('Print Document')
                    ->form([
                        \Filament\Forms\Components\Toggle::make('print_all_pages')
                            ->label('Print all pages')
                            ->helperText('Enable to print all pages, or disable to select specific pages')
                            ->default(true)
                            ->inline(),
                        \Filament\Forms\Components\Select::make('pages')
                            ->label('Pages to Print')
                            ->multiple()
                            ->options(function (Document $record) {
                                $totalPages = $record->getPageCount();
                                $options = [];
                                for ($i = 1; $i <= $totalPages; $i++) {
                                    $options[$i] = "Page {$i}";
                                }
                                return $options;
                            })
                            ->hidden(fn ($get) => $get('print_all_pages'))
                            ->required(fn ($get) => !$get('print_all_pages')),

                    
                    ])
                    ->action(function (array $data, Document $record) {
                        // If printing all pages, get all page numbers
                        if ($data['print_all_pages']) {
                            $totalPages = $record->getPageCount();
                            $pages = range(1, $totalPages);
                            $pagesStr = implode(',', $pages);
                        } else {
                            $pagesStr = implode(',', $data['pages']);
                        }

                        return redirect()->route('document.print', [
                            'document' => $record->id,
                            'pages' => $pagesStr
                        ]);
                    }),

                DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Document $record) {
                        // Delete the file from storage
                        if (file_exists(storage_path('app/' . $record->file_path))) {
                            unlink(storage_path('app/' . $record->file_path));
                            \Log::info('File deleted: ' . $record->file_path);
                        }
                        // Delete the database record
                        $record->delete();
                    }),
            ]);
    }

}
