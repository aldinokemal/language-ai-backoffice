@extends('layouts.app')

@section('title', $role ? 'Edit Role' : 'Tambah Role')

@section('toolbar-actions')
    <a href="{{ goBack($url) }}" class="kt-btn kt-btn-sm kt-btn-outline">
        <i class="ki-filled ki-arrow-left"></i>
        Kembali
    </a>
@endsection

@section('content')
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Role Basic Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">{{ $role ? 'Edit Role' : 'Tambah Role' }}</h2>
                        <p class="text-sm text-muted-foreground">
                            {{ $role ? 'Perbarui informasi role' : 'Buat role baru dalam sistem' }}</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    <form id="roleForm" method="POST"
                        action="{{ $role ? route('roles.update', customEncrypt($role->id)) : route('roles.store') }}">
                        @csrf
                        @if ($role)
                            @method('PUT')
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="kt-label text-sm font-medium">Nama Role</label>
                                    <input type="text" name="name" class="kt-input"
                                        value="{{ old('name', $role?->name) }}" placeholder="Masukkan nama role" required>
                                    @error('name')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <input type="text" name="guard_name" class="kt-input" value="web" hidden
                                    placeholder="Masukkan guard role" required>


                                <div>
                                    <label class="kt-label text-sm font-medium">Organisasi</label>
                                    <select name="organization_id" id="organizationSelect" class="kt-select" required>
                                        <option value="">Pilih Organisasi</option>
                                    </select>
                                    @error('organization_id')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="kt-label text-sm font-medium">Deskripsi</label>
                                    <textarea name="description" class="kt-textarea" rows="5" placeholder="Masukkan deskripsi role (opsional)">{{ old('description', $role?->description) }}</textarea>
                                    @error('description')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" id="saveButton" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i>
                                {{ $role ? 'Perbarui Role' : 'Simpan Role' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($role)
                <!-- Role Permissions -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <div class="kt-card-heading">
                            <h2 class="kt-card-title">Permissions Role <span id="permissionsCount"></span></h2>
                            <p class="text-sm text-muted-foreground">Kelola permissions yang diberikan pada role ini</p>
                        </div>
                        <div class="kt-card-toolbar">
                            <button type="button" id="savePermissionsButton" class="kt-btn kt-btn-primary kt-btn-sm">
                                <i class="ki-filled ki-check"></i>
                                Simpan Permissions
                            </button>
                        </div>
                    </div>
                    <div class="kt-card-content">
                        <div id="permissionsGrid"></div>
                    </div>
                </div>

                <!-- Role Granted Users -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <div class="kt-card-heading">
                            <h2 class="kt-card-title">Pengguna yang Memiliki Role <span id="grantedUsersCount"></span></h2>
                            <p class="text-sm text-muted-foreground">Daftar pengguna yang memiliki role ini</p>
                        </div>
                        <div class="kt-card-toolbar">
                            <button type="button" id="saveUsersButton" class="kt-btn kt-btn-primary kt-btn-sm">
                                <i class="ki-filled ki-check"></i>
                                Simpan Pengguna
                            </button>
                        </div>
                    </div>
                    <div class="kt-card-content">
                        <div id="usersGrid"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Organization Select
            $("#organizationSelect").kendoDropDownList({
                dataTextField: "name",
                dataValueField: "id",
                placeholder: "Pilih Organisasi",
                dataSource: {
                    transport: {
                        read: {
                            url: "{{ route('organizations.ajax.datagrid') }}",
                            dataType: "json",
                            type: "POST"
                        }
                    },
                    schema: {
                        data: "data"
                    }
                },
                @if ($role && $role->organization_id)
                    value: "{{ customEncrypt($role->organization_id) }}"
                @endif
            });

            // Save Role Form
            $("#saveButton").click(function(e) {
                e.preventDefault();

                const form = $("#roleForm");
                const formData = new FormData(form[0]);

                const loadingDialog = KendoDialog.loading({
                    title: "Menyimpan...",
                    content: "Mohon tunggu sebentar..."
                });

                axios.post(form.attr('action'), formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    loadingDialog.close();

                    KendoDialog.alert({
                        title: "Berhasil",
                        content: response.data.message,
                        type: 'success',
                        onClose: function() {
                            @if (!$role)
                                window.location.href = "{{ route('roles.index') }}";
                            @else
                                window.location.reload();
                            @endif
                        }
                    });
                }).catch(error => {
                    loadingDialog.close();

                    let message = "Gagal menyimpan role";
                    if (error.response?.data?.errors) {
                        const errors = Object.values(error.response.data.errors).flat();
                        message = errors.join(', ');
                    } else if (error.response?.data?.message) {
                        message = error.response.data.message;
                    }

                    KendoDialog.alert({
                        title: "Error",
                        content: message,
                        type: 'error'
                    });
                });
            });

            @if ($role)
                // Store selected permissions globally
                window.selectedPermissionIds = new Set();
                window.uncheckedPermissionIds = new Set();

                // Initialize Permissions Grid
                $("#permissionsGrid").kendoGrid({
                    dataSource: {
                        transport: {
                            read: {
                                url: "{{ route('roles.ajax.role-permissions') }}",
                                dataType: "json",
                                type: "POST",
                                data: {
                                    role_id: "{{ customEncrypt($role->id) }}"
                                }
                            }
                        },
                        pageSize: 50,
                        schema: {
                            model: {
                                id: "id",
                                fields: {
                                    id: { type: "string" },
                                    name: { type: "string" },
                                    alias: { type: "string" },
                                    guard_name: { type: "string" },
                                    menu_name: { type: "string" },
                                    selected: { type: "boolean" }
                                }
                            },
                            data: "data",
                            total: "total"
                        },
                        serverPaging: true,
                        serverFiltering: true,
                        serverSorting: true,
                        group: {
                            field: "menu_name",
                            title: "Menu"
                        },
                        requestEnd: function(e) {
                            if (e.type === "read") {
                                e.response.data.forEach(function(item) {
                                    if (item.selected && !window.uncheckedPermissionIds.has(item.id)) {
                                        window.selectedPermissionIds.add(item.id);
                                    }
                                });
                            }
                        }
                    },
                    height: 600,
                    sortable: false,
                    filterable: {
                        mode: "row",
                        operators: {
                            string: {
                                contains: "Contains"
                            }
                        }
                    },
                    pageable: {
                        refresh: true,
                        pageSizes: [10, 25, 50, 100],
                        buttonCount: 5
                    },
                    dataBound: function() {
                        const grid = this;
                        grid.tbody.find("tr").each(function() {
                            const dataItem = grid.dataItem($(this));
                            if (dataItem && (dataItem.selected || window.selectedPermissionIds.has(dataItem.id)) && !window.uncheckedPermissionIds.has(dataItem.id)) {
                                grid.select($(this));
                            }
                        });

                        $("#permissionsCount").text(`(${window.selectedPermissionIds.size})`);
                    },
                    columns: [{
                            selectable: true,
                            width: 50
                        },
                        {
                            field: "name",
                            title: "Permission Name",
                            width: 250,
                            filterable: {
                                cell: {
                                    operator: "contains",
                                    showOperators: false
                                }
                            }
                        },
                        {
                            field: "alias",
                            title: "Alias",
                            width: 150,
                            filterable: {
                                cell: {
                                    operator: "contains",
                                    showOperators: false
                                }
                            }
                        },
                        {
                            field: "menu_name",
                            title: "Menu",
                            filterable: false,
                            hidden: true
                        }
                    ],
                    selectable: "multiple, row",
                    change: function(e) {
                        const grid = this;
                        const selectedRows = grid.select();

                        // Clear previous selections for current page
                        grid.tbody.find("tr").each(function() {
                            const dataItem = grid.dataItem($(this));
                            if (dataItem) {
                                window.selectedPermissionIds.delete(dataItem.id);
                                window.uncheckedPermissionIds.add(dataItem.id);
                            }
                        });

                        // Add current selections
                        selectedRows.each(function(index, row) {
                            const dataItem = grid.dataItem(row);
                            if (dataItem) {
                                window.selectedPermissionIds.add(dataItem.id);
                                window.uncheckedPermissionIds.delete(dataItem.id);
                            }
                        });

                        $("#permissionsCount").text(`(${window.selectedPermissionIds.size})`);
                    }
                });

                // Store selected users globally
                window.selectedUserIds = new Set();
                window.uncheckedUserIds = new Set();

                // Initialize granted users grid
                $("#usersGrid").kendoGrid({
                    dataSource: {
                        transport: {
                            read: {
                                url: "{{ route('roles.ajax.role-granted-users') }}",
                                type: "POST",
                                data: {
                                    role_id: "{{ customEncrypt($role->id) }}"
                                },
                                dataType: "json"
                            }
                        },
                        schema: {
                            data: "data",
                            total: "total"
                        },
                        pageSize: 10,
                        serverPaging: true,
                        serverFiltering: true,
                        requestEnd: function(e) {
                            if (e.type === "read") {
                                e.response.data.forEach(function(item) {
                                    if (item.selected && !window.uncheckedUserIds.has(item.id)) {
                                        window.selectedUserIds.add(item.id);
                                    }
                                });
                            }
                        }
                    },
                    height: 550,
                    sortable: false,
                    filterable: {
                        mode: "row",
                        operators: {
                            string: {
                                contains: "Contains"
                            }
                        }
                    },
                    pageable: {
                        refresh: true,
                        pageSizes: [10, 25, 50],
                        buttonCount: 5
                    },
                    columns: [{
                            selectable: true,
                            width: 50
                        },
                        {
                            field: "name",
                            title: "Nama",
                            filterable: {
                                cell: {
                                    operator: "contains",
                                    showOperators: false,
                                    template: function(e) {
                                        e.element.kendoTextBox();
                                    }
                                }
                            },
                            template: function(dataItem) {
                                var avatar = dataItem.avatar ? dataItem.avatar :
                                    '/assets/media/avatars/blank.png';
                                return `<div class="flex items-center gap-3">
                                <div class="kt-avatar size-8">
                                    <div class="kt-avatar-image">
                                       <img src="${avatar}" alt="${dataItem.name}">
                                    </div>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-foreground font-medium mb-1">${dataItem.name}</span>
                                    <span class="text-muted-foreground text-sm">${dataItem.email}</span>
                                </div>
                            </div>`;
                            }
                        }
                    ],
                    dataBound: function() {
                        const grid = this;
                        grid.tbody.find("tr").each(function() {
                            const dataItem = grid.dataItem($(this));
                            if (dataItem && (dataItem.selected || window.selectedUserIds.has(dataItem.id)) && !window.uncheckedUserIds.has(dataItem.id)) {
                                grid.select($(this));
                            }
                        });

                        $("#grantedUsersCount").text(`(${window.selectedUserIds.size})`);
                    },
                    selectable: "multiple, row",
                    change: function(e) {
                        const grid = this;
                        const selectedRows = grid.select();

                        // Clear previous selections for current page
                        grid.tbody.find("tr").each(function() {
                            const dataItem = grid.dataItem($(this));
                            if (dataItem) {
                                window.selectedUserIds.delete(dataItem.id);
                                window.uncheckedUserIds.add(dataItem.id);
                            }
                        });

                        // Add current selections
                        selectedRows.each(function(index, row) {
                            const dataItem = grid.dataItem(row);
                            if (dataItem) {
                                window.selectedUserIds.add(dataItem.id);
                                window.uncheckedUserIds.delete(dataItem.id);
                            }
                        });

                        $("#grantedUsersCount").text(`(${window.selectedUserIds.size})`);
                    }
                });

                // Save Permissions
                $("#savePermissionsButton").click(function() {
                    const permissionsGrid = $("#permissionsGrid");
                    kendo.ui.progress(permissionsGrid, true);
                    document.getElementById('savePermissionsButton').disabled = true;

                    axios.put("{{ route('roles.update-permissions', customEncrypt($role->id)) }}", {
                        permission_ids: Array.from(window.selectedPermissionIds)
                    }).then(response => {
                        KendoDialog.alert({
                            title: "Berhasil",
                            content: response.data.message,
                            type: 'success'
                        });
                    }).catch(error => {
                        KendoDialog.alert({
                            title: "Error",
                            content: error.response?.data?.message ||
                                "Gagal menyimpan permissions",
                            type: 'error'
                        });
                    }).finally(() => {
                        kendo.ui.progress(permissionsGrid, false);
                        document.getElementById('savePermissionsButton').disabled = false;
                    });
                });

                // Save Users
                $("#saveUsersButton").click(function() {
                    const grantedUsersGrid = $("#usersGrid");
                    kendo.ui.progress(grantedUsersGrid, true);
                    document.getElementById('saveUsersButton').disabled = true;

                    axios.put("{{ route('roles.update-granted-users', customEncrypt($role->id)) }}", {
                        user_ids: Array.from(window.selectedUserIds)
                    }).then(response => {
                        KendoDialog.alert({
                            title: "Berhasil",
                            content: response.data.message,
                            type: 'success'
                        });

                        // Reload grid and go to page 1
                        const grid = grantedUsersGrid.data("kendoGrid");
                        grid.dataSource.page(1);
                    }).catch(error => {
                        KendoDialog.alert({
                            title: "Error",
                            content: error.response?.data?.message ||
                                "Gagal menyimpan pengguna",
                            type: 'error'
                        });
                    }).finally(() => {
                        kendo.ui.progress(grantedUsersGrid, false);
                        document.getElementById('saveUsersButton').disabled = false;
                    });
                });
            @endif
        });
    </script>
@endpush
