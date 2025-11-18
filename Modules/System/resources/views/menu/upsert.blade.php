@extends('layouts.app')

@section('title', 'Manajemen Menu')

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
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">Detail Menu</h2>
                    </div>
                </div>
                <form action="{{ $menu ? route('menus.update', customEncrypt($menu->id)) : route('menus.store') }}" method="POST"
                    onsubmit="document.getElementById('btn-save').disabled = true;">
                    @csrf
                    @method($menu ? 'PUT' : 'POST')
                    
                    <div class="kt-card-content">
                        <div class="grid gap-6 max-w-4xl">
                            <!-- Menu Induk -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                                <div class="lg:pt-2">
                                    <label class="text-sm font-medium text-foreground" for="parent_id">
                                        Menu Induk
                                    </label>
                                </div>
                                <div class="lg:col-span-2">
                                    <input class="kt-input" name="parent_id" id="parent_id" placeholder="Pilih menu induk..." />
                                </div>
                            </div>

                            <!-- Nama Menu -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                                <div class="lg:pt-2">
                                    <label class="text-sm font-medium text-foreground required" for="name">
                                        Nama Menu
                                    </label>
                                </div>
                                <div class="lg:col-span-2 space-y-2">
                                    <input class="kt-input" type="text" name="name" id="name"
                                        value="{{ old('name', $menu?->name) }}" required placeholder="Masukkan nama menu" />
                                    <p class="text-xs text-muted-foreground">
                                        Pastikan nama ditambahkan dalam multi bahasa
                                    </p>
                                </div>
                            </div>

                            <!-- Ikon -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                                <div class="lg:pt-2">
                                    <label class="text-sm font-medium text-foreground" for="icon">
                                        Ikon
                                    </label>
                                </div>
                                <div class="lg:col-span-2 space-y-2">
                                    <input class="kt-input" type="text" name="icon" id="icon"
                                        value="{{ old('icon', $menu?->icon) }}" placeholder="ki-financial-schedule" />
                                    <p class="text-xs text-muted-foreground">
                                        Untuk detail lebih lanjut <a href="https://keenthemes.com/metronic/tailwind/docs/plugins/keenicons"
                                            class="text-primary hover:underline" target="_blank">Kunjungi Keen Icons</a>
                                    </p>
                                </div>
                            </div>

                            <!-- URL -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                                <div class="lg:pt-2">
                                    <label class="text-sm font-medium text-foreground" for="url">
                                        URL
                                    </label>
                                </div>
                                <div class="lg:col-span-2 space-y-2">
                                    <input class="kt-input" type="text" name="url" id="url"
                                        value="{{ old('url', $menu?->url) }}" placeholder="/dashboard" />
                                    <p class="text-xs text-muted-foreground">
                                        Ini adalah URL aplikasi
                                    </p>
                                </div>
                            </div>

                            <!-- Permission -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                                <div class="lg:pt-2">
                                    <label class="text-sm font-medium text-foreground" for="show_if_has_permission">
                                        Permission
                                    </label>
                                </div>
                                <div class="lg:col-span-2 space-y-2">
                                    <select id="show_if_has_permission" name="show_if_has_permission" class="kt-select"></select>
                                    <p class="text-xs text-muted-foreground">
                                        Menu akan tampil jika pengguna memiliki izin ini
                                    </p>
                                </div>
                            </div>

                            <!-- Urutan -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                                <div class="lg:pt-2">
                                    <label class="text-sm font-medium text-foreground required" for="order">
                                        Urutan
                                    </label>
                                </div>
                                <div class="lg:col-span-2">
                                    <input class="kt-input" type="number" name="order" id="order"
                                        value="{{ old('order', $menu?->order) }}" required placeholder="40" />
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                                <div class="lg:pt-2">
                                    <label class="text-sm font-medium text-foreground required" for="is_active">
                                        Status
                                    </label>
                                </div>
                                <div class="lg:col-span-2">
                                    <select class="kt-select" name="is_active" id="is_active" required>
                                        <option value="1" {{ old('is_active', $menu?->is_active) ? 'selected' : '' }}>
                                            Aktif</option>
                                        <option value="0" {{ !old('is_active', $menu?->is_active) ? 'selected' : '' }}>
                                            Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        @if ($errors->any())
                            <div class="kt-alert kt-alert-danger mt-5">
                                <div class="kt-alert-content">
                                    <div class="kt-alert-title">Terjadi kesalahan:</div>
                                    <ul class="mt-2 list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="kt-card-footer">
                        <div class="flex justify-end gap-3">
                            <a href="{{ goBack($url) }}" class="kt-btn kt-btn-secondary">
                                Batal
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary" id="btn-save">
                                <i class="ki-filled ki-check"></i>
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var dataSource = new kendo.data.HierarchicalDataSource({
                transport: {
                    read: {
                        url: "{{ route('menus.ajax.tree-menu') }}",
                        type: "GET",
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
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
                                type: "string",
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
            });

            $('#parent_id').kendoDropDownTree({
                placeholder: "Pilih Menu Induk",
                height: "auto",
                dataSource: dataSource,
                dataValueField: "id",
                dataTextField: "text",
                clearButton: true,
                filter: "contains",
                value: "{{ old('parent_id', $menu?->parent_id ? customEncrypt($menu?->parent_id) : '') }}",
                checkboxes: false,
                autoWidth: true,
                loadOnDemand: false
            });


            $("#show_if_has_permission").kendoMultiColumnComboBox({
                dataTextField: "name",
                dataValueField: "name",
                height: 400,
                columns: [{
                        field: "name",
                        title: "Permission",
                        width: 300
                    },
                    {
                        field: "alias",
                        title: "Alias",
                        width: 200
                    }
                ],
                footerTemplate: 'Total #: instance.dataSource.total() # items found',
                filter: "contains",
                value: "{{ old('permission', $menu?->show_if_has_permission) }}",
                filterFields: ["name", "alias"],
                dataSource: {
                    type: "json",
                    transport: {
                        read: `{{ route('menus.ajax.list-permission') }}`
                    }
                }
            }).data("kendoMultiColumnComboBox");
        });
    </script>
@endpush
