<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FinanceService;
use App\Repositories\FinanceRepository;

class FinanceServiceProvider extends ServiceProvider
{
    public function generate_invoice()
    {
        $this->app->bind(FinanceService::class, function ($app) {
            $repository = $app->make(FinanceRepository::class);
            return new FinanceService($repository);
        });
        
        $this->app->bind(FinanceRepository::class, function ($app) {
            $connection = $app->make('db.finance');
            return new FinanceRepository($connection);
        });
    }
}