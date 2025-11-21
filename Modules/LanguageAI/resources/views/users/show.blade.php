@extends('layouts.app')

@section('title', 'User Detail')

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
    Back
</a>
@can('language.ai.users.update')
    <a href="{{ route('language-ai.users.edit', $data->_id) }}" class="kt-btn kt-btn-primary kt-btn-sm ml-2">
        <i class="ki-filled ki-pencil"></i>
        Edit
    </a>
@endcan
@endsection

@section('content')
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- User Profile Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">{{ $data->name }}</h2>
                        <p class="text-sm text-muted-foreground">User profile information</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-6">
                        <!-- Avatar Section -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                Profile Picture
                            </label>
                            <div class="flex-1">
                                <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-gray-200">
                                    <img src="{{ $data->picture ?? '/assets/media/avatars/blank.png' }}"
                                         alt="{{ $data->name }}"
                                         class="w-full h-full object-cover">
                                </div>
                            </div>
                        </div>

                        <!-- User Information Grid -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Name</label>
                                <div class="kt-input-readonly">{{ $data->name }}</div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Email</label>
                                <div class="kt-input-readonly">{{ $data->email }}</div>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            @if($data->google_id)
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Google ID</label>
                                <div class="kt-input-readonly">{{ $data->google_id }}</div>
                            </div>
                            @endif

                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Status</label>
                                <div class="kt-input-readonly">
                                    @if($data->isActivated())
                                        <span class="kt-badge kt-badge-success kt-badge-sm">Activated</span>
                                    @else
                                        <span class="kt-badge kt-badge-secondary kt-badge-sm">Not Activated</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($data->activated_at)
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">Activated At</label>
                            <div class="kt-input-readonly">{{ $data->activated_at->format('d/m/Y H:i:s') }}</div>
                        </div>
                        @endif

                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Created At</label>
                                <div class="kt-input-readonly">{{ $data->created_at->format('d/m/Y H:i:s') }}</div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Last Updated</label>
                                <div class="kt-input-readonly">{{ $data->updated_at->format('d/m/Y H:i:s') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Plan Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">Current Plan</h2>
                        <p class="text-sm text-muted-foreground">User subscription plan details</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    @if($data->plan)
                    <div class="grid gap-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Plan Name</label>
                                <div class="kt-input-readonly">{{ $data->plan->plan_name }}</div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Plan Code</label>
                                <div class="kt-input-readonly">{{ $data->plan->plan_code }}</div>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Price</label>
                                <div class="kt-input-readonly">{{ $data->plan->currency }} {{ number_format($data->plan->price, 2) }}</div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Interval</label>
                                <div class="kt-input-readonly">{{ ucfirst($data->plan->interval) }}</div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Duration</label>
                                <div class="kt-input-readonly">{{ $data->plan->duration }} {{ $data->plan->interval }}</div>
                            </div>
                        </div>

                        @if($data->plan->features && count($data->plan->features) > 0)
                        <div class="flex flex-col gap-2">
                            <label class="kt-label">Features</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($data->plan->features as $feature)
                                    <span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm">{{ $feature }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @else
                    <p class="text-muted-foreground">No plan assigned</p>
                    @endif
                </div>
            </div>

            <!-- Alert Settings Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">Alert Settings</h2>
                        <p class="text-sm text-muted-foreground">User notification preferences</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <label class="kt-label">Device Login Alerts</label>
                                <p class="text-sm text-muted-foreground">Receive alerts when new device logs in</p>
                            </div>
                            <div>
                                @if($data->hasDeviceLoginAlerts())
                                    <span class="kt-badge kt-badge-success kt-badge-sm">Enabled</span>
                                @else
                                    <span class="kt-badge kt-badge-secondary kt-badge-sm">Disabled</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <label class="kt-label">Importance Update Alerts</label>
                                <p class="text-sm text-muted-foreground">Receive important update notifications</p>
                            </div>
                            <div>
                                @if($data->hasImportanceUpdateAlerts())
                                    <span class="kt-badge kt-badge-success kt-badge-sm">Enabled</span>
                                @else
                                    <span class="kt-badge kt-badge-secondary kt-badge-sm">Disabled</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Usage History Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">Chat Usage History</h2>
                        <p class="text-sm text-muted-foreground">Daily chat usage over time</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-foreground">Date Range:</label>
                        <div id="chatUsageDateRange"></div>
                        <button id="refreshChatUsage" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-arrows-circle"></i>
                            Refresh
                        </button>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div id="chatUsageChart" style="min-height: 400px;"></div>
                    <div id="chatUsageStats" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-2 p-4 bg-gray-50 rounded-lg">
                            <span class="text-sm text-muted-foreground">Total Usage</span>
                            <span id="totalUsage" class="text-2xl font-bold text-foreground">-</span>
                        </div>
                        <div class="flex flex-col gap-2 p-4 bg-gray-50 rounded-lg">
                            <span class="text-sm text-muted-foreground">Average Daily</span>
                            <span id="avgUsage" class="text-2xl font-bold text-foreground">-</span>
                        </div>
                        <div class="flex flex-col gap-2 p-4 bg-gray-50 rounded-lg">
                            <span class="text-sm text-muted-foreground">Peak Usage</span>
                            <span id="peakUsage" class="text-2xl font-bold text-foreground">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription History Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">Subscription History</h2>
                        <p class="text-sm text-muted-foreground">User subscription records</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div id="subscriptionsGrid"></div>
                </div>
            </div>

            <!-- User Devices Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">User Devices</h2>
                        <p class="text-sm text-muted-foreground">Devices associated with this user</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div id="devicesGrid"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const isMobile = window.matchMedia('(max-width: 1024px)').matches;
        const computedGridHeight = isMobile ? 'auto' : Math.max(400, document.documentElement.clientHeight - 350);

        // Chat Usage Chart Initialization
        let chatUsageChart = null;
        const userId = "{{ $data->_id }}";

        // Initialize date range picker with default last 30 days
        const defaultEndDate = new Date();
        const defaultStartDate = new Date();
        defaultStartDate.setDate(defaultStartDate.getDate() - 30);

        $("#chatUsageDateRange").kendoDateRangePicker({
            labels: false,
            range: {
                start: defaultStartDate,
                end: defaultEndDate
            },
            change: function(e) {
                loadChatUsageData();
            }
        });

        // Load chat usage data
        function loadChatUsageData() {
            const dateRangePicker = $("#chatUsageDateRange").data("kendoDateRangePicker");
            const range = dateRangePicker.range();

            if (!range.start || !range.end) {
                return;
            }

            const startDate = kendo.toString(range.start, "yyyy-MM-dd");
            const endDate = kendo.toString(range.end, "yyyy-MM-dd");

            $.ajax({
                url: "{{ route('language-ai.users.ajax.chat-usage', $data->_id) }}",
                method: 'POST',
                data: {
                    start_date: startDate,
                    end_date: endDate
                },
                beforeSend: function() {
                    $("#chatUsageChart").html('<div class="text-center py-8"><i class="ki-filled ki-loading text-4xl animate-spin"></i></div>');
                },
                success: function(response) {
                    renderChatUsageChart(response.data);
                    updateChatUsageStats(response.data, response.total);
                },
                error: function(xhr) {
                    $("#chatUsageChart").html('<div class="text-center py-8 text-danger"><i class="ki-filled ki-information-2"></i> Failed to load chat usage data</div>');
                    console.error('Error loading chat usage data:', xhr);
                }
            });
        }

        // Render Kendo UI Line Chart
        function renderChatUsageChart(data) {
            if (chatUsageChart) {
                chatUsageChart.destroy();
            }

            $("#chatUsageChart").kendoChart({
                dataSource: {
                    data: data
                },
                legend: {
                    visible: false
                },
                seriesDefaults: {
                    type: "line",
                    style: "smooth"
                },
                series: [{
                    field: "usage",
                    name: "Chat Usage",
                    color: "#3b82f6",
                    tooltip: {
                        visible: true,
                        template: "<strong>#= kendo.toString(new Date(dataItem.date), 'dd/MM/yyyy') #</strong><br/>Usage: #= dataItem.usage #"
                    },
                    markers: {
                        visible: true,
                        size: 4
                    }
                }],
                categoryAxis: {
                    field: "date",
                    labels: {
                        rotation: -45,
                        template: "#= kendo.toString(new Date(value), 'dd/MM') #"
                    },
                    majorGridLines: {
                        visible: false
                    }
                },
                valueAxis: {
                    title: {
                        text: "Usage Count"
                    },
                    min: 0,
                    majorGridLines: {
                        visible: true
                    }
                },
                tooltip: {
                    visible: true,
                    shared: true
                }
            });

            chatUsageChart = $("#chatUsageChart").data("kendoChart");
        }

        // Update statistics
        function updateChatUsageStats(data, total) {
            const usageValues = data.map(item => item.usage);
            const average = data.length > 0 ? (total / data.length).toFixed(2) : 0;
            const peak = Math.max(...usageValues, 0);

            $("#totalUsage").text(total.toLocaleString());
            $("#avgUsage").text(average);
            $("#peakUsage").text(peak.toLocaleString());
        }

        // Refresh button click handler
        $("#refreshChatUsage").on("click", function() {
            loadChatUsageData();
        });

        // Initial load
        loadChatUsageData();

        // Subscriptions Grid
        $("#subscriptionsGrid").kendoGrid({
            dataSource: {
                data: @json($data->subscription()->get()),
                schema: {
                    model: {
                        fields: {
                            start_date: { type: "date" },
                            end_date: { type: "date" },
                            price: { type: "number" },
                            duration: { type: "number" }
                        }
                    }
                },
                pageSize: 10
            },
            height: computedGridHeight,
            pageable: {
                pageSizes: [5, 10, 25],
                buttonCount: 3
            },
            sortable: true,
            resizable: true,
            columns: [
                {
                    field: "plan_id",
                    title: "Plan",
                    template: function(dataItem) {
                        return dataItem.plan ? dataItem.plan.plan_name : 'N/A';
                    }
                },
                {
                    field: "price",
                    title: "Price",
                    width: "120px",
                    template: function(dataItem) {
                        return dataItem.currency + ' ' + Number(dataItem.price).toFixed(2);
                    }
                },
                {
                    field: "interval",
                    title: "Interval",
                    width: "100px",
                    template: function(dataItem) {
                        return dataItem.interval ? dataItem.interval.charAt(0).toUpperCase() + dataItem.interval.slice(1) : 'N/A';
                    }
                },
                {
                    field: "duration",
                    title: "Duration",
                    width: "100px"
                },
                {
                    field: "start_date",
                    title: "Start Date",
                    format: "{0:dd/MM/yyyy}",
                    width: "140px"
                },
                {
                    field: "end_date",
                    title: "End Date",
                    format: "{0:dd/MM/yyyy}",
                    width: "140px",
                    template: function(dataItem) {
                        return dataItem.end_date ? kendo.toString(new Date(dataItem.end_date), "dd/MM/yyyy") : 'Lifetime';
                    }
                },
                {
                    title: "Status",
                    width: "120px",
                    template: function(dataItem) {
                        const now = new Date();
                        const startDate = new Date(dataItem.start_date);
                        const endDate = dataItem.end_date ? new Date(dataItem.end_date) : null;

                        if (startDate <= now && (!endDate || endDate >= now)) {
                            return '<span class="kt-badge kt-badge-success kt-badge-sm">Active</span>';
                        }
                        return '<span class="kt-badge kt-badge-secondary kt-badge-sm">Inactive</span>';
                    }
                }
            ]
        });

        // Devices Grid
        $("#devicesGrid").kendoGrid({
            dataSource: {
                data: @json($data->devices()->get()),
                schema: {
                    model: {
                        fields: {
                            created_at: { type: "date" },
                            revoked_at: { type: "date" }
                        }
                    }
                },
                pageSize: 10
            },
            height: computedGridHeight,
            pageable: {
                pageSizes: [5, 10, 25],
                buttonCount: 3
            },
            sortable: true,
            resizable: true,
            columns: [
                {
                    field: "device_name",
                    title: "Device Name",
                    width: "200px"
                },
                {
                    field: "device_type",
                    title: "Type",
                    width: "120px",
                    template: function(dataItem) {
                        return dataItem.device_type ? dataItem.device_type.toUpperCase() : 'N/A';
                    }
                },
                {
                    field: "app_version",
                    title: "App Version",
                    width: "120px"
                },
                {
                    field: "created_at",
                    title: "Added At",
                    format: "{0:dd/MM/yyyy HH:mm}",
                    width: "180px"
                },
                {
                    field: "revoked_at",
                    title: "Status",
                    width: "120px",
                    template: function(dataItem) {
                        if (!dataItem.revoked_at) {
                            return '<span class="kt-badge kt-badge-success kt-badge-sm">Active</span>';
                        }
                        return '<span class="kt-badge kt-badge-secondary kt-badge-sm">Revoked</span>';
                    }
                }
            ]
        });
    });
</script>
@endpush

