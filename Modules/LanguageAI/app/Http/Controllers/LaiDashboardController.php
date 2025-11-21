<?php

namespace Modules\LanguageAI\Http\Controllers;

use App\Classes\Breadcrumbs;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\MongoDB\ChatUsage;
use App\Models\MongoDB\MasterPlan;
use App\Models\MongoDB\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LaiDashboardController extends Controller
{
    private string $url = '/language-ai/dashboard';

    private function defaultParser(): array
    {
        return [
            'url'  => $this->url,
            'view' => 'languageai::dashboard',
        ];
    }

    public function index()
    {
        Gate::authorize(Permission::LANGUAGE_AI_DASHBOARD_VIEW);

        $breadcrumbs = [
            new Breadcrumbs('Language AI', '/language-ai'),
            new Breadcrumbs('Dashboard', $this->url),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
        ]);

        return view('languageai::dashboard.index')->with($parser);
    }

    public function ajaxMetrics(): JsonResponse
    {
        Gate::authorize(Permission::LANGUAGE_AI_DASHBOARD_VIEW);

        $totalUsers = User::count();

        // Assuming 'active' subscriptions means users with a plan_id that is not null and potentially checking subscription status if available
        // For now, let's count users with a plan_id
        $activeSubscriptions = User::whereNotNull('plan_id')->count();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        $totalUsage = ChatUsage::whereBetween('date', [$startOfMonth, $endOfMonth])->sum('usage');

        return response()->json([
            'total_users'          => $totalUsers,
            'active_subscriptions' => $activeSubscriptions,
            'total_usage'          => (int) $totalUsage,
        ]);
    }

    public function ajaxChartData(Request $request): JsonResponse
    {
        Gate::authorize(Permission::LANGUAGE_AI_DASHBOARD_VIEW);

        $endDate   = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays(29)->startOfDay();

        $chatUsages = ChatUsage::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->date)->format('Y-m-d');
            });

        $period = CarbonPeriod::create($startDate, $endDate);
        $data   = [];

        foreach ($period as $date) {
            $dateKey  = $date->format('Y-m-d');
            $dayUsage = isset($chatUsages[$dateKey]) ? $chatUsages[$dateKey]->sum('usage') : 0;

            $data[] = [
                'date'  => $dateKey,
                'value' => (int) $dayUsage,
            ];
        }

        return response()->json($data);
    }
}
