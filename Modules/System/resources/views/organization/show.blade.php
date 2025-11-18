@extends('layouts.app')

@section('title', 'Detail Organisasi')

@push('styles')
<style>
    .kt-input-readonly {
        padding: 0.75rem 1rem;
        background-color: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        color: #374151;
        font-size: 0.875rem;
        line-height: 1.5;
    }
</style>
@endpush

@section('toolbar-actions')
<a href="{{ url($url) }}" class="kt-btn kt-btn-secondary kt-btn-sm">
    <i class="ki-filled ki-arrow-left"></i>
    Kembali
</a>
@can('system.organizations.update')
    <a href="{{ route('organizations.edit', customEncrypt($data->id)) }}" class="kt-btn kt-btn-primary kt-btn-sm ml-2">
        <i class="ki-filled ki-pencil"></i>
        Edit
    </a>
@endcan
@endsection

@section('content')
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Organization Details Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">{{ $data->name }}</h2>
                        <p class="text-sm text-muted-foreground">Detail informasi organisasi</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-6">
                        <!-- Logo Section -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                Logo Organisasi
                            </label>
                            <div class="flex-1">
                                <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-gray-200">
                                    <img src="{{ getOrganizationLogo($data) }}"
                                         alt="Logo {{ $data->name }}"
                                         class="w-full h-full object-cover">
                                </div>
                            </div>
                        </div>

                        <!-- Organization Information Grid -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">
                                    Kode Organisasi
                                </label>
                                <div class="kt-input-readonly">{{ $data->code }}</div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="kt-label">
                                    Nama Organisasi
                                </label>
                                <div class="kt-input-readonly">{{ $data->name }}</div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="kt-label">
                                Alamat
                            </label>
                            <div class="kt-input-readonly min-h-[80px] whitespace-pre-line">{{ $data->address }}</div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">
                                    Nomor Telepon
                                </label>
                                <div class="kt-input-readonly">{{ $data->phone }}</div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="kt-label">
                                    Email
                                </label>
                                <div class="kt-input-readonly">{{ $data->email }}</div>
                            </div>
                        </div>

                        @if($data->website)
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">
                                Website
                            </label>
                            <div class="kt-input-readonly">
                                <a href="{{ $data->website }}" target="_blank" class="text-primary hover:text-primary-dark">
                                    {{ $data->website }}
                                    <i class="ki-filled ki-arrow-top-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Users Grid Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">Daftar Pengguna</h2>
                        <p class="text-sm text-muted-foreground">Pengguna yang tergabung dalam organisasi ini</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div id="usersGrid"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const isMobile = window.matchMedia('(max-width: 1024px)').matches;
        const computedGridHeight = isMobile ? 'auto' : Math.max(500, document.documentElement.clientHeight - 250);

        $("#usersGrid").kendoGrid({
            dataSource: {
                transport: {
                    read: {
                        url: "{{ route('users.ajax.datagrid') }}",
                        dataType: "json",
                        type: "POST",
                        data: function() {
                            return {
                                filter: {
                                    filters: [{
                                        field: "organizations",
                                        operator: "eq",
                                        value: "{{ $data->name }}"
                                    }]
                                }
                            };
                        }
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
                    total: "total"
                },
                serverPaging: true,
                serverFiltering: true,
                serverSorting: true
            },
            height: computedGridHeight,
            filterable: {
                mode: "row"
            },
            resizable: true,
            reorderable: true,
            pageable: {
                refresh: true,
                pageSizes: [10, 25, 50, 100],
                buttonCount: 3
            },
            sortable: true,
            adaptiveMode: "auto",
            scrollable: isMobile ? false : true,
            columns: [
                {
                    field: "name",
                    width: "300px",
                    title: "Nama",
                    filterable: {
                        cell: {
                            showOperators: false,
                            template: function(e) {
                                e.element.kendoTextBox();
                            }
                        }
                    },
                    template: function(dataItem) {
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
                    field: "is_banned",
                    width: "auto",
                    title: "Status",
                    template: function(dataItem) {
                        return dataItem.is_banned ? "Diblokir" : "Aktif";
                    },
                    filterable: {
                        cell: {
                            template: function(args) {
                                args.element.kendoDropDownList({
                                    dataSource: [{
                                        text: "Semua",
                                        value: ""
                                    },
                                    {
                                        text: "Diblokir",
                                        value: "true"
                                    },
                                    {
                                        text: "Aktif",
                                        value: "false"
                                    }],
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
                    format: "{0:MM/dd/yyyy}",
                    width: "360px",
                    filterable: {
                        cell: {
                            operator: "between",
                            template: function(args) {
                                var filterCell = args.element.parents(".k-filtercell");
                                filterCell.empty();
                                filterCell.html('<div id="daterangepicker" title="daterangepicker"></div>');
                                $("#daterangepicker", filterCell).kendoDateRangePicker({
                                    labels: false,
                                    change: function(e) {
                                        var range = e.sender.range(),
                                            startDate = range.start,
                                            endDate = range.end,
                                            dataSource = $("#usersGrid").data("kendoGrid").dataSource;

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
                            },
                            showOperators: false
                        }
                    }
                }
            ]
        });
    });
</script>
@endpush
