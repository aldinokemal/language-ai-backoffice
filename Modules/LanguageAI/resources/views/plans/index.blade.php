@extends('layouts.app')

@section('title', 'Plans')

@section('toolbar-actions')
    @can(\App\Enums\Permission::LANGUAGE_AI_PLANS_CREATE->value)
        <a href="{{ route('language-ai.plans.upsert') }}" class="kt-btn kt-btn-sm kt-btn-primary">
            <i class="ki-filled ki-plus"></i>
            Create Plan
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
        $(document).ready(function () {
            const computedGridHeight = Math.max(500, document.documentElement.clientHeight - 280);

            $("#grid").kendoGrid({
                dataSource: {
                    transport: {
                        read: {
                            url: "{{ route('language-ai.plans.ajax.datagrid') }}",
                            dataType: "json",
                            type: "POST",
                        },
                        destroy: {
                            url: function(data) {
                                return "{{ url('language-ai/plans') }}/" + data.id;
                            },
                            dataType: "json",
                            type: "DELETE",
                        }
                    },
                    pageSize: 25,
                    schema: {
                        model: {
                            id: "id",
                            fields: {
                                id: { type: "string" },
                                plan_name: { type: "string" },
                                plan_code: { type: "string" },
                                price: { type: "number" },
                                currency: { type: "string" },
                                interval: { type: "string" },
                                is_active: { type: "boolean" },
                                is_popular: { type: "boolean" },
                                is_displayed: { type: "boolean" },
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
                scrollable: true,
                columns: [
                    {
                        field: "plan_name",
                        title: "Name",
                        width: "200px",
                        filterable: {
                            cell: {
                                showOperators: false,
                                operator: "contains"
                            }
                        }
                    },
                    {
                        field: "plan_code",
                        title: "Code",
                        width: "150px",
                        filterable: {
                            cell: {
                                showOperators: false,
                                operator: "contains"
                            }
                        }
                    },
                    {
                        field: "price",
                        title: "Price",
                        width: "120px",
                        template: function(dataItem) {
                            return dataItem.currency + ' ' + kendo.toString(dataItem.price, "n2");
                        },
                        filterable: false
                    },
                    {
                        field: "interval",
                        title: "Interval",
                        width: "100px",
                        filterable: false
                    },
                    {
                        field: "is_active",
                        title: "Active",
                        width: "100px",
                        template: function (dataItem) {
                            return dataItem.is_active
                                ? '<span class="kt-badge kt-badge-success kt-badge-sm">Yes</span>'
                                : '<span class="kt-badge kt-badge-secondary kt-badge-sm">No</span>';
                        },
                        filterable: {
                            cell: {
                                template: function (args) {
                                    args.element.kendoDropDownList({
                                        dataSource: [
                                            { text: "All", value: "" },
                                            { text: "Yes", value: "true" },
                                            { text: "No", value: "false" }
                                        ],
                                        dataTextField: "text",
                                        dataValueField: "value",
                                        valuePrimitive: true,
                                        optionLabel: "All"
                                    });
                                },
                                showOperators: false
                            }
                        }
                    },
                    {
                        title: "Status",
                        width: "150px",
                        template: function(dataItem) {
                            let html = '';
                            if (dataItem.is_popular) html += '<span class="kt-badge kt-badge-outline kt-badge-warning kt-badge-sm me-1">Popular</span>';
                            if (dataItem.is_displayed) html += '<span class="kt-badge kt-badge-outline kt-badge-info kt-badge-sm">Displayed</span>';
                            return html;
                        },
                        filterable: false
                    },
                    {
                        command: [
                            @can(\App\Enums\Permission::LANGUAGE_AI_PLANS_UPDATE->value)
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
                                    window.location.href = "{{ url('language-ai/plans/upsert') }}/" + data.id;
                                }
                            },
                            @endcan
                            @can(\App\Enums\Permission::LANGUAGE_AI_PLANS_DELETE->value)
                            {
                                name: "destroy",
                                text: "Delete",
                                icon: 'trash',
                                size: 'small',
                                themeColor: 'error',
                                fillMode: 'outline'
                            }
                            @endcan
                        ],
                        width: 160
                    }
                ]
            });
        });
    </script>
@endpush

