@extends('layouts.app')

@section('title', 'Manajemen Menu')

@section('toolbar-actions')
    <a href="{{ url('system/menus/create') }}" class="kt-btn kt-btn-primary kt-btn-sm">
        <i class="ki-filled ki-plus"></i>
        Tambah Menu
    </a>
    @can('system.users.update')
        <div class="flex items-center gap-2">
            <button id="disableButton" disabled class="kt-btn kt-btn-sm kt-btn-destructive">
                <i class="ki-filled ki-cross"></i>Nonaktifkan
            </button>
            <button id="enableButton" disabled class="kt-btn kt-btn-sm kt-btn-success">
                <i class="ki-filled ki-check"></i>Aktifkan
            </button>
        </div>
    @endcan
@endsection

@section('content')
    <div class="kt-container-fixed">
        @if (session('message'))
            <div class="mt-2 mb-2 p-4 bg-green-100 border border-green-400 text-green-700">
                {{ session('message') }}
            </div>
        @endif
        <div id="grid"></div>
    </div>
@endsection

@push('styles')
    <style>
        .customEdit {
            color: #0d6efd;
            background-color: transparent;
            border: 1px solid #0d6efd;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 8px;
        }
        .customEdit:hover {
            color: #fff;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .customDestroy {
            color: #dc3545;
            background-color: transparent;
            border: 1px solid #dc3545;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 8px;
        }
        .customDestroy:hover {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            const isMobile = window.matchMedia('(max-width: 1024px)').matches;
            const computedHeight = isMobile ? 'auto' : Math.max(500, document.documentElement.clientHeight - 250);
            var dataSource = new kendo.data.TreeListDataSource({
                transport: {
                    read: {
                        url: "{{ route('menus.ajax.treegrid') }}",
                        type: "POST",
                        dataType: "json"
                    },
                    destroy: {
                        url: function (options) {
                            return "{{ url('system/menus') }}/" + options.id;
                        },
                        type: "DELETE",
                        dataType: "json"
                    },
                    update: {
                        url: "{{ url('system/menus') }}/status",
                        type: "POST",
                        dataType: "json"
                    }
                },
                schema: {
                    model: {
                        id: "id",
                        parentId: "parentId",
                        expanded: true,
                        fields: {
                            id: { type: "string", nullable: false },
                            parentId: { field: "parentId", nullable: true },
                            Name: { type: "string" },
                            Url: { type: "string" },
                            IsActive: { type: "boolean" },
                            ShowIfHasPermission: { type: "string" }
                        }
                    }
                }
            });

            var grid = $("#grid").kendoTreeList({
                dataSource: dataSource,
                height: computedHeight,
                toolbar: false,
                scrollable: isMobile ? false : true,
                columns: [
                    { selectable: true, width: "35px" },
                    { field: "Name", title: "Nama Menu", width: "350px" },
                    { field: "Url", title: "URL", width: "250px" },
                    { field: "ShowIfHasPermission", title: "Izin", width: "200px" },
                    {
                        field: "IsActive", title: "Status",
                        template: "#= IsActive ? '<span class=\"kt-badge kt-badge-success kt-badge-sm\">Aktif</span>' : '<span class=\"kt-badge kt-badge-destructive kt-badge-sm\">Nonaktif</span>' #",
                        width: "120px"
                    },
                    {
                        command: [
                            {
                                name: "customEdit",
                                text: "Edit",
                                icon: 'pencil',
                                size: 'small',
                                themeColor: 'primary',
                                fillMode: 'outline',
                                className: 'customEdit',
                                click: function (e) {
                                    e.preventDefault();
                                    var tr = $(e.target).closest("tr");
                                    var data = this.dataItem(tr);
                                    window.location.href = "{{ url('system/menus') }}/" + data.id + "/edit";
                                }
                            },
                            {
                                name: "destroy",
                                icon: 'trash',
                                size: 'small',
                                themeColor: 'error',
                                fillMode: 'outline',
                                className: 'customDestroy',
                            }
                        ],
                        width: 200
                    }
                ],
                editable: true,
                sortable: true,
                filterable: false,
                change: function (e) {
                    const selectedRows = this.select();
                    const disableButton = document.getElementById('disableButton');
                    const enableButton = document.getElementById('enableButton');

                    if (selectedRows.length > 0) {
                        disableButton.disabled = false;
                        enableButton.disabled = false;
                    } else {
                        disableButton.disabled = true;
                        enableButton.disabled = true;
                    }
                },
                remove: function (e) {
                    e.preventDefault();

                    KendoDialog.confirm({
                        title: "Hapus Menu",
                        content: "Apakah Anda yakin ingin menghapus menu ini? Tindakan ini tidak dapat dibatalkan.",
                        confirmText: "Ya, Hapus",
                        cancelText: "Batal",
                        onConfirm: function() {
                            dataSource.remove(e.model);
                            dataSource.sync();
                        }
                    });
                }
            }).data("kendoTreeList");

            $('#enableButton').click(function () {
                updateMenuStatus(true);
            });

            $('#disableButton').click(function () {
                updateMenuStatus(false);
            });

            function updateMenuStatus(isActive) {
                const selectedRows = grid.select();
                const selectedIds = [];

                selectedRows.each(function (index, row) {
                    const dataItem = grid.dataItem(row);
                    selectedIds.push(dataItem.id);
                });

                if (selectedIds.length === 0) return;

                KendoDialog.confirm({
                    title: isActive ?
                        `Aktifkan ${selectedIds.length} Menu` :
                        `Nonaktifkan ${selectedIds.length} Menu`,
                    content: isActive ?
                        `Apakah Anda yakin ingin mengaktifkan ${selectedIds.length} menu yang dipilih?` :
                        `Apakah Anda yakin ingin menonaktifkan ${selectedIds.length} menu yang dipilih?`,
                    confirmText: isActive ? "Ya, Aktifkan" : "Ya, Nonaktifkan",
                    cancelText: "Batal",
                    onConfirm: function() {
                        const loadingDialog = KendoDialog.loading({
                            title: "Memproses...",
                            content: "Mohon tunggu sebentar..."
                        });

                        $.ajax({
                            url: "{{ url("$url") }}/status",
                            type: "POST",
                            data: {
                                ids: selectedIds,
                                is_active: isActive
                            },
                            success: function () {
                                loadingDialog.close();
                                grid.dataSource.read();

                                KendoDialog.alert({
                                    title: "Berhasil",
                                    content: isActive ? "Menu berhasil diaktifkan" : "Menu berhasil dinonaktifkan",
                                    type: 'success'
                                });

                                // disable buttons
                                $('#disableButton').prop('disabled', true);
                                $('#enableButton').prop('disabled', true);
                            },
                            error: function () {
                                loadingDialog.close();

                                KendoDialog.alert({
                                    title: "Error",
                                    content: "Terjadi kesalahan saat memperbarui status menu",
                                    type: 'error'
                                });
                            }
                        });
                    }
                });
            }
        });
    </script>
@endpush
