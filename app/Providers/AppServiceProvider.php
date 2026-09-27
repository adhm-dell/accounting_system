<?php

namespace App\Providers;

use App\Models\CapitalExpense;
use App\Models\DailyEnvelope;
use App\Models\DebtPayment;
use App\Models\OwnerWithdrawal;
use App\Observers\CapitalExpenseObserver;
use App\Observers\DailyEnvelopeObserver;
use App\Observers\DebtPaymentObserver;
use App\Observers\OwnerWithdrawalObserver;
use App\Services\DebtService;
use App\Services\NotificationService;
use App\Services\SafeBalanceService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SafeBalanceService::class);
        $this->app->singleton(DebtService::class);
        $this->app->singleton(NotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        DailyEnvelope::observe(DailyEnvelopeObserver::class);
        OwnerWithdrawal::observe(OwnerWithdrawalObserver::class);
        CapitalExpense::observe(CapitalExpenseObserver::class);
        DebtPayment::observe(DebtPaymentObserver::class);

        // Allow all actions for the local app
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return true;
        });

        // Failsafe: Ensure admin user exists for fresh installations
        try {
            if (\App\Models\User::count() === 0) {
                \App\Models\User::create([
                    'name' => 'Admin',
                    'email' => 'admin@admin.com',
                    'password' => 'adminadmin',
                    'role' => 'owner_admin',
                    'is_active' => true,
                ]);
            }
        } catch (\Exception $e) {
            // Ignore if tables don't exist yet
        }
    }
}
