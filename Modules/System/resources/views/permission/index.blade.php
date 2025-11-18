@extends('layouts.app')

@section('title', 'Manajemen Izin')

@section('toolbar-actions')
    @can('system.permissions.create')
        <button id="addPermissionBtn" class="kt-btn kt-btn-primary kt-btn-sm">
            <i class="ki-filled ki-plus"></i>
            Tambah Izin
        </button>
    @endcan
@endsection

@section('content')
    <div class="kt-container-fixed">
        <div id="grid"></div>
    </div>
@endsection

@push('scripts')
    <script>
        function deletePermission(e) {
            e.preventDefault();

            const tr = $(e.target).closest("tr");
            const data = this.dataItem(tr);

            KendoDialog.confirm({
                title: "Hapus Izin",
                content: "Apakah Anda yakin ingin menghapus izin ini? Tindakan ini tidak dapat dibatalkan.",
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
                            content: "Izin berhasil dihapus",
                            type: 'success'
                        });
                    }).catch((xhr) => {
                        loadingDialog.close();
                        KendoDialog.alert({
                            title: "Error",
                            content: xhr.responseJSON?.message || "Terjadi kesalahan saat menghapus izin",
                            type: 'error'
                        });
                    });
                }
            });
        }

        $(document).ready(function() {
            const isMobile = window.matchMedia('(max-width: 1024px)').matches;
            const computedGridHeight = isMobile ? 600 : Math.max(document.documentElement.clientHeight - 220, 550);
            const crudServiceBaseUrl = "{{ url('/system/permissions') }}",
                dataSource = new kendo.data.DataSource({
                    transport: {
                        read: {
                            url: "{{ route('permissions.ajax.datagrid') }}",
                            type: "POST",
                            dataType: "json"
                        },
                        update: {
                            url: function(data) {
                                return crudServiceBaseUrl + "/" + data.models[0].id;
                            },
                            dataType: "json",
                            type: "PUT"
                        },
                        destroy: {
                            url: function(data) {
                                return crudServiceBaseUrl + "/" + data.models[0].id;
                            },
                            dataType: "json",
                            type: "DELETE"
                        },
                        create: {
                            url: crudServiceBaseUrl,
                            dataType: "json",
                            type: "POST"
                        },
                        parameterMap: function(options, operation) {
                            if (operation !== "read") {
                                if (options.models) {
                                    return {
                                        models: kendo.stringify(options.models)
                                    };
                                }
                            } else {
                                return {
                                    page: options.page,
                                    pageSize: options.pageSize,
                                    filter: options.filter
                                };
                            }
                        },
                    },
                    batch: true,
                    pageSize: 20,
                    serverPaging: true,
                    serverFiltering: true,
                    group: {
                        field: "menu_name",
                        title: "Menu"
                    },
                    schema: {
                        model: {
                            id: "id",
                            fields: {
                                id: {
                                    editable: false,
                                    nullable: true
                                },
                                name: {
                                    validation: {
                                        required: true
                                    },
                                    type: "string"
                                },
                                alias: {
                                    validation: {
                                        required: true
                                    },
                                    type: "string"
                                },
                                guard_name: {
                                    validation: {
                                        required: true
                                    },
                                    type: "string",
                                    defaultValue: "web"
                                },
                                menu_name: {
                                    editable: false,
                                    type: "string"
                                },
                                menu_id: {
                                    validation: {
                                        required: true
                                    },
                                    type: "string"
                                }
                            }
                        },
                        total: "total",
                        data: "data"
                    },
                    requestEnd: function(e) {
                        if (e.type !== "read") {
                            this.read();
                        }
                    }
                });

            $("#grid").kendoGrid({
                dataSource: dataSource,
                pageable: {
                    refresh: true,
                    pageSizes: true,
                    buttonCount: 5
                },
                height: computedGridHeight,
                toolbar: false,
                groupable: false,
                filterable: {
                    mode: "row"
                },
                adaptiveMode: "auto",
                scrollable: true,
                columns: [{
                        field: "name",
                        title: "Nama Izin",
                        width: "350px",
                        filterable: {
                            cell: {
                                operator: "contains",
                                showOperators: false,
                                template: function(e) {
                                    e.element.kendoTextBox();
                                }
                            }
                        }
                    },
                    {
                        field: "alias",
                        title: "Alias",
                        width: "180px",
                        filterable: {
                            cell: {
                                operator: "contains",
                                showOperators: false
                            }
                        }
                    },
                    {
                        field: "guard_name",
                        title: "Guard",
                        width: "120px",
                        filterable: {
                            cell: {
                                operator: "contains",
                                showOperators: false
                            }
                        }
                    },
                    {
                        field: "menu_id",
                        title: "Menu",
                        width: "250px",
                        template: "#: menu_name #",
                        editor: function(container, options) {
                            $('<input name="' + options.field + '" required />')
                                .appendTo(container)
                                .kendoDropDownTree({
                                    dataSource: {
                                        transport: {
                                            read: {
                                                url: "{{ route('menus.ajax.tree-menu') }}",
                                                dataType: "json"
                                            }
                                        },
                                        schema: {
                                            model: {
                                                id: "id",
                                                hasChildren: "hasChildren",
                                                children: "items",
                                                fields: {
                                                    id: {
                                                        type: "string"
                                                    },
                                                    text: {
                                                        type: "string"
                                                    },
                                                    parentId: {
                                                        field: "parentId",
                                                        nullable: true
                                                    },
                                                    hasChildren: {
                                                        type: "boolean"
                                                    },
                                                    items: {
                                                        defaultValue: []
                                                    }
                                                }
                                            }
                                        }
                                    },
                                    dataTextField: "text",
                                    dataValueField: "id",
                                    filter: "contains",
                                    clearButton: true,
                                    height: "auto",
                                    value: options.model.menu_id || "",
                                    change: function(e) {
                                        var value = this.value();
                                        var dataItem = this.dataSource.get(value);
                                        if (dataItem) {
                                            options.model.set("menu_id", value);
                                            options.model.set("menu_name", dataItem.text);
                                        } else {
                                            options.model.set("menu_id", "");
                                            options.model.set("menu_name", "");
                                        }
                                    }
                                });
                        },
                        filterable: false
                    },
                    {
                        field: "menu_name",
                        title: "Menu",
                        width: "220px",
                        hidden: true
                    },
                    {
                        command: [{
                                name: 'edit',
                                size: 'small',
                                icon: 'pencil',
                                themeColor: 'primary',
                                fillMode: 'outline',
                            },
                            {
                                name: "customDestroy",
                                text: "Hapus",
                                size: 'small',
                                icon: 'trash',
                                themeColor: 'error',
                                fillMode: 'outline',
                                click: deletePermission
                            }
                        ],
                        title: "&nbsp;",
                        width: "250px",
                        filterable: false
                    }
                ],
                editable: "inline"
            });

            // Handle add permission button click
            $('#addPermissionBtn').click(function() {
                const grid = $("#grid").data("kendoGrid");
                grid.addRow();
            });
        });
    </script>
@endpush
