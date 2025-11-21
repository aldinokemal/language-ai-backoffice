@extends('layouts.app')

@section('title', 'Language AI Dashboard')

@section('content')
    <div class="kt-container-fixed">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h5 class="kt-card-title">Total Users</h5>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="text-3xl font-bold" id="total-users">-</div>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h5 class="kt-card-title">Active Subscriptions</h5>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="text-3xl font-bold" id="active-subscriptions">-</div>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h5 class="kt-card-title">Total Chat Usage (Month)</h5>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="text-3xl font-bold" id="total-usage">-</div>
                </div>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header">
                <div class="kt-card-heading">
                    <h5 class="kt-card-title">Daily Chat Usage (Last 30 Days)</h5>
                </div>
            </div>
            <div class="kt-card-content">
                <div id="chart" style="height: 400px;"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Fetch Metrics
            $.post("{{ route('language-ai.dashboard.ajax.metrics') }}", {
                _token: "{{ csrf_token() }}"
            }, function (data) {
                $('#total-users').text(data.total_users);
                $('#active-subscriptions').text(data.active_subscriptions);
                $('#total-usage').text(data.total_usage);
            });

            // Initialize Chart
            $("#chart").kendoChart({
                dataSource: {
                    transport: {
                        read: {
                            url: "{{ route('language-ai.dashboard.ajax.chart-data') }}",
                            dataType: "json",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            }
                        }
                    },
                    sort: {
                        field: "date",
                        dir: "asc"
                    }
                },
                series: [{
                    type: "column",
                    field: "value",
                    categoryField: "date",
                    name: "Chat Usage",
                    color: "#3b82f6"
                }],
                categoryAxis: {
                    labels: {
                        rotation: -45,
                        dateFormats: {
                            days: "dd/MM"
                        }
                    },
                    majorGridLines: {
                        visible: false
                    }
                },
                valueAxis: {
                    labels: {
                        format: "{0}"
                    },
                    line: {
                        visible: false
                    }
                },
                tooltip: {
                    visible: true,
                    format: "{0}",
                    template: "#= category #: #= value #"
                }
            });
        });
    </script>
@endpush