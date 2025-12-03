<?php

namespace Modules\System\Http\Controllers;

use App\Classes\Breadcrumbs;
use App\Enums\Permission;
use App\Enums\StorageSource;
use App\Http\Controllers\Controller;
use App\Models\DB1\SysOrganization;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ManageOrganizationController extends Controller
{
    private string $url = '/system/organizations';

    private function defaultParser(): array
    {
        return [
            'url' => $this->url,
            'view' => 'system::organization',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize(Permission::SYSTEM_ORGANIZATIONS_VIEW);

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Manajemen Organisasi', $this->url),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
        ]);

        return view('system::organization.index')->with($parser);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize(Permission::SYSTEM_ORGANIZATIONS_CREATE);

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Manajemen Organisasi', $this->url),
            new Breadcrumbs('Tambah Organisasi'),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'data' => null,
        ]);

        return view('system::organization.upsert')->with($parser);
    }

    private function handleUpsert(Request $request, ?SysOrganization $organization = null): mixed
    {
        $isUpdate = $organization !== null;

        // Log the request for debugging
        Log::info('Organization upsert request', [
            'is_update' => $isUpdate,
            'has_file' => $request->hasFile('logo'),
            'logo_remove' => $request->get('logo_remove'),
            'files' => $request->allFiles(),
            'all_data' => $request->except(['_token', '_method']),
        ]);

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
        ];

        // Add logo validation if provided
        if ($request->hasFile('logo')) {
            $rules['logo'] = 'image|mimes:jpeg,jpg,png|max:2048'; // Max 2MB
        }

        // Add logo_remove validation
        if ($request->has('logo_remove')) {
            $rules['logo_remove'] = 'boolean';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Create or update organization
            if (! $isUpdate) {
                $organization = new SysOrganization;
            }

            // Update basic organization data
            $organization->name = $request->name;
            $organization->address = $request->address;
            $organization->phone = $request->phone;
            $organization->email = $request->email;
            $organization->website = $request->website;
            $organization->code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Save organization first to ensure we have an ID
            $organization->save();
            Log::info('Organization saved', ['id' => $organization->id, 'name' => $organization->name]);

            // Handle logo removal if requested
            if ($request->boolean('logo_remove') && $organization->logo_path) {
                try {
                    // Delete existing logo if not default
                    if (! str_contains($organization->logo_path, 'default.png') && $organization->logo_storage === StorageSource::S3->value) {
                        Storage::disk(StorageSource::S3->value)->delete($organization->logo_path);
                    }

                    $oldLogoPath = $organization->logo_path;
                    $organization->logo_path = null;
                    $organization->logo_storage = null;
                    $organization->save();

                    // Log logo removal
                    activity()
                        ->causedBy(Auth::user())
                        ->performedOn($organization)
                        ->event('updated')
                        ->withProperties([
                            'operation' => 'logo_removed',
                            'old_logo_path' => $oldLogoPath,
                        ])
                        ->inLog('system_organizations')
                        ->log('Logo organisasi dihapus');
                } catch (Exception $e) {
                    // Log error but don't fail the entire operation
                    logError($e);
                }
            }

            // Handle logo upload if provided
            if ($request->hasFile('logo')) {
                try {
                    $file = $request->file('logo');

                    // Generate file path using config
                    $extension = $file->getClientOriginalExtension();
                    $filePath = sprintf(config('app.image_org_path'), $organization->id, $extension);

                    // Store the file
                    Storage::disk(StorageSource::S3->value)->put($filePath, file_get_contents($file));

                    if ($filePath) {
                        // Delete old logo if exists and not default
                        if ($organization->logo_path && ! str_contains($organization->logo_path, 'default.png') && $organization->logo_storage === StorageSource::S3->value) {
                            Storage::disk(StorageSource::S3->value)->delete($organization->logo_path);
                        }

                        $oldLogoPath = $organization->logo_path;
                        $organization->logo_path = $filePath;
                        $organization->logo_storage = StorageSource::S3->value;
                        $organization->save();

                        // Log logo upload
                        activity()
                            ->causedBy(Auth::user())
                            ->performedOn($organization)
                            ->event($isUpdate ? 'updated' : 'created')
                            ->withProperties([
                                'operation' => $isUpdate ? 'logo_updated' : 'logo_uploaded',
                                'new_logo_path' => $filePath,
                                'old_logo_path' => $oldLogoPath,
                                'file_size' => $file->getSize(),
                                'file_type' => $file->getMimeType(),
                            ])
                            ->inLog('system_organizations')
                            ->log($isUpdate ? 'Logo organisasi diperbarui' : 'Logo organisasi diunggah');
                    }
                } catch (Exception $e) {
                    // Log error but don't fail the entire operation
                    logError($e);
                }
            }

            DB::commit();

            $message = $isUpdate ?
                'Organisasi berhasil diperbarui' :
                'Organisasi berhasil dibuat';

            return response()->json(['message' => $message]);

        } catch (Exception $e) {
            DB::rollBack();
            logError($e);
            $errorMessage = $isUpdate ?
                'Gagal memperbarui organisasi' :
                'Gagal membuat organisasi';

            return response()->json(['message' => $errorMessage], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_ORGANIZATIONS_CREATE);

        return $this->handleUpsert($request);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        Gate::authorize(Permission::SYSTEM_ORGANIZATIONS_VIEW);

        $id = customDecrypt($id);
        $organization = SysOrganization::findOrFail($id);

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Manajemen Organisasi', $this->url),
            new Breadcrumbs('Detail Organisasi'),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'data' => $organization,
        ]);

        return view('system::organization.show')->with($parser);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        Gate::authorize(Permission::SYSTEM_ORGANIZATIONS_UPDATE);

        $id = customDecrypt($id);
        $organization = SysOrganization::findOrFail($id);

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Manajemen Organisasi', $this->url),
            new Breadcrumbs('Edit Organisasi'),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'data' => $organization,
        ]);

        return view('system::organization.upsert')->with($parser);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Gate::authorize(Permission::SYSTEM_ORGANIZATIONS_UPDATE);

        $id = customDecrypt($id);
        $organization = SysOrganization::findOrFail($id);

        return $this->handleUpsert($request, $organization);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Gate::authorize(Permission::SYSTEM_ORGANIZATIONS_DELETE);

        $id = customDecrypt($id);
        $organization = SysOrganization::findOrFail($id);

        // Log organization deletion with data before deletion
        activity()
            ->causedBy(Auth::user())
            ->performedOn($organization)
            ->event('deleted')
            ->withProperties([
                'operation' => 'organization_deleted',
                'deleted_organization_data' => [
                    'code' => $organization->code,
                    'name' => $organization->name,
                    'address' => $organization->address,
                    'phone' => $organization->phone,
                    'email' => $organization->email,
                    'website' => $organization->website,
                    'had_logo' => ! empty($organization->logo_path),
                ],
            ])
            ->inLog('system_organizations')
            ->log('Organisasi dihapus');

        // Delete logo if exists and not default
        if ($organization->logo_path && ! str_contains($organization->logo_path, 'default.png') && $organization->logo_storage === StorageSource::S3->value) {
            Storage::disk(StorageSource::S3->value)->delete($organization->logo_path);
        }

        $organization->delete();

        return response()->json(['message' => 'Organisasi berhasil dihapus']);
    }

    public function ajaxDatagrid(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_ORGANIZATIONS_VIEW);

        $query = SysOrganization::query();

        // Apply filters
        if ($request->has('filter.filters')) {
            $filters = $request->input('filter.filters');
            foreach ($filters as $filter) {
                if (! isset($filter['field']) || ! isset($filter['value'])) {
                    continue;
                }

                switch ($filter['field']) {
                    case 'name':
                        $query->where('name', 'ilike', '%'.strtolower($filter['value']).'%');
                        break;
                    case 'code':
                        $query->where('code', 'ilike', '%'.strtolower($filter['value']).'%');
                        break;
                }
            }
        }

        // Apply sorting
        if ($request->has('sort')) {
            $sorts = $request->input('sort');
            foreach ($sorts as $sort) {
                $query->orderBy($sort['field'], $sort['dir']);
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $totalCount = $query->count();

        // Apply pagination if requested
        if ($request->has('take')) {
            $query->skip($request->input('skip'))->take($request->input('take'));
        }

        $organizations = $query->get();

        $formattedData = [
            'total' => $totalCount,
            'data' => $organizations->map(function ($organization) {
                return [
                    'id' => customEncrypt($organization->id),
                    'code' => $organization->code,
                    'name' => $organization->name,
                    'address' => $organization->address,
                    'phone' => $organization->phone,
                    'email' => $organization->email,
                    'website' => $organization->website,
                ];
            }),
        ];

        return response()->json($formattedData);
    }
}
