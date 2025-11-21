@extends('layouts.app')

@section('title', 'Language AI Users')

@section('toolbar-actions')
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

        $(document).ready(function () {
            const isMobile = window.matchMedia('(max-width: 1024px)').matches;
            const computedGridHeight = Math.max(500, document.documentElement.clientHeight - 280);

            $("#grid").kendoGrid({
                dataSource: {
                    transport: {
                        read: {
                            url: "{{ route('language-ai.users.ajax.datagrid') }}",
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
                                picture: { type: "string" },
                                plan_id: { type: "string" },
                                plan_name: { type: "string" },
                                is_activated: { type: "boolean" },
                                devices_count: { type: "number" },
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
                adaptiveMode: "auto",
                scrollable: true,
                columns: [
                    {
                        field: "name",
                        width: "300px",
                        title: "User",
                        filterable: {
                            cell: {
                                showOperators: false,
                                template: function (e) {
                                    e.element.kendoTextBox();
                                }
                            },
                        },
                        template: function (dataItem) {
                            var picture = dataItem.picture ? dataItem.picture : '/assets/media/avatars/blank.png';
                            return `<div class="flex items-center gap-3">
                                <div class="kt-avatar size-8">
                                    <div class="kt-avatar-image">
                                       <img src="${picture}" alt="${dataItem.name}">
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
                        field: "plan_id",
                        width: "200px",
                        title: "Plan",
                        filterable: {
                            cell: {
                                showOperators: false,
                                template: function (args) {
                                    args.element.kendoMultiSelect({
                                        dataSource: @json($plans),
                                        dataTextField: "plan_name",
                                        dataValueField: "_id",
                                        valuePrimitive: true,
                                        placeholder: "Select plans...",
                                        autoClose: false
                                    });
                                }
                            },
                        },
                        template: function(dataItem) {
                            return `<span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm">${dataItem.plan_name}</span>`;
                        }
                    },
                    {
                        field: "is_activated",
                        width: "150px",
                        title: "Status",
                        template: function (dataItem) {
                            if (dataItem.is_activated) {
                                return '<span class="kt-badge kt-badge-success kt-badge-sm">Activated</span>';
                            }
                            return '<span class="kt-badge kt-badge-secondary kt-badge-sm">Not Activated</span>';
                        },
                        filterable: {
                            cell: {
                                template: function (args) {
                                    var dropdown = args.element.kendoDropDownList({
                                        dataSource: [
                                            { text: "All", value: "" },
                                            { text: "Activated", value: "true" },
                                            { text: "Not Activated", value: "false" }
                                        ],
                                        dataTextField: "text",
                                        dataValueField: "value",
                                        autoClose: true,
                                        change: function (e) {
                                            var value = this.value();
                                            var grid = $("#grid").data("kendoGrid");
                                            var dataSource = grid.dataSource;
                                            var currentFilter = dataSource.filter();

                                            // Build filter structure
                                            var filters = [];

                                            // Preserve existing filters (except is_activated)
                                            if (currentFilter && currentFilter.filters) {
                                                filters = currentFilter.filters.filter(function(f) {
                                                    return f.field !== "is_activated";
                                                });
                                            }

                                            // Add is_activated filter if value is not empty
                                            if (value !== "") {
                                                filters.push({
                                                    field: "is_activated",
                                                    operator: "eq",
                                                    value: value
                                                });
                                            }

                                            // Apply filter
                                            if (filters.length > 0) {
                                                dataSource.filter({
                                                    logic: "and",
                                                    filters: filters
                                                });
                                            } else {
                                                dataSource.filter({});
                                            }
                                        }
                                    }).data("kendoDropDownList");
                                },
                                showOperators: false
                            }
                        }
                    },
                    {
                        field: "devices_count",
                        width: "120px",
                        title: "Devices",
                        filterable: false,
                        template: function (dataItem) {
                            return `<span class="kt-badge kt-badge-outline kt-badge-info kt-badge-sm">${dataItem.devices_count}</span>`;
                        }
                    },
                    {
                        field: "created_at",
                        title: "Created At",
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
                                name: "customView",
                                text: "View",
                                icon: 'eye',
                                size: 'small',
                                themeColor: 'info',
                                fillMode: 'outline',
                                click: function(e) {
                                    e.preventDefault();
                                    var tr = $(e.target).closest("tr");
                                    var data = this.dataItem(tr);
                                    window.location.href = "{{ url("$url") }}/" + data.id;
                                }
                            },
                            @can('language.ai.users.update')
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
                            @endcan
                        ],
                        width: 100
                    }
                ]
            });
        })
    </script>
@endpush
