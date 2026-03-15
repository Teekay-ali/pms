<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;
use App\Models\ChangeOrder;
use App\Policies\ChangeOrderPolicy;
use App\Models\Invoice;
use App\Models\Bill;
use App\Models\Payment;
use App\Policies\InvoicePolicy;
use App\Policies\BillPolicy;
use App\Policies\PaymentPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Gate::policy(ChangeOrder::class, ChangeOrderPolicy::class);

        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Bill::class, BillPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);

    }
}
