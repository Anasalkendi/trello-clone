<?php

namespace App\Providers;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;
use App\Models\Project;
use App\Policies\BoardListPolicy;
use App\Policies\BoardPolicy;
use App\Policies\CardPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Board::class, BoardPolicy::class);
        Gate::policy(BoardList::class, BoardListPolicy::class);
        Gate::policy(Card::class, CardPolicy::class);

        RateLimiter::for('api', function (Request $request) {
            $identifier = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(120)->by($identifier);
        });
    }
}
