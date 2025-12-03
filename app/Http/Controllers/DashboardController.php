<?php

namespace App\Http\Controllers;

use App\Models\DB1\SysUser;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getDashboardStats();
        $recentActivity = $this->getRecentActivity();

        return view('dashboard', compact('stats', 'recentActivity'));
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->getDashboardStats());
    }

    private function getDashboardStats(): array
    {
        // Get basic user statistics
        $totalUsers = SysUser::count();
        $activeUsers = SysUser::where('status', 'active')->count();
        $newUsersThisMonth = SysUser::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthUsers = SysUser::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $userGrowth = $lastMonthUsers > 0
            ? round((($newUsersThisMonth - $lastMonthUsers) / $lastMonthUsers) * 100, 1)
            : 0;

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'new_users_this_month' => $newUsersThisMonth,
            'active_users_growth' => $userGrowth,
            'total_projects' => 0, // Placeholder - implement when projects table exists
            'projects_growth' => 0,
            'completed_tasks' => 0, // Placeholder - implement when tasks table exists
            'tasks_completion_rate' => 0,
        ];
    }

    private function getRecentActivity()
    {
        // Placeholder - implement when activity tracking is available
        // This would typically return recent user activities, logins, etc.
        return collect([]);
    }
}
