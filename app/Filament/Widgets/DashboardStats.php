<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Person;
use App\Models\Document;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalPeople = Person::count();
        $totalDocuments = Document::count();
        $totalStorageBytes = Document::sum('file_size') ?? 0;
        $totalPages = Document::sum('page_count') ?? 0;
        
        // Format storage size
        $storageMB = number_format($totalStorageBytes / (1024 * 1024), 2);
        
        // Get recent documents count (last 7 days)
        $recentDocuments = Document::where('created_at', '>=', now()->subDays(7))->count();
        
        // Get recent people count (last 7 days)
        $recentPeople = Person::where('created_at', '>=', now()->subDays(7))->count();

        return [
            Stat::make('Total People', $totalPeople)
                ->description('All registered people')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            
            Stat::make('Total Documents', $totalDocuments)
                ->description('PDF files uploaded')
                ->descriptionIcon('heroicon-m-document')
                ->color('success'),
            
            Stat::make('Total Pages', $totalPages)
                ->description('Pages across all documents')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            
            Stat::make('Storage Used', $storageMB . ' MB')
                ->description('Total file size')
                ->descriptionIcon('heroicon-m-server-stack')
                ->color('danger'),
            
            Stat::make('Recent People', $recentPeople)
                ->description('Added in last 7 days')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),
            
            Stat::make('Recent Documents', $recentDocuments)
                ->description('Uploaded in last 7 days')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('success'),
        ];
    }
}
