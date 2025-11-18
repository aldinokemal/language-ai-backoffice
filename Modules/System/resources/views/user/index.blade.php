@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('toolbar-actions')
    @can('system.users.create')
        <a href="{{ route('users.create') }}" class="kt-btn kt-btn-primary kt-btn-sm">
            <i class="ki-filled ki-plus"></i>
            Tambah Pengguna
        </a>
    @endcan
    @can('system.users.update')
        <div class="flex items-center gap-2">
            <button id="banButton" disabled class="kt-btn kt-btn-sm kt-btn-destructive">
                <i class="ki-filled ki-shield-slash"></i>Blokir Terpilih
            </button>
            <button id="unbanButton" disabled class="kt-btn kt-btn-sm kt-btn-secondary">
                <i class="ki-filled ki-shield-tick"></i>Buka Blokir Terpilih
            </button>
        </div>
    @endcan
@endsection

@section('content')
<div class="kt-container-fixed">
    <div id="grid"></div>
</div>
@endsection

@push('scripts')
    <script>
        function betweenFilter(args) {
            var filterCell = args.element.parents(".k-filtercell");

            filterCell.empty();
            filterCell.html(
                '<div id="daterangepicker" title="daterangepicker"></div>'
            );

            $("#daterangepicker", filterCell).kendoDateRangePicker({
                labels: false,
                change: function (e) {
                    var range = e.sender.range(),
                        startDate = range.start,
                        endDate = range.end,
                        dataSource = $("#grid").data("kendoGrid").dataSource;

                    if (startDate && endDate) {
                        var filter = {
                            logic: "and",
                            filters: []
                        };
                        filter.filters.push({
                            field: "created_at",
                            operator: "gte",
                            value: kendo.toString(startDate, "yyyy-MM-dd")
                        });
                        filter.filters.push({
                            field: "created_at",
                            operator: "lte",
                            value: kendo.toString(endDate, "yyyy-MM-dd")
                        });
                        dataSource.filter(filter);
                    }
                }
            });
        }

        @can('system.users.update')
        function banOrUnban(action) {
            const grid = $("#grid").data("kendoGrid");
            const selectedRows = grid.selectedKeyNames();
            const isBanning = action === 'banned';

            KendoDialog.confirm({
                title: isBanning ?
                    `Blokir ${selectedRows.length} Pengguna` :
                    `Buka Blokir ${selectedRows.length} Pengguna`,
                content: isBanning ?
                    `Apakah Anda yakin ingin memblokir ${selectedRows.length} pengguna yang dipilih?` :
                    `Apakah Anda yakin ingin membuka blokir ${selectedRows.length} pengguna yang dipilih?`,
                confirmText: isBanning ? "Ya, Blokir" : "Ya, Buka Blokir",
                cancelText: "Batal",
                onConfirm: function() {
                    const loadingDialog = KendoDialog.loading({
                        title: "Memproses...",
                        content: "Mohon tunggu sebentar..."
                    });

                    axios.post(`{{ url("$url/ajax/") }}/${action}`, {
                        toggledNodes: selectedRows
                    }).then(response => {
                        loadingDialog.close();
                        grid.dataSource.read();

                        KendoDialog.alert({
                            title: "Berhasil",
                            content: isBanning ? "Pengguna berhasil diblokir" : "Pengguna berhasil dibuka blokirnya",
                            type: 'success'
                        });

                        // disable button
                        $('#banButton').prop('disabled', true);
                        $('#unbanButton').prop('disabled', true);

                    }).catch(error => {
                        loadingDialog.close();

                        KendoDialog.alert({
                            title: "Error",
                            content: isBanning ? "Gagal memblokir pengguna" : "Gagal membuka blokir pengguna",
                            type: 'error'
                        });
                    });
                }
            });
        }
        @endcan

        $(document).ready(function () {
            const isMobile = window.matchMedia('(max-width: 1024px)').matches;
            const computedGridHeight = Math.max(500, document.documentElement.clientHeight - 280);

            $("#grid").kendoGrid({
                dataSource: {
                    transport: {
                        read: {
                            url: "{{ route('users.ajax.datagrid') }}",
                            dataType: "json",
                            type: "POST",
                        }
                    },
                    pageSize: 25,
                    schema: {
                        model: {
                            id: "id",
                            fields: {
                                id: { type: "string" },
                                name: { type: "string" },
                                email: { type: "string" },
                                avatar: { type: "string" },
                                organizations: { type: "string" },
                                is_banned: { type: "boolean" },
                                created_at: { type: "date" }
                            }
                        },
                        data: "data",
                        total: "total",
                    },
                    serverPaging: true,
                    serverFiltering: true,
                    serverSorting: true
                },
                height: computedGridHeight,
                filterable: {
                    mode: "row",
                },
                toolbar: false,
                resizable: true,
                reorderable: true,
                pageable: {
                    refresh: true,
                    pageSizes: [10, 25, 50, 100],
                    buttonCount: 3
                },
                sortable: true,
                change: function (e) {
                    const selectedRows = this.selectedKeyNames();
                    const banButton = document.getElementById('banButton');
                    const unbanButton = document.getElementById('unbanButton');

                    if (selectedRows.length > 0) {
                        banButton.disabled = false;
                        unbanButton.disabled = false;
                    } else {
                        banButton.disabled = true;
                        unbanButton.disabled = true;
                    }
                },
                adaptiveMode: "auto",
                scrollable: true,
                columns: [
                    {
                        selectable: true,
                        width: "35px",
                        locked: isMobile ? false : true,
                    },
                    {
                        field: "name",
                        width: "300px",
                        title: "Nama",
                        filterable: {
                            cell: {
                                showOperators: false,
                                template: function (e) {
                                    e.element.kendoTextBox();
                                }
                            },
                        },
                        template: function (dataItem) {
                            var avatar = dataItem.avatar ? dataItem.avatar : '/assets/media/avatars/blank.png';
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
                    },
                    {
                        field: "organizations",
                        width: "300px",
                        title: "Organisasi",
                        sortable: false,
                        template: function(dataItem) {
                            if (!dataItem.organizations) return '';
                            const orgs = dataItem.organizations.split('|');
                            return `<div class="flex flex-col gap-1">
                                ${orgs.map(org =>
                                    `<span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm">${org}</span>`
                                ).join('')}
                            </div>`;
                        },
                        filterable: {
                            cell: {
                                showOperators: false,
                                template: function (e) {
                                    e.element.kendoTextBox();
                                }
                            },
                        }
                    },
                    {
                        field: "is_banned",
                        width: "150px",
                        title: "Status",
                        template: function (dataItem) {
                            return dataItem.is_banned ? "Diblokir" : "Aktif";
                        },
                        filterable: {
                            cell: {
                                template: function (args) {
                                    args.element.kendoDropDownList({
                                        dataSource: [{
                                            text: "Semua",
                                            value: ""
                                        },
                                            {
                                                text: "Diblokir",
                                                value: true
                                            },
                                            {
                                                text: "Aktif",
                                                value: false
                                            }
                                        ],
                                        dataTextField: "text",
                                        dataValueField: "value",
                                        autoClose: true
                                    });
                                },
                                showOperators: false
                            }
                        }
                    },
                    {
                        field: "created_at",
                        title: "Tanggal Bergabung",
                        format: "{0:dd/MM/yyyy}",
                        width: "200px",
                        filterable: {
                            cell: {
                                operator: "between",
                                template: betweenFilter,
                                showOperators: false
                            }
                        },
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
                                click: function(e) {
                                    e.preventDefault();
                                    var tr = $(e.target).closest("tr");
                                    var data = this.dataItem(tr);
                                    window.location.href = "{{ url("$url") }}/" + data.id + "/edit";
                                }
                            },
                        ],
                        width: 100
                    }
                ]
            });

            // Handle ban/unban button clicks
            $('#banButton').click(() => banOrUnban('banned'));
            $('#unbanButton').click(() => banOrUnban('unbanned'));
        })
    </script>
@endpush
