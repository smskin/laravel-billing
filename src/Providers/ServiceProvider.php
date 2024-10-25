<?php

namespace SMSkin\Billing\Providers;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->registerConfigs();
        $this->registerMigrations();
    }

    protected function registerConfigs()
    {
        $this->publishes([
            __DIR__ . '/../../config/billing.php' => config_path('billing.php'),
        ], 'config');
    }

    protected function registerMigrations()
    {
        if (empty(glob(database_path('migrations/*_create_billing_operations_table.php')))) {
            $this->publishes([
                __DIR__ . '/../../database/migrations/create_billing_operations_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_billing_operations_table.php'),
            ], 'migrations');
        }
    }
}
