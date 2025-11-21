@extends('layouts.app')

@section('title', 'Edit User')

@push('styles')
<style>
    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
    }

    .form-check-label {
        cursor: pointer;
        user-select: none;
    }
</style>
@endpush

@section('toolbar-actions')
<a href="{{ goBack($url) }}" class="kt-btn kt-btn-secondary kt-btn-sm">
    <i class="ki-filled ki-arrow-left"></i>
    Back
</a>
@endsection

@section('content')
<div class="kt-container-fixed">
    <form id="editUserForm" method="POST" action="{{ route('language-ai.users.update', $user->_id) }}">
        @csrf
        @method('PUT')

        <div class="grid gap-5 lg:gap-7.5">
            <!-- Basic Information Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">Basic Information</h2>
                        <p class="text-sm text-muted-foreground">Update user basic details</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-5">
                        <!-- Name -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium required">
                                Name
                            </label>
                            <div class="flex-1 w-full">
                                <input type="text"
                                       name="name"
                                       class="kt-input @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}"
                                       required>
                                @error('name')
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium required">
                                Email
                            </label>
                            <div class="flex-1 w-full">
                                <input type="email"
                                       name="email"
                                       class="kt-input @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}"
                                       required>
                                @error('email')
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Plan -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                Plan
                            </label>
                            <div class="flex-1 w-full">
                                <select name="plan_id"
                                        class="kt-input @error('plan_id') is-invalid @enderror">
                                    <option value="">No Plan</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->_id }}"
                                                {{ old('plan_id', $user->plan_id) == $plan->_id ? 'selected' : '' }}>
                                            {{ $plan->plan_name }} ({{ $plan->currency }} {{ number_format($plan->price, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Activated Status -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                Account Status
                            </label>
                            <div class="flex-1 w-full">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox"
                                           name="activated_at"
                                           id="activated_at"
                                           class="form-check-input"
                                           value="1"
                                           {{ old('activated_at', $user->isActivated()) ? 'checked' : '' }}>
                                    <label for="activated_at" class="form-check-label">
                                        Account is activated
                                    </label>
                                </div>
                                <p class="text-sm text-muted-foreground mt-2">
                                    When checked, the user account is activated and can access the system
                                </p>
                                @error('activated_at')
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Settings Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">Alert Settings</h2>
                        <p class="text-sm text-muted-foreground">Configure user notification preferences</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-5">
                        <!-- Device Login Alert -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                Device Login Alerts
                            </label>
                            <div class="flex-1 w-full">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox"
                                           name="alert_setting[device_login]"
                                           id="alert_device_login"
                                           class="form-check-input"
                                           value="1"
                                           {{ old('alert_setting.device_login', $user->hasDeviceLoginAlerts()) ? 'checked' : '' }}>
                                    <label for="alert_device_login" class="form-check-label">
                                        Send alerts when new device logs in
                                    </label>
                                </div>
                                @error('alert_setting.device_login')
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Importance Update Alert -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                Importance Update Alerts
                            </label>
                            <div class="flex-1 w-full">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox"
                                           name="alert_setting[importance_update]"
                                           id="alert_importance_update"
                                           class="form-check-input"
                                           value="1"
                                           {{ old('alert_setting.importance_update', $user->hasImportanceUpdateAlerts()) ? 'checked' : '' }}>
                                    <label for="alert_importance_update" class="form-check-label">
                                        Send important update notifications
                                    </label>
                                </div>
                                @error('alert_setting.importance_update')
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('language-ai.users.show', $user->_id) }}"
                           class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                        <button type="submit"
                                class="kt-btn kt-btn-primary"
                                id="submitBtn">
                            <i class="ki-filled ki-check"></i>
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#editUserForm').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = $('#submitBtn');
            const formData = new FormData(this);

            // Disable submit button
            submitBtn.prop('disabled', true).html('<i class="ki-filled ki-loading"></i> Saving...');

            // Show loading dialog
            const loadingDialog = KendoDialog.loading({
                title: "Processing...",
                content: "Please wait while we update the user..."
            });

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    loadingDialog.close();

                    KendoDialog.alert({
                        title: "Success",
                        content: response.message || "User successfully updated",
                        type: 'success',
                        onClose: function() {
                            window.location.href = "{{ route('language-ai.users.show', $user->_id) }}";
                        }
                    });
                },
                error: function(xhr) {
                    loadingDialog.close();
                    submitBtn.prop('disabled', false).html('<i class="ki-filled ki-check"></i> Save Changes');

                    let errorMessage = "Failed to update user";

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    KendoDialog.alert({
                        title: "Error",
                        content: errorMessage,
                        type: 'error'
                    });
                }
            });
        });
    });
</script>
@endpush

