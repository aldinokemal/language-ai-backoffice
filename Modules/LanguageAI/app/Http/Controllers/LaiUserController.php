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
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use MongoDB\BSON\ObjectId;

class LaiUserController extends Controller
{
    private string $url = '/language-ai/users';

    private function defaultParser(): array
    {
        return [
            'url'  => $this->url,
            'view' => 'languageai::users',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize(Permission::LANGUAGE_AI_USERS_VIEW);

        $breadcrumbs = [
            new Breadcrumbs('Language AI', '/language-ai'),
            new Breadcrumbs('Users', $this->url),
        ];

        $plans = MasterPlan::active()
            ->orderBy('plan_name')
            ->get()
            ->map(fn($plan) => [
                '_id'       => (string) $plan->_id,
                'plan_name' => $plan->plan_name . ' (' . $plan->plan_code . ')',
            ]);

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'plans'       => $plans,
        ]);

        return view('languageai::users.index')->with($parser);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        Gate::authorize(Permission::LANGUAGE_AI_USERS_VIEW);

        $user = User::findOrFail($id);

        $breadcrumbs = [
            new Breadcrumbs('Language AI', '/language-ai'),
            new Breadcrumbs('Users', $this->url),
            new Breadcrumbs('Detail User'),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'data'        => $user,
        ]);

        return view('languageai::users.show')->with($parser);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        Gate::authorize(Permission::LANGUAGE_AI_USERS_UPDATE);

        $user  = User::findOrFail($id);
        $plans = MasterPlan::where('is_active', true)->get();

        $breadcrumbs = [
            new Breadcrumbs('Language AI', '/language-ai'),
            new Breadcrumbs('Users', $this->url),
            new Breadcrumbs('Edit User'),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'user'        => $user,
            'plans'       => $plans,
        ]);

        return view('languageai::users.edit')->with($parser);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        Gate::authorize(Permission::LANGUAGE_AI_USERS_UPDATE);

        $user = User::findOrFail($id);

        $request->validate([
            'name'                            => 'required|string|max:255',
            'email'                           => [
                'required',
                'email',
                function ($attribute, $value, $fail) use ($user) {
                    $existingUser = User::where('email', $value)
                        ->where('_id', '!=', $user->_id)
                        ->first();

                    if ($existingUser) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'plan_id'                         => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && !MasterPlan::where('_id', $value)->exists()) {
                        $fail('The selected plan is invalid.');
                    }
                },
            ],
            'activated_at'                    => 'nullable|boolean',
            'alert_setting.device_login'      => 'nullable|boolean',
            'alert_setting.importance_update' => 'nullable|boolean',
        ]);

        try {
            $user->name    = $request->name;
            $user->email   = $request->email;
            $user->plan_id = $request->plan_id;

            // Handle activated_at toggle - checkbox returns nothing when unchecked
            $user->activated_at = $request->boolean('activated_at') ? now() : null;

            // Handle alert settings
            $user->alert_setting = [
                'device_login'      => $request->boolean('alert_setting.device_login', false),
                'importance_update' => $request->boolean('alert_setting.importance_update', false),
            ];

            $user->save();

            return responseJSON('User successfully updated', true);
        } catch (Exception $e) {
            logError($e);

            return responseJSON('Failed to update user', [], 500, 'ERROR');
        }
    }

    /**
     * Handle AJAX request for Kendo Grid datagrid
     */
    public function ajaxDatagrid(Request $request): JsonResponse
    {
        Gate::authorize(Permission::LANGUAGE_AI_USERS_VIEW);

        $query = $this->buildDatagridQuery();
        $this->applyDatagridFilters($query, $request);
        $this->applyDatagridSorting($query, $request);

        $totalCount = $this->getTotalCount($query);
        $users      = $this->getPaginatedUsers($query, $request);

        $formattedData = $this->formatDatagridResponse($totalCount, $users);

        return response()->json($formattedData);
    }

    private function buildDatagridQuery()
    {
        return User::query()->with(['plan', 'devices']);
    }

    private function applyDatagridFilters($query, Request $request): void
    {
        if (!$request->has('filter.filters')) {
            return;
        }

        $filters = $request->input('filter.filters');
        foreach ($filters as $filterItem) {
            if (!isset($filterItem['field']) || !isset($filterItem['value'])) {
                continue;
            }

            $this->applySingleFilter($query, $filterItem);
        }
    }

    private function applySingleFilter($query, array $filterItem): void
    {
        switch ($filterItem['field']) {
            case 'name':
                $this->applyNameFilter($query, $filterItem);
                break;
            case 'email':
                $this->applyEmailFilter($query, $filterItem);
                break;
            case 'plan_id':
                $this->applyPlanFilter($query, $filterItem);
                break;
            case 'is_activated':
                $this->applyActivationStatusFilter($query, $filterItem);
                break;
            case 'created_at':
                $this->applyDateFilter($query, $filterItem);
                break;
        }
    }

    private function applyNameFilter($query, array $filterItem): void
    {
        $searchValue = $filterItem['value'];

        $query->where(function ($q) use ($searchValue) {
            $q->where('name', 'like', '%' . $searchValue . '%')
                ->orWhere('email', 'like', '%' . $searchValue . '%');
        });
    }

    private function applyEmailFilter($query, array $filterItem): void
    {
        $query->where('email', 'like', '%' . $filterItem['value'] . '%');
    }

    private function applyPlanFilter($query, array $filterItem): void
    {
        $value = $filterItem['value'];

        // Handle both single value and comma-separated values
        $planIds = is_array($value) ? $value : explode(',', $value);

        // Trim whitespace and convert each to ObjectId
        $planIds = array_map(function ($id) {
            return new ObjectId(trim($id));
        }, $planIds);

        if (!empty($planIds)) {
            $query->whereIn('plan_id', $planIds);
        }
    }

    private function applyActivationStatusFilter($query, array $filterItem): void
    {
        $value = $filterItem['value'];

        // Skip if empty (All option)
        if ($value === '' || $value === null) {
            return;
        }

        // Check string value directly - matches pattern from ManageUserController
        // Frontend change handler ensures correct value ("true" or "false") is sent
        if ($value === 'true') {
            $query->whereNotNull('activated_at');
        } else {
            $query->whereNull('activated_at');
        }
    }

    private function applyDateFilter($query, array $filterItem): void
    {
        if ($filterItem['operator'] === 'gte') {
            $query->whereDate('created_at', '>=', $filterItem['value']);
        } elseif ($filterItem['operator'] === 'lte') {
            $query->whereDate('created_at', '<=', $filterItem['value']);
        }
    }

    private function applyDatagridSorting($query, Request $request): void
    {
        if (!$request->has('sort')) {
            return;
        }

        $sorts = $request->input('sort');
        foreach ($sorts as $sort) {
            $this->applySingleSort($query, $sort);
        }
    }

    private function applySingleSort($query, array $sort): void
    {
        switch ($sort['field']) {
            case 'name':
                $query->orderBy('name', $sort['dir']);
                break;
            case 'email':
                $query->orderBy('email', $sort['dir']);
                break;
            case 'is_activated':
                $query->orderBy('activated_at', $sort['dir']);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sort['dir']);
                break;
        }
    }

    private function getTotalCount($query): int
    {
        return $query->count();
    }

    private function getPaginatedUsers($query, Request $request)
    {
        return $query->skip($request->input('skip', 0))->take($request->input('take', 25))->get();
    }

    private function formatDatagridResponse(int $totalCount, $users): array
    {
        return [
            'total' => $totalCount,
            'data'  => $users->map(fn(User $user) => [
                'id'            => (string) $user->_id,
                'name'          => $user->name,
                'email'         => $user->email,
                'picture'       => $user->picture ?? '/assets/media/avatars/blank.png',
                'plan_id'       => $user->plan_id ? (string) $user->plan_id : null,
                'plan_name'     => $user->plan?->plan_name ?? 'No Plan',
                'is_activated'  => !is_null($user->activated_at),
                'devices_count' => $user->devices()->count(),
                'created_at'    => $user->created_at,
            ]),
        ];
    }

    /**
     * Handle AJAX request for chat usage data
     */
    public function ajaxChatUsage(Request $request, string $id): JsonResponse
    {
        Gate::authorize(Permission::LANGUAGE_AI_USERS_VIEW);

        $user = User::findOrFail($id);

        // Get date range from request or default to last 30 days
        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        // Ensure start date is not before end date
        if ($startDate->gt($endDate)) {
            return response()->json([
                'error' => 'Start date must be before end date',
            ], 400);
        }

        // Query chat usage data
        $chatUsages = ChatUsage::where('user_id', new ObjectId($user->_id))
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->date)->format('Y-m-d'));

        // Create a period of all dates in range
        $period = CarbonPeriod::create($startDate, $endDate);

        // Build data array with all dates (including missing dates with 0 usage)
        $data = [];
        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');
            $usage = $chatUsages->get($dateKey);

            $data[] = [
                'date'  => $dateKey,
                'usage' => $usage ? (int) $usage->usage : 0,
            ];
        }

        return response()->json([
            'data'       => $data,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date'   => $endDate->format('Y-m-d'),
            'total'      => array_sum(array_column($data, 'usage')),
        ]);
    }
}
