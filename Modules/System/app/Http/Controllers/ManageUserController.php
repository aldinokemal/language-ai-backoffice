<?php

namespace Modules\System\Http\Controllers;

use App\Classes\Breadcrumbs;
use App\Enums\Permission;
use App\Enums\StorageSource;
use App\Http\Controllers\Controller;
use App\Models\DB1\SysRole;
use App\Models\DB1\SysUser;
use App\Models\DB1\SysUserOrganization;
use App\Models\DB1\SysUserOrganizationRole;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ManageUserController extends Controller
{
    private string $url = '/system/users';

    private function defaultParser(): array
    {
        return [
            'url' => $this->url,
            'view' => 'system::user',
        ];
    }

    public function index()
    {
        Gate::authorize(Permission::SYSTEM_USERS_VIEW);

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Kelola Pengguna', $this->url),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
        ]);

        return view('system::user.index')->with($parser);
    }

    public function edit($id)
    {
        Gate::authorize(Permission::SYSTEM_USERS_UPDATE);

        $id = customDecrypt($id);
        $user = SysUser::findOrFail($id);

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Pengaturan Pengguna', $this->url),
            new Breadcrumbs('Edit Pengguna'),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'user' => $user,
        ]);

        return view('system::user.upsert')->with($parser);
    }

    public function create()
    {
        Gate::authorize(Permission::SYSTEM_USERS_CREATE);

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Pengaturan Pengguna', $this->url),
            new Breadcrumbs('Tambah Pengguna'),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'user' => null,
        ]);

        return view('system::user.upsert')->with($parser);
    }

    private function handleUpsert(Request $request, ?SysUser $user = null): JsonResponse
    {
        $isUpdate = $user !== null;
        $this->validateUpsertRequest($request, $user);

        try {
            DB::beginTransaction();

            $user = $this->createOrUpdateUser($request, $user, $isUpdate);
            $this->handleAvatarOperations($request, $user, $isUpdate);
            $this->handleRoleAssignments($request, $user, $isUpdate);

            DB::commit();

            $message = $isUpdate ? 'Pengguna berhasil diperbarui' : 'Pengguna berhasil dibuat';

            return responseJSON($message, true);

        } catch (Exception $e) {
            DB::rollBack();
            logError($e);
            $errorMessage = $isUpdate ? 'Gagal memperbarui pengguna' : 'Gagal membuat pengguna';

            return responseJSON($errorMessage, [], 500, 'ERROR');
        }
    }

    private function validateUpsertRequest(Request $request, ?SysUser $user): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('sys_users', 'email')->ignore($user?->id)],
            'username' => ['required', 'string', Rule::unique('sys_users', 'username')->ignore($user?->id)],
            'phone' => 'nullable|string|max:20',
            'selected_roles' => 'nullable|array',
            'default_roles' => 'nullable|array',
        ];

        if (! $user || $request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        if ($request->hasFile('avatar')) {
            $rules['avatar'] = 'image|mimes:jpeg,jpg,png|max:5120';
        }

        if ($request->has('avatar_remove')) {
            $rules['avatar_remove'] = 'boolean';
        }

        $request->validate($rules);
    }

    private function createOrUpdateUser(Request $request, ?SysUser $user, bool $isUpdate): SysUser
    {
        if (! $isUpdate) {
            $user = new SysUser;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->phone = $request->phone;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return $user;
    }

    private function handleAvatarOperations(Request $request, SysUser $user, bool $isUpdate): void
    {
        if ($request->boolean('avatar_remove') && $user->picture) {
            $this->removeAvatar($user, $isUpdate);
        }

        if ($request->hasFile('avatar')) {
            $this->uploadAvatar($request, $user, $isUpdate);
        }
    }

    private function removeAvatar(SysUser $user, bool $isUpdate): void
    {
        try {
            if (! str_contains($user->picture, 'default.png') && $user->picture_storage === StorageSource::S3->value) {
                Storage::disk(StorageSource::S3->value)->delete($user->picture);
            }

            $oldPicture = $user->picture;
            $user->picture = null;
            $user->picture_storage = null;
            $user->save();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($user)
                ->event('updated')
                ->withProperties(['operation' => 'avatar_removed', 'old_picture' => $oldPicture])
                ->inLog('system_users')
                ->log('Avatar pengguna dihapus');

            if ($isUpdate) {
                $cacheKey = sprintf(config('cache.img_profile.cacheKey'), $user->id);
                Cache::forget($cacheKey);
            }
        } catch (Exception $e) {
            logError($e);
        }
    }

    private function uploadAvatar(Request $request, SysUser $user, bool $isUpdate): void
    {
        try {
            $file = $request->file('avatar');
            $extension = $file->getClientOriginalExtension();
            $filePath = sprintf(config('app.image_profile_path'), $user->id, $extension);

            $path = $file->storeAs(dirname($filePath), basename($filePath), StorageSource::S3->value);

            if ($path) {
                if ($user->picture && ! str_contains($user->picture, 'default.png') && $user->picture_storage === StorageSource::S3->value) {
                    Storage::disk(StorageSource::S3->value)->delete($user->picture);
                }

                $oldPicture = $user->picture;
                $user->picture = $filePath;
                $user->picture_storage = StorageSource::S3->value;
                $user->save();

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($user)
                    ->event($isUpdate ? 'updated' : 'created')
                    ->withProperties([
                        'operation' => $isUpdate ? 'avatar_updated' : 'avatar_uploaded',
                        'new_picture' => $filePath,
                        'old_picture' => $oldPicture,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                    ])
                    ->inLog('system_users')
                    ->log($isUpdate ? 'Avatar pengguna diperbarui' : 'Avatar pengguna diunggah');

                if ($isUpdate) {
                    $cacheKey = sprintf(config('cache.img_profile.cacheKey'), $user->id);
                    Cache::forget($cacheKey);
                }
            }
        } catch (Exception $e) {
            logError($e);
        }
    }

    private function handleRoleAssignments(Request $request, SysUser $user, bool $isUpdate): void
    {
        if (! $request->filled('selected_roles')) {
            return;
        }

        if ($isUpdate) {
            SysUserOrganizationRole::query()
                ->whereHas('userOrganization', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->delete();
        }

        $selectedRoles = collect($request->selected_roles)
            ->map(fn (string $id): int => customDecrypt($id))
            ->toArray();

        $defaultRoles = collect($request->default_roles ?? [])
            ->mapWithKeys(fn (string $roleId, string $orgId): array => [customDecrypt($orgId) => customDecrypt($roleId)])
            ->toArray();

        foreach ($selectedRoles as $roleId) {
            $role = SysRole::find($roleId);
            $userOrg = SysUserOrganization::firstOrCreate([
                'organization_id' => $role->organization_id,
                'user_id' => $user->id,
            ], ['is_default' => true]);

            SysUserOrganizationRole::updateOrCreate([
                'user_organization_id' => $userOrg->id,
                'role_id' => $roleId,
            ], ['is_default' => in_array($roleId, $defaultRoles)]);
        }

        $assignedRoles = SysRole::whereIn('id', $selectedRoles)->with('organization')->get();
        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->event($isUpdate ? 'updated' : 'created')
            ->withProperties([
                'assigned_roles' => $assignedRoles->map(fn (SysRole $role) => [
                    'role_name' => $role->name,
                    'organization_name' => $role->organization?->name ?? 'Unknown',
                    'is_default' => in_array($role->id, $defaultRoles),
                ])->toArray(),
                'operation_type' => $isUpdate ? 'update_roles' : 'assign_roles',
            ])
            ->inLog('system_users')
            ->log($isUpdate ? 'Role pengguna diperbarui' : 'Role pengguna ditetapkan');

        SysUserOrganization::query()
            ->whereDoesntHave('organizationRoles')
            ->delete();
    }

    public function store(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_USERS_CREATE);

        return $this->handleUpsert($request);
    }

    public function update(Request $request, $id)
    {
        Gate::authorize(Permission::SYSTEM_USERS_UPDATE);
        $id = customDecrypt($id);
        $user = SysUser::findOrFail($id);

        return $this->handleUpsert($request, $user);
    }

    public function destroy($id)
    {
        Gate::authorize(Permission::SYSTEM_USERS_DELETE);

        $userId = $this->decryptUserId($id);
        $user = $this->findUser($userId);

        $this->logUserDeletion($user);
        $this->cleanupUserAvatar($user);
        $this->deleteUser($user);

        return responseJSON('Pengguna berhasil dihapus');
    }

    private function decryptUserId($id)
    {
        return decryptOrAbort($id);
    }

    private function findUser(int $userId): SysUser
    {
        return SysUser::findOrFail($userId);
    }

    private function logUserDeletion(SysUser $user): void
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->event('deleted')
            ->withProperties([
                'operation' => 'user_deleted',
                'deleted_user_data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'had_avatar' => ! empty($user->picture),
                ],
            ])
            ->inLog('system_users')
            ->log('Pengguna dihapus');
    }

    private function cleanupUserAvatar(SysUser $user): void
    {
        if ($user->picture && ! str_contains($user->picture, 'default.png') && $user->picture_storage === StorageSource::S3->value) {
            Storage::disk(StorageSource::S3->value)->delete($user->picture);
        }
    }

    private function deleteUser(SysUser $user): void
    {
        $user->delete();
    }

    public function ajaxBannedOrUnbanned(Request $request, $action)
    {
        Gate::authorize(Permission::SYSTEM_USERS_UPDATE);

        $bannedAt = $this->determineBanStatus($action);
        $userIds = $this->extractUserIds($request);
        $selectAll = $request->input('selectAll');

        $query = $this->buildBanQuery($userIds, $selectAll);
        $affectedUsers = $this->getAffectedUsers($query);

        $this->performBulkBanUpdate($query, $bannedAt);
        $this->logBulkBanActivity($affectedUsers, $action, $bannedAt, $selectAll);

        $message = $this->getBanResponseMessage($action);

        return responseJSON($message);
    }

    private function determineBanStatus(string $action): ?string
    {
        return $action === 'banned' ? now() : null;
    }

    private function extractUserIds(Request $request): array
    {
        return collect($request->input('toggledNodes', []))
            ->map(fn (string $id) => customDecrypt($id))
            ->toArray();
    }

    private function buildBanQuery(array $userIds, bool $selectAll)
    {
        $query = SysUser::query();

        if ($selectAll) {
            $query->whereNotIn('id', $userIds);
        } else {
            $query->whereIn('id', $userIds);
        }

        return $query;
    }

    private function getAffectedUsers($query)
    {
        return $query->get();
    }

    private function performBulkBanUpdate($query, $bannedAt): void
    {
        $query->update(['banned_at' => $bannedAt]);
    }

    private function logBulkBanActivity($affectedUsers, string $action, $bannedAt, bool $selectAll): void
    {
        foreach ($affectedUsers as $affectedUser) {
            activity()
                ->causedBy(Auth::user())
                ->performedOn($affectedUser)
                ->event('updated')
                ->withProperties([
                    'action' => $action,
                    'banned_at' => $bannedAt,
                    'bulk_operation' => true,
                    'select_all' => $selectAll,
                ])
                ->inLog('system_users')
                ->log($action === 'banned' ? 'Pengguna diblokir' : 'Blokir pengguna dibuka');
        }
    }

    private function getBanResponseMessage(string $action): string
    {
        return $action === 'banned' ? 'Pengguna berhasil diblokir' : 'Pengguna berhasil dibuka blokirnya';
    }

    public function ajaxDatagrid(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_USERS_VIEW);

        $query = $this->buildDatagridQuery();
        $this->applyDatagridFilters($query, $request);
        $this->applyDatagridSorting($query, $request);

        $totalCount = $this->getTotalCount($query);
        $users = $this->getPaginatedUsers($query, $request);

        $formattedData = $this->formatDatagridResponse($totalCount, $users);

        return response()->json($formattedData);
    }

    private function buildDatagridQuery()
    {
        return SysUser::query()->with('organizations.organization');
    }

    private function applyDatagridFilters($query, Request $request): void
    {
        if (! $request->has('filter.filters')) {
            return;
        }

        $filters = $request->input('filter.filters');
        foreach ($filters as $filterItem) {
            if (! isset($filterItem['field']) || ! isset($filterItem['value'])) {
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
            case 'organizations':
                $this->applyOrganizationFilter($query, $filterItem);
                break;
            case 'email':
                $this->applyEmailFilter($query, $filterItem);
                break;
            case 'is_banned':
                $this->applyBanStatusFilter($query, $filterItem);
                break;
            case 'created_at':
                $this->applyDateFilter($query, $filterItem);
                break;
        }
    }

    private function applyNameFilter($query, array $filterItem): void
    {
        $query->where(function ($q) use ($filterItem) {
            $q->where('name', 'ilike', '%'.strtolower($filterItem['value']).'%')
                ->orWhere('email', 'ilike', '%'.strtolower($filterItem['value']).'%');
        });
    }

    private function applyOrganizationFilter($query, array $filterItem): void
    {
        $query->whereHas('organizations', function ($q) use ($filterItem) {
            $q->whereHas('organization', function ($q) use ($filterItem) {
                $q->where('name', 'ilike', '%'.strtolower($filterItem['value']).'%');
            });
        });
    }

    private function applyEmailFilter($query, array $filterItem): void
    {
        $query->where('email', 'ilike', '%'.strtolower($filterItem['value']).'%');
    }

    private function applyBanStatusFilter($query, array $filterItem): void
    {
        if ($filterItem['value'] === 'true') {
            $query->whereNotNull('banned_at');
        } else {
            $query->whereNull('banned_at');
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
        if (! $request->has('sort')) {
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
            case 'organizations':
                $query->orderBy('organizations.organization.name', $sort['dir']);
                break;
            case 'is_banned':
                $query->orderBy('banned_at', $sort['dir']);
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
        return $query->skip($request->input('skip'))->take($request->input('take'))->get();
    }

    private function formatDatagridResponse(int $totalCount, $users): array
    {
        return [
            'total' => $totalCount,
            'data' => $users->map(fn (SysUser $user) => [
                'id' => customEncrypt($user->id),
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => getUserImage($user),
                'is_banned' => ! empty($user->banned_at),
                'created_at' => $user->created_at,
                'organizations' => $user->organizations->map(fn (SysUserOrganization $organization) => $organization->organization?->name ?? 'Unknown')->join('|'),
            ]),
        ];
    }

    public function ajaxOrganizationAndRole(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_USERS_VIEW);

        $query = $this->buildOrganizationRoleQuery();
        $this->applyOrganizationRoleFilters($query, $request);

        $totalCount = $this->getOrganizationRoleTotalCount($query);
        $this->applyOrganizationRolePagination($query, $request);

        $roles = $this->getOrganizationRoles($query);
        $selectedRoles = $this->getSelectedRolesForUser($request);

        $formattedData = $this->formatOrganizationRoleResponse($totalCount, $roles, $selectedRoles);

        return response()->json($formattedData);
    }

    private function buildOrganizationRoleQuery()
    {
        return SysRole::query()->with(['organization']);
    }

    private function applyOrganizationRoleFilters($query, Request $request): void
    {
        $organizationId = $request->input('organization_id');
        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        if ($request->has('filter.filters')) {
            $filters = $request->input('filter.filters');
            foreach ($filters as $filter) {
                if (! isset($filter['field']) || ! isset($filter['value'])) {
                    continue;
                }

                $this->applyOrganizationRoleSingleFilter($query, $filter);
            }
        }
    }

    private function applyOrganizationRoleSingleFilter($query, array $filter): void
    {
        $value = strtolower($filter['value']);

        switch ($filter['field']) {
            case 'organization':
                $this->applyOrganizationNameFilter($query, $value);
                break;
            case 'role':
                $this->applyRoleNameFilter($query, $value);
                break;
        }
    }

    private function applyOrganizationNameFilter($query, string $value): void
    {
        $query->whereHas('organization', function ($q) use ($value) {
            $q->where('name', 'ilike', "%{$value}%");
        });
    }

    private function applyRoleNameFilter($query, string $value): void
    {
        $query->where('name', 'ilike', "%{$value}%");
    }

    private function getOrganizationRoleTotalCount($query): int
    {
        return $query->count();
    }

    private function applyOrganizationRolePagination($query, Request $request): void
    {
        if ($request->has('take')) {
            $query->skip($request->input('skip'))->take($request->input('take'));
        }
    }

    private function getOrganizationRoles($query)
    {
        return $query->get();
    }

    private function getSelectedRolesForUser(Request $request)
    {
        $userId = $request->input('user_id');
        if (! $userId) {
            return collect();
        }

        $userId = customDecrypt($userId);

        return SysUserOrganizationRole::query()
            ->whereHas('userOrganization', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->get()
            ->mapWithKeys(function ($role) {
                return [$role->role_id => $role->is_default];
            });
    }

    private function formatOrganizationRoleResponse(int $totalCount, $roles, $selectedRoles): array
    {
        return [
            'total' => $totalCount,
            'data' => $roles->map(function (SysRole $role) use ($selectedRoles): array {
                $roleId = $role->id;

                return [
                    'id' => customEncrypt($roleId),
                    'organization_id' => customEncrypt($role->organization_id),
                    'organization' => $role->organization?->name ?? 'Unknown',
                    'role' => $role->name,
                    'description' => $role->description,
                    'is_selected' => $selectedRoles->has($roleId),
                    'is_default' => $selectedRoles->get($roleId, false),
                ];
            }),
        ];
    }
}
