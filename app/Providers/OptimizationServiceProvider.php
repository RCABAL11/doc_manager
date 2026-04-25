<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

class OptimizationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Use cursor pagination for large datasets
        Paginator::useBootstrapFive();
        
        // Set memory limit for large files
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
    }
}