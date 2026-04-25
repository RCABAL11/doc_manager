<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget;
use App\Models\Person;
use App\Models\Document;
use App\Filament\Widgets\DashboardStats;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = -2;

    public function getWidgets(): array
    {
        return [
            DashboardStats::class,
        ];
    }
}
