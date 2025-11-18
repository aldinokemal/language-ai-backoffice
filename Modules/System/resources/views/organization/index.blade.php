@extends('layouts.app')

@section('title', 'Manajemen Organisasi')

@section('toolbar-actions')
    @can('system.organizations.create')
        <a href="{{ route('organizations.create') }}" class="kt-btn kt-btn-primary kt-btn-sm">
            <i class="ki-filled ki-plus"></i>
            Tambah Organisasi
        </a>
    @endcan
@endsection

@section('content')
<div class="kt-container-fixed">
    <div id="grid"></div>
</div>
@endsection

@push('scripts')
    <script>
        function deleteOrganization(e) {
            e.preventDefault();

            const tr = $(e.target).closest("tr");
            const data = this.dataItem(tr);

            KendoDialog.confirm({
                title: "Hapus Organisasi",
                content: "Apakah Anda yakin ingin menghapus organisasi ini? Tindakan ini tidak dapat dibatalkan.",
                confirmText: "Ya, Hapus",
                cancelText: "Batal",
                onConfirm: function() {
                    const loadingDialog = KendoDialog.loading({
                        title: "Memproses...",
                        content: "Mohon tunggu sebentar..."
                    });

                    const grid = $("#grid").data("kendoGrid");
                    grid.dataSource.remove(data);
                    grid.dataSource.sync().then(() => {
                        loadingDialog.close();
                        KendoDialog.alert({
                            title: "Berhasil",
                            content: "Organisasi berhasil dihapus",
                            type: 'success'
                        });
                    }).catch((xhr) => {
                        loadingDialog.close();
                        KendoDialog.alert({
                            title: "Error",
                            content: xhr.responseJSON?.message || "Terjadi kesalahan saat menghapus organisasi",
                            type: 'error'
                        });
                    });
                }
            });
        }

        $(document).ready(function () {
            const isMobile = window.matchMedia('(max-width: 1024px)').matches;
            const computedGridHeight = Math.max(500, document.documentElement.clientHeight - 280);
            const crudServiceBaseUrl = "{{ url("$url") }}",
                dataSource = new kendo.data.DataSource({
                    transport: {
                        read: {
                            url: "{{ route('organizations.ajax.datagrid') }}",
                            type: "POST",
                            dataType: "json"
                        },
                        destroy: {
                            url: function (data) {
                                return crudServiceBaseUrl + "/" + data.id;
                            },
                            type: "DELETE",
                            dataType: "json"
                        }
                    },
                    schema: {
                        data: "data",
                        total: "total",
                        model: {
                            id: "id",
                            fields: {
                                name: { type: "string" },
                                code: { type: "string" },
                                totalUsers: { type: "number" }
                            }
                        }
                    },
                    pageSize: 25,
                    serverPaging: true,
                    serverFiltering: true,
                    serverSorting: true,
                    error: function (e) {
                        if (e.xhr.responseJSON) {
                            KendoDialog.alert({
                                title: "Error",
                                content: e.xhr.responseJSON?.message || "Terjadi kesalahan saat memproses data",
                                type: 'error'
                            });
                        }
                        this.cancelChanges();
                    }
                });

            $("#grid").kendoGrid({
                dataSource: dataSource,
                sortable: true,
                filterable: {
                    mode: "row",
                    operators: {
                        string: {
                            contains: "Contains"
                        },
                        number: {
                            gte: ">=",
                            lte: "<=",
                            eq: "="
                        }
                    }
                },
                height: computedGridHeight,
                pageable: {
                    refresh: true,
                    pageSizes: [10, 25, 50, 100],
                    buttonCount: 3
                },
                toolbar: false,
                adaptiveMode: "auto",
                scrollable: true,
                columns: [{
                    field: "name",
                    width: '180px',
                    title: "Nama Organisasi",
                    sortable: true,
                    template: function(dataItem) {
                        return '<a style="color: #3B82F6;" href="{{ url("$url") }}/' + dataItem.id + '">' + dataItem.name + '</a>';
                    },
                    filterable: {
                        cell: {
                            operator: "contains",
                            showOperators: false,
                            template: function (e) {
                                e.element.kendoTextBox();
                            }
                        }
                    }
                },
                {
                    field: "code",
                    width: '120px',
                    title: "Kode",
                    sortable: false,
                    filterable: {
                        cell: {
                            operator: "contains",
                            showOperators: false,
                            template: function (e) {
                                e.element.kendoTextBox();
                            }
                        }
                    }
                },
                {
                    field: "address",
                    width: '260px',
                    title: "Alamat",
                    sortable: false,
                    filterable: {
                        cell: {
                            operator: "contains",
                            showOperators: false,
                            template: function (e) {
                                e.element.kendoTextBox();
                            }
                        }
                    }
                },
                {
                    field: "phone",
                    width: '150px',
                    title: "Telepon",
                    sortable: false,
                    filterable: {
                        cell: {
                            operator: "contains",
                            showOperators: false,
                            template: function (e) {
                                e.element.kendoTextBox();
                            }
                        }
                    }
                },
                {
                    command: [{
                        name: "customEdit",
                        text: "Edit",
                        icon: 'pencil',
                        size: 'small',
                        themeColor: 'primary',
                        fillMode: 'outline',
                        click: function (e) {
                            e.preventDefault();
                            const tr = $(e.target).closest("tr");
                            const data = this.dataItem(tr);
                            window.location.href = "{{ url("$url") }}/" + data.id + "/edit";
                        }
                    },
                    {
                        name: "customDestroy",
                        text: "Hapus",
                        icon: 'trash',
                        size: 'small',
                        themeColor: 'error',
                        fillMode: 'outline',
                        click: deleteOrganization
                    }],
                    title: "Aksi",
                    width: '200px'
                }
                ]
            });
        });
    </script>
@endpush
