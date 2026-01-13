<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Dashboard Services
        $this->app->bind(\App\Services\Dashboard\DashboardService::class);
        $this->app->bind(\App\Services\Dashboard\DashboardCacheService::class);
        $this->app->bind(\App\Services\Dashboard\DashboardStatsService::class);
        $this->app->bind(\App\Services\Dashboard\DashboardChartService::class);
        
        // Rating Services
        $this->app->bind(\App\Services\Rating\RatingService::class);
        $this->app->bind(\App\Services\Rating\RatingFilterService::class);
        $this->app->bind(\App\Services\Rating\RatingModerationService::class);
        $this->app->bind(\App\Services\Rating\RatingStatisticsService::class);
        $this->app->bind(\App\Services\Rating\RatingBulkActionService::class);
        
        // Report Services
        $this->app->bind(\App\Services\Report\ReportService::class);
        $this->app->bind(\App\Services\Report\ReportFilterService::class);
        $this->app->bind(\App\Services\Report\ReportStatisticsService::class);
        $this->app->bind(\App\Services\Report\ReportBulkActionService::class);
        $this->app->bind(\App\Services\Report\ReportExportService::class);
        
        // Unit Services
        $this->app->bind(\App\Services\Unit\UnitService::class);
        $this->app->bind(\App\Services\Unit\UnitFilterService::class);
        $this->app->bind(\App\Services\Unit\UnitStatisticsService::class);
        $this->app->bind(\App\Services\Unit\UnitBulkActionService::class);
        $this->app->bind(\App\Services\Unit\UnitExportService::class);
        $this->app->bind(\App\Services\Unit\UnitPhotoService::class);
        
        // Logging Services
        $this->app->bind(\App\Services\Logging\AdminActionLogger::class);
        
        // Cache Services (if needed)
        $this->app->bind(\App\Services\Cache\DashboardCacheManager::class);
    }
}