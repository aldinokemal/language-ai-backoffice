@extends('layouts.app')

@section('title', isset($user) ? 'Edit Pengguna' : 'Buat Pengguna')

@push('styles')
<style>
    .avatar-upload-container {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .avatar-preview-container {
        position: relative;
        display: inline-block;
    }
    
    .avatar-preview {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e5e7eb;
        transition: border-color 0.3s ease;
    }
    
    .avatar-preview:hover {
        border-color: #3b82f6;
    }
    
    .remove-avatar-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 24px;
        height: 24px;
        background-color: #ef4444;
        color: white;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
        transition: background-color 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .remove-avatar-btn:hover {
        background-color: #dc2626;
    }
    
    .file-input-wrapper {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .file-input {
        display: block;
        width: 100%;
        font-size: 0.875rem;
        color: #6b7280;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        cursor: pointer;
        background-color: #f9fafb;
        transition: border-color 0.3s ease;
    }
    
    .file-input:hover {
        border-color: #3b82f6;
    }
    
    .file-input::-webkit-file-upload-button {
        margin-right: 1rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        border: none;
        font-size: 0.875rem;
        font-weight: 500;
        background-color: #3b82f6;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .file-input::-webkit-file-upload-button:hover {
        background-color: #2563eb;
    }
    
    /* Mobile horizontal scroll for organization and role grid */
    @media (max-width: 1024px) {
        #organizationAndRoleGrid {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        
        #organizationAndRoleGrid .k-grid {
            width: 100% !important;
            min-width: 600px; /* Ensure minimum width for table content */
        }
        
        #organizationAndRoleGrid .k-grid-content {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>
@endpush

@section('toolbar-actions')
    <a href="{{ goBack($url) }}" class="kt-btn kt-btn-sm kt-btn-outline">
        <i class="ki-filled ki-arrow-left"></i>
        Kembali
    </a>
@endsection

@section('content')
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content">
                    <!-- User Information Section -->
                    <div class="mb-10">
                        <h3 class="text-lg font-semibold text-foreground mb-5">Informasi Pengguna</h3>
                        <div class="grid gap-5">
                            <!-- Avatar Upload -->
                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                    Photo
                                </label>
                                <div class="flex-1">
                                    <div class="avatar-upload-container">
                                        <div class="avatar-preview-container">
                                            <img id="avatarPreview" class="avatar-preview" 
                                                 src="{{ isset($user) ? getUserImage($user) : asset('assets/media/avatars/blank.png') }}" 
                                                 alt="Avatar Preview">
                                            <button type="button" id="removeAvatar" class="remove-avatar-btn" style="display: none;">
                                                ×
                                            </button>
                                        </div>
                                        <div class="file-input-wrapper">
                                            <input type="file" id="avatarInput" name="avatar" class="file-input" 
                                                   accept=".png,.jpg,.jpeg">
                                            <input type="hidden" id="avatarRemove" name="avatar_remove" value="0">
                                            <small class="text-sm text-secondary-foreground">
                                                150x150px JPEG, PNG Image (max 5MB)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Basic Information Fields -->
                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                    Nama Lengkap
                                </label>
                                <div class="flex-1">
                                    <input class="kt-input" name="name" id="name" value="{{ $user?->name }}"
                                        placeholder="Masukkan nama lengkap" />
                                    <div id="name-error" class="text-sm text-red-600 mt-1 hidden"></div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                    Username
                                </label>
                                <div class="flex-1">
                                    <input class="kt-input" name="username" id="username" value="{{ $user?->username }}"
                                        placeholder="Masukkan username" />
                                    <div id="username-error" class="text-sm text-red-600 mt-1 hidden"></div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="form-label min-w-0 lg:w-56 text-foreground font-medium required">
                                    Email
                                </label>
                                <div class="flex-1">
                                    <input class="kt-input" type="email" name="email" id="email"
                                        value="{{ $user?->email }}" required placeholder="Masukkan alamat email" />
                                    <div id="email-error" class="text-sm text-red-600 mt-1 hidden"></div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                    Telepon
                                </label>
                                <div class="flex-1">
                                    <input class="kt-input" type="text" name="phone" id="phone"
                                        value="{{ $user?->phone }}" placeholder="Masukkan nomor telepon" />
                                    <div id="phone-error" class="text-sm text-red-600 mt-1 hidden"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password Section -->
                    <div class="mb-10">
                        <h3 class="text-lg font-semibold text-foreground mb-5">Keamanan</h3>
                        <div class="grid gap-5">
                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="form-label min-w-0 lg:w-56 text-foreground font-medium {{ !isset($user) ? 'required' : '' }}">
                                    Password
                                </label>
                                <div class="flex-1">
                                    <input class="kt-input" type="password" name="password" id="password" 
                                        {{ !isset($user) ? 'required' : '' }}
                                        placeholder="{{ isset($user) ? 'Kosongkan jika tidak ingin mengubah password' : 'Masukkan password' }}" />
                                    <div id="password-error" class="text-sm text-red-600 mt-1 hidden"></div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="form-label min-w-0 lg:w-56 text-foreground font-medium {{ !isset($user) ? 'required' : '' }}">
                                    Konfirmasi Password
                                </label>
                                <div class="flex-1">
                                    <input class="kt-input" type="password" name="password_confirmation"
                                        id="password_confirmation" {{ !isset($user) ? 'required' : '' }}
                                        placeholder="Ulangi password" />
                                    <div id="password_confirmation-error" class="text-sm text-red-600 mt-1 hidden"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Organization & Roles Section -->
                    <div class="mb-10">
                        <h3 class="text-lg font-semibold text-foreground mb-5">Organisasi & Peran</h3>
                        <div id="organizationAndRoleGrid"></div>
                    </div>
                </div>

                <div class="kt-card-footer flex justify-end gap-3">
                    <button type="submit" class="kt-btn kt-btn-primary" id="btn-save">
                        <i class="ki-filled ki-check"></i>
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let defaultRoleIds = {};
        let checkedRoles = new Set(); // Track checked roles across pages/filters
        let uncheckedRoles = new Set(); // Track explicitly unchecked roles

        // Function to clear all validation errors
        function clearValidationErrors() {
            const errorElements = document.querySelectorAll('[id$="-error"]');
            errorElements.forEach(element => {
                element.textContent = '';
                element.classList.add('hidden');
            });
            
            // Remove error styling from inputs
            const inputs = document.querySelectorAll('.kt-input');
            inputs.forEach(input => {
                input.classList.remove('border-red-500');
            });
        }

        // Function to display validation errors
        function displayValidationErrors(errors) {
            clearValidationErrors();
            
            Object.keys(errors).forEach(field => {
                const errorElement = document.getElementById(field + '-error');
                const inputElement = document.getElementById(field);
                
                if (errorElement && errors[field].length > 0) {
                    errorElement.textContent = errors[field][0]; // Show first error
                    errorElement.classList.remove('hidden');
                    
                    if (inputElement) {
                        inputElement.classList.add('border-red-500');
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Avatar handling
            const avatarInput = document.getElementById('avatarInput');
            const avatarPreview = document.getElementById('avatarPreview');
            const removeAvatarBtn = document.getElementById('removeAvatar');
            const avatarRemoveInput = document.getElementById('avatarRemove');
            const defaultAvatar = '{{ asset('assets/media/avatars/blank.png') }}';

            // Handle file selection
            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file type
                    const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg'];
                    if (!allowedTypes.includes(file.type)) {
                        KendoDialog.alert({
                            title: "Error",
                            content: "Format file tidak didukung. Harap gunakan PNG, JPG, atau JPEG.",
                            type: "error"
                        });
                        this.value = '';
                        return;
                    }

                    // Validate file size (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        KendoDialog.alert({
                            title: "Error",
                            content: "Ukuran file terlalu besar. Maksimal 5MB.",
                            type: "error"
                        });
                        this.value = '';
                        return;
                    }

                    // Create preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                        removeAvatarBtn.style.display = 'block';
                        avatarRemoveInput.value = '0';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Handle remove avatar
            removeAvatarBtn.addEventListener('click', function() {
                avatarPreview.src = defaultAvatar;
                avatarInput.value = '';
                removeAvatarBtn.style.display = 'none';
                avatarRemoveInput.value = '1';
            });

            // Show remove button if user has existing avatar
            if (!avatarPreview.src.includes('blank.png')) {
                removeAvatarBtn.style.display = 'block';
            }
        });

        document.getElementById('btn-save').addEventListener('click', async function() {
            // Check if each organization has a default role
            const grid = $("#organizationAndRoleGrid").data("kendoGrid");
            const organizations = new Map(); // Map to track organizations and their selected roles

            grid.dataSource.data().forEach(item => {
                if (!organizations.has(item.organization_id)) {
                    organizations.set(item.organization_id, {
                        name: item.organization,
                        hasSelectedRoles: false
                    });
                }

                // Check if this org has any selected roles
                if (checkedRoles.has(item.id)) {
                    organizations.get(item.organization_id).hasSelectedRoles = true;
                }
            });

            // Find organizations that have selected roles but no default role
            const orgsWithoutDefault = Array.from(organizations.entries())
                .filter(([orgId, org]) => org.hasSelectedRoles && !defaultRoleIds[orgId])
                .map(([_, org]) => org.name);

            if (orgsWithoutDefault.length > 0) {
                KendoDialog.alert({
                    title: "Error",
                    content: "Harap tetapkan peran default untuk organisasi: " + orgsWithoutDefault.join(", "),
                    type: 'error'
                });
                return;
            }

            // Prepare JSON data object
            const jsonData = {
                name: document.getElementById('name').value,
                phone: document.getElementById('phone').value,
                username: document.getElementById('username').value,
                email: document.getElementById('email').value,
                _token: '{{ csrf_token() }}',
                _method: '{{ isset($user) ? 'PUT' : 'POST' }}',
                selected_roles: Array.from(checkedRoles), // Convert Set to Array
                default_roles: defaultRoleIds
            };

            // Add password
            if (document.getElementById('password').value) {
                jsonData.password = document.getElementById('password').value;
                jsonData.password_confirmation = document.getElementById('password_confirmation').value;
            }

            // Handle avatar removal
            const avatarRemove = document.getElementById('avatarRemove');
            if (avatarRemove.value === '1') {
                jsonData.avatar_remove = '1';
            }

            try {
                // Clear any previous validation errors
                clearValidationErrors();
                
                kendo.ui.progress($(document.body), true);
                const button = document.getElementById('btn-save');
                button.disabled = true;
                button.innerHTML = 'Menyimpan...';

                // Create FormData object for file upload
                const formData = new FormData();
                
                // Add avatar file if present
                const avatarInput = document.getElementById('avatarInput');
                if (avatarInput.files.length > 0) {
                    formData.append('avatar', avatarInput.files[0]);
                }
                
                // Add all the form fields
                Object.keys(jsonData).forEach(key => {
                    if (Array.isArray(jsonData[key])) {
                        jsonData[key].forEach(item => {
                            formData.append(key + '[]', item);
                        });
                    } else if (typeof jsonData[key] === 'object' && jsonData[key] !== null) {
                        Object.keys(jsonData[key]).forEach(subKey => {
                            formData.append(key + '[' + subKey + ']', jsonData[key][subKey]);
                        });
                    } else {
                        formData.append(key, jsonData[key]);
                    }
                });

                const response = await axios({
                    method: 'POST',
                    url: '{{ $user ? route('users.update', customEncrypt($user->id)) : route('users.store') }}',
                    data: formData,
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                KendoDialog.alert({
                    title: "Berhasil",
                    content: response.data.message,
                    type: "success",
                    onClose: function() {
                        @if (!isset($user))
                            window.location.href = '{{ url("$url") }}';
                        @else
                            window.location.reload();
                        @endif
                    }
                });
            } catch (error) {
                console.error('Error:', error);
                
                // Handle validation errors (422 status)
                if (error.response?.status === 422 && error.response?.data?.errors) {
                    displayValidationErrors(error.response.data.errors);
                    
                    KendoDialog.alert({
                        title: "Error Validasi",
                        content: "Harap perbaiki kesalahan pada form",
                        type: "error"
                    });
                } else {
                    // Handle other errors
                    KendoDialog.alert({
                        title: "Error",
                        content: error.response?.data?.message || "Terjadi kesalahan internal server",
                        type: "error"
                    });
                }
            } finally {
                const button = document.getElementById('btn-save');
                button.disabled = false;
                button.innerHTML = 'Simpan';
                kendo.ui.progress($(document.body), false);
            }
        });

        $(document).ready(function() {
            $("#organizationAndRoleGrid").kendoGrid({
                dataSource: {
                    transport: {
                        read: {
                            url: "{{ route('users.ajax.organization-and-role') }}",
                            type: "POST",
                            dataType: "json",
                            data: function() {
                                return {
                                    _token: "{{ csrf_token() }}",
                                    user_id: "{{ isset($user) ? customEncrypt($user->id) : '' }}"
                                };
                            }
                        }
                    },
                    schema: {
                        data: "data",
                        total: "total",
                        model: {
                            id: "id",
                            fields: {
                                id: { type: "string" },
                                organization: { type: "string" },
                                role: { type: "string" },
                                description: { type: "string" },
                                is_selected: { type: "boolean" },
                                is_default: { type: "boolean" }
                            }
                        }
                    },
                    group: [{
                        field: "organization",
                        title: "Organisasi",
                        dir: "asc"
                    }],
                    pageSize: 25,
                    serverPaging: true,
                    serverFiltering: true,
                    serverSorting: true,
                    requestEnd: function(e) {
                        // Initialize selected roles and default roles from response data
                        if (e.type === "read") {
                            e.response.data.forEach(function(item) {
                                if (item.is_selected && !uncheckedRoles.has(item.id)) {
                                    checkedRoles.add(item.id); // Add to checked roles set
                                    if (item.is_default) {
                                        defaultRoleIds[item.organization_id] = item.id;
                                    }
                                }
                            });
                        }
                    }
                },
                height: 700,
                sortable: false,
                filterable: {
                    mode: "row",
                    operators: {
                        string: {
                            contains: "Contains"
                        },
                    }
                },
                toolbar: [{
                    template: function() {
                        return `<div class="k-toolbar-item k-toolbar-first-visible">
                            <input type="text" class="kt-input"
                                placeholder="Cari organisasi..."
                                onkeyup="searchOrganization(this.value)">
                            </div>`;
                    }
                }],
                pageable: {
                    refresh: true,
                    pageSizes: [10, 25, 50],
                    buttonCount: 5
                },
                columns: [{
                        template: function(dataItem) {
                            const checkboxId = "role_" + dataItem.id;
                            const isChecked = checkedRoles.has(dataItem.id) && !uncheckedRoles.has(dataItem.id);
                            return '<div class="flex items-center gap-2">' +
                                '<input type="checkbox" class="checkbox" id="' + checkboxId + '" ' +
                                'data-organization="' + dataItem.organization + '" ' +
                                'data-organization-id="' + dataItem.organization_id + '" ' +
                                (isChecked ? 'checked' : '') + ' ' +
                                'onchange="handleRoleSelection(this, \'' + dataItem.id + '\', \'' +
                                dataItem.organization_id + '\')">' +
                                '</div>';
                        },
                        width: 80
                    },
                    {
                        field: "organization",
                        title: "Organisasi",
                        hidden: true
                    },
                    {
                        field: "role",
                        title: "Peran",
                        filterable: {
                            cell: {
                                operator: "contains",
                                showOperators: false,
                                template: function(e) {
                                    e.element.kendoTextBox();
                                }
                            }
                        },
                        width: 200
                    },
                    {
                        field: "description",
                        title: "Deskripsi",
                        width: 300,
                        filterable: false,
                    },
                    {
                        template: function(dataItem) {
                            const btnClass = dataItem.is_default ? 'kt-btn-success' : 'kt-btn-secondary';
                            const escapedOrg = dataItem.organization_id.replace(/'/g, "\\'");
                            return '<button type="button" class="kt-btn ' + btnClass + ' kt-btn-sm" ' +
                                'onclick="toggleDefaultRole(\'' + dataItem.id + '\', \'' +
                                escapedOrg + '\', this)">' +
                                'Set Default</button>';
                        },
                        title: "Set Default",
                        width: 120
                    }
                ]
            });
        });

        let searchTimeout;

        function searchOrganization(value) {
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(function() {
                const grid = $("#organizationAndRoleGrid").data("kendoGrid");
                const filters = [];

                if (value) {
                    filters.push({
                        field: "organization",
                        operator: "contains",
                        value: value
                    });
                }

                grid.dataSource.filter({
                    filters: filters
                });
            }, 500);
        }

        function handleRoleSelection(checkbox, roleId, organization) {
            if (checkbox.checked) {
                checkedRoles.add(roleId); // Add to checked roles set
                uncheckedRoles.delete(roleId); // Remove from unchecked set
            } else {
                checkedRoles.delete(roleId); // Remove from checked roles set
                uncheckedRoles.add(roleId); // Add to unchecked set
                if (defaultRoleIds[organization] === roleId) {
                    delete defaultRoleIds[organization];
                    // Update button appearance
                    const button = checkbox.closest('tr').querySelector('.kt-btn');
                    button.classList.remove('kt-btn-success');
                    button.classList.add('kt-btn-secondary');
                }
            }
        }

        function toggleDefaultRole(roleId, organization, button) {
            // First check if role is selected
            const checkbox = document.getElementById('role_' + roleId);
            if (!checkbox.checked) {
                checkbox.checked = true;
                handleRoleSelection(checkbox, roleId, organization);
            }

            // Reset buttons for the same organization to default state
            const buttons = document.querySelectorAll('.kt-btn-success');
            buttons.forEach(btn => {
                const btnOrg = btn.closest('tr').querySelector('.checkbox').dataset.organizationId;
                if (btnOrg === organization) {
                    btn.classList.remove('kt-btn-success');
                    btn.classList.add('kt-btn-secondary');
                }
            });

            // Toggle current button
            if (defaultRoleIds[organization] === roleId) {
                delete defaultRoleIds[organization];
                button.classList.remove('kt-btn-success');
                button.classList.add('kt-btn-secondary');
            } else {
                defaultRoleIds[organization] = roleId;
                button.classList.remove('kt-btn-secondary');
                button.classList.add('kt-btn-success');
            }
        }
    </script>
@endpush