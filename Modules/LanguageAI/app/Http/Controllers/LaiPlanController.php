<?php

namespace Modules\LanguageAI\Http\Controllers;

use App\Classes\Breadcrumbs;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\MongoDB\MasterPlan;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\LanguageAI\Http\Requests\SavePlanRequest;

class LaiPlanController extends Controller
{
    private string $url = '/language-ai/plans';

    private function defaultParser(): array
    {
        return [
            'url' => $this->url,
            'view' => 'languageai::plans',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize(Permission::LANGUAGE_AI_PLANS_VIEW->value);

        $breadcrumbs = [
            new Breadcrumbs('Language AI', '/language-ai'),
            new Breadcrumbs('Plans', $this->url),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
        ]);

        return view('languageai::plans.index')->with($parser);
    }

    /**
     * Show the form for creating or editing a resource.
     */
    public function upsert(?string $id = null)
    {
        $permission = $id
            ? Permission::LANGUAGE_AI_PLANS_UPDATE->value
            : Permission::LANGUAGE_AI_PLANS_CREATE->value;

        Gate::authorize($permission);

        $plan = null;
        $title = 'Create Plan';
        $breadcrumbs = [
            new Breadcrumbs('Language AI', '/language-ai'),
            new Breadcrumbs('Plans', $this->url),
        ];

        if ($id) {
            $plan = MasterPlan::findOrFail($id);
            $title = 'Edit Plan';
            $breadcrumbs[] = new Breadcrumbs('Edit Plan');
        } else {
            $breadcrumbs[] = new Breadcrumbs('Create Plan');
        }

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'plan' => $plan,
            'title' => $title,
        ]);

        return view('languageai::plans.upsert')->with($parser);
    }

    /**
     * Store or update the resource in storage.
     */
    public function save(SavePlanRequest $request, ?string $id = null): JsonResponse
    {
        try {
            $data = $request->validated();

            // Handle booleans
            $data['is_active'] = $request->boolean('is_active');
            $data['is_popular'] = $request->boolean('is_popular');
            $data['is_displayed'] = $request->boolean('is_displayed');

            // Handle features (remove empty)
            if (isset($data['features'])) {
                $data['features'] = array_values(array_filter($data['features']));
            }

            // Ensure max_usage.monthly is an integer
            if (isset($data['max_usage']['monthly'])) {
                $data['max_usage']['monthly'] = (int) $data['max_usage']['monthly'];
            }

            if ($id) {
                $plan = MasterPlan::findOrFail($id);
                $plan->update($data);
                $message = 'Plan successfully updated';
            } else {
                MasterPlan::create($data);
                $message = 'Plan successfully created';
            }

            return responseJSON($message, true, 200, 'SUCCESS');
        } catch (Exception $e) {
            logError($e);

            return responseJSON('Failed to save plan', [], 500, 'ERROR');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        Gate::authorize(Permission::LANGUAGE_AI_PLANS_DELETE->value);

        try {
            $plan = MasterPlan::findOrFail($id);
            $plan->delete();

            return responseJSON('Plan successfully deleted', true);
        } catch (Exception $e) {
            logError($e);

            return responseJSON('Failed to delete plan', [], 500, 'ERROR');
        }
    }

    /**
     * Handle AJAX request for Kendo Grid datagrid
     */
    public function ajaxDatagrid(Request $request): JsonResponse
    {
        Gate::authorize(Permission::LANGUAGE_AI_PLANS_VIEW->value);

        $query = MasterPlan::query();

        if ($request->has('filter.filters')) {
            foreach ($request->input('filter.filters') as $filter) {
                if (isset($filter['field']) && isset($filter['value'])) {
                    if ($filter['field'] === 'plan_name') {
                        $query->where('plan_name', 'like', '%'.$filter['value'].'%');
                    } elseif ($filter['field'] === 'plan_code') {
                        $query->where('plan_code', 'like', '%'.$filter['value'].'%');
                    } elseif ($filter['field'] === 'is_active') {
                        $value = $filter['value'] === 'true';
                        $query->where('is_active', $value);
                    }
                }
            }
        }

        if ($request->has('sort')) {
            foreach ($request->input('sort') as $sort) {
                $query->orderBy($sort['field'], $sort['dir']);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $total = $query->count();
        $data = $query->skip($request->input('skip', 0))
            ->take($request->input('take', 25))
            ->get()
            ->map(fn ($item) => [
                'id' => (string) $item->_id,
                'plan_name' => $item->plan_name,
                'plan_code' => $item->plan_code,
                'price' => $item->price,
                'currency' => $item->currency,
                'interval' => $item->interval,
                'is_active' => $item->is_active,
                'is_popular' => $item->is_popular,
                'is_displayed' => $item->is_displayed,
                'created_at' => $item->created_at,
            ]);

        return response()->json([
            'total' => $total,
            'data' => $data,
        ]);
    }
}
