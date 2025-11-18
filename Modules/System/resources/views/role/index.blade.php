@extends('layouts.app')

@section('title', 'Manajemen Peran')

@section('content')
    <!-- Header Section -->
    <div class="kt-container-fixed">
        <div class="kt-card bg-primary/5 border-0 shadow-sm mb-5">
            <div class="kt-card-content p-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center size-10 bg-primary/10 rounded-lg">
                                <i class="ki-duotone ki-security-user text-lg text-primary"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-foreground">Manajemen Peran</h1>
                                <p class="text-sm text-muted-foreground">
                                    Kelola peran dan izin untuk mengontrol akses sistem
                                </p>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="flex gap-3 mt-1">
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-card rounded-lg border">
                                <i class="ki-duotone ki-badge text-xs text-primary"></i>
                                <span class="text-sm font-medium">{{ count($roles) }} Peran</span>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-card rounded-lg border">
                                <i class="ki-duotone ki-users text-xs text-success"></i>
                                <span class="text-sm font-medium">{{ $roles->sum('granted_users') }} Pengguna</span>
                            </div>
                        </div>
                    </div>

                    <!-- Organization Chooser -->
                    <div class="min-w-[250px]">
                        <div class="kt-card border shadow-sm">
                            <div class="kt-card-content p-3">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="ki-duotone ki-building text-xs text-muted-foreground"></i>
                                    <span class="text-sm font-medium">Pilih Organisasi</span>
                                </div>
                                <select id="kd-organization-chooser" class="w-full"></select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="kt-container-fixed">
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($roles as $role)
                <div class="kt-card group hover:shadow-lg transition-all duration-200 border shadow-sm">
                    <!-- Card Header -->
                    <div class="p-4 border-b">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="flex items-center justify-center size-10 bg-primary/10 rounded-lg">
                                        <i class="ki-duotone ki-security-user text-lg text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-foreground">{{ $role->name }}</h3>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <span class="text-xs text-muted-foreground">Dibuat</span>
                                        <span
                                            class="text-xs text-muted-foreground">{{ \Carbon\Carbon::parse($role->created_at)->isoFormat('DD MMMM YYYY') }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="kt-card-content p-4">
                        <!-- Description -->
                        <p class="text-sm text-muted-foreground mb-3 line-clamp-2">
                            {{ $role->description ?: 'Tidak ada deskripsi tersedia untuk peran ini.' }}
                        </p>

                        <!-- Statistics -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex items-center gap-2 p-2.5 bg-success/5 rounded-lg border border-success/20">
                                <div class="flex items-center justify-center size-7 bg-success/10 rounded-md">
                                    <i class="ki-duotone ki-people text-xs text-success"></i>
                                </div>
                                <div>
                                    <div class="text-base font-bold text-foreground">{{ $role->granted_users }}</div>
                                    <div class="text-xs text-muted-foreground">Pengguna</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 p-2.5 bg-primary/5 rounded-lg border border-primary/20">
                                <div class="flex items-center justify-center size-7 bg-primary/10 rounded-md">
                                    <i class="ki-duotone ki-shield-tick text-xs text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-base font-bold text-foreground">{{ $role->permissions_count }}</div>
                                    <div class="text-xs text-muted-foreground">Izin</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="kt-card-footer bg-muted/20 p-3 border-t justify-between">
                        <a href="{{ url("$url/" . customEncrypt($role->id) . '/edit') }}"
                            class="kt-btn kt-btn-sm kt-btn-primary kt-btn-outline">
                            <i class="ki-duotone ki-notepad-edit text-xs"></i>
                            Edit
                        </a>
                        <button class="kt-btn kt-btn-sm kt-btn-destructive kt-btn-outline delete-role"
                            data-role-id="{{ customEncrypt($role->id) }}">
                            <i class="ki-duotone ki-trash text-xs"></i>
                            Hapus
                        </button>
                    </div>
                </div>
            @endforeach

            <!-- Add New Role Card -->
            <a class="kt-card border-2 border-dashed border-primary/30 hover:border-primary/50 bg-primary/5 hover:bg-primary/10 transition-all duration-200 group"
                href="{{ url("$url/create?organization_id=" . customEncrypt($organization->id)) }}">
                <div class="kt-card-content flex items-center justify-center min-h-[250px] p-6">
                    <div class="text-center space-y-3">
                        <div
                            class="flex items-center justify-center size-12 bg-primary/10 rounded-xl group-hover:bg-primary/20 transition-colors mx-auto">
                            <i class="ki-duotone ki-plus-squared text-2xl text-primary"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-foreground group-hover:text-primary transition-colors">
                                Buat Peran Baru
                            </h3>
                            <p class="text-sm text-muted-foreground mt-1">
                                Tambahkan peran baru dengan izin khusus
                            </p>
                        </div>
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary/10 rounded-full text-primary text-sm font-medium">
                            <i class="ki-duotone ki-rocket text-xs"></i>
                            Mulai Sekarang
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* Line clamp utility */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            const organizationId = `{{ customEncrypt($organization->id) }}`;
            const organizationName = "{!! $organization->name !!}";

            // Delete role handler
            $(document).on('click', '.delete-role', function(e) {
                e.preventDefault();
                const roleId = $(this).data('role-id');

                KendoDialog.confirm({
                    title: "Hapus Peran",
                    content: "Apakah Anda yakin ingin menghapus peran ini? Tindakan ini tidak dapat dibatalkan.",
                    confirmText: "Ya, Hapus",
                    cancelText: "Batal",
                    onConfirm: function() {
                        const loadingDialog = KendoDialog.loading({
                            title: "Memproses...",
                            content: "Mohon tunggu sebentar..."
                        });

                        axios.delete(`{{ url("$url") }}/${roleId}`)
                            .then(response => {
                                loadingDialog.close();

                                KendoDialog.alert({
                                    title: "Berhasil",
                                    content: response.data.message ||
                                        "Peran berhasil dihapus",
                                    type: 'success',
                                    onClose: function() {
                                        window.location.reload();
                                    }
                                });
                            })
                            .catch(error => {
                                loadingDialog.close();

                                KendoDialog.alert({
                                    title: "Error",
                                    content: error.response?.data?.message ||
                                        "Terjadi kesalahan saat menghapus peran",
                                    type: 'error'
                                });
                            });
                    }
                });
            });

            // Organization dropdown with optimized performance
            $("#kd-organization-chooser").kendoDropDownList({
                optionLabel: 'Pilih Organisasi',
                dataTextField: "name",
                dataValueField: "id",
                filter: "contains",
                value: organizationId,
                height: 300,
                delay: 0,
                serverFiltering: false,
                dataSource: {
                    transport: {
                        read: {
                            url: "{{ route('organizations.ajax.datagrid') }}",
                            type: "POST",
                            dataType: "json",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        }
                    },
                    schema: {
                        data: "data"
                    }
                },
                dataBound: function() {
                    const dropdownlist = this;
                    const dataSource = dropdownlist.dataSource;

                    // Check if the current value exists in the dataset
                    const hasValue = dataSource.data().some(function(item) {
                        return item.id === organizationId;
                    });

                    // If value doesn't exist, add it manually
                    if (!hasValue) {
                        dataSource.add({
                            id: organizationId,
                            name: organizationName
                        });
                        dropdownlist.value(organizationId);
                    }
                },
                change: function(e) {
                    const selectedValue = e.sender.value();
                    window.location.href = `{{ url("$url") }}?organization_id=${selectedValue}`;
                }
            });
        });
    </script>
@endpush
