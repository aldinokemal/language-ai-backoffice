@extends('layouts.app')

@section('title', $title)

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
<a href="{{ route('language-ai.plans.index') }}" class="kt-btn kt-btn-secondary kt-btn-sm">
    <i class="ki-filled ki-arrow-left"></i>
    Back
</a>
@endsection

@section('content')
<div class="kt-container-fixed">
    <form action="{{ route('language-ai.plans.save', $plan?->id) }}" method="POST" id="plan-form">
        @csrf
        
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Plan Details Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">Plan Details</h2>
                        <p class="text-sm text-muted-foreground">Enter the plan information</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-5">
                        <!-- Name -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium required">
                                Plan Name
                            </label>
                            <div class="flex-1 w-full">
                                <input type="text" 
                                       name="plan_name" 
                                       class="kt-input @error('plan_name') is-invalid @enderror" 
                                       value="{{ old('plan_name', $plan?->plan_name) }}" 
                                       required>
                                @error('plan_name') 
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>

                        <!-- Code -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium required">
                                Plan Code
                            </label>
                            <div class="flex-1 w-full">
                                <input type="text" 
                                       name="plan_code" 
                                       class="kt-input @error('plan_code') is-invalid @enderror" 
                                       value="{{ old('plan_code', $plan?->plan_code) }}" 
                                       required>
                                @error('plan_code') 
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium required">
                                Price
                            </label>
                            <div class="flex-1 w-full">
                                <input type="number" 
                                       step="0.01" 
                                       name="price" 
                                       class="kt-input @error('price') is-invalid @enderror" 
                                       value="{{ old('price', $plan?->price ?? 0) }}" 
                                       required>
                                @error('price') 
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>

                        <!-- Currency -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium required">
                                Currency
                            </label>
                            <div class="flex-1 w-full">
                                <input type="text" 
                                       name="currency" 
                                       class="kt-input @error('currency') is-invalid @enderror" 
                                       value="{{ old('currency', $plan?->currency ?? 'USD') }}" 
                                       required 
                                       maxlength="3">
                                @error('currency') 
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>

                        <!-- Interval -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium required">
                                Interval
                            </label>
                            <div class="flex-1 w-full">
                                <select name="interval" class="kt-input @error('interval') is-invalid @enderror" required>
                                    <option value="daily" {{ old('interval', $plan?->interval) == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ old('interval', $plan?->interval) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('interval', $plan?->interval ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ old('interval', $plan?->interval) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                @error('interval') 
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium required">
                                Duration
                            </label>
                            <div class="flex-1 w-full">
                                <input type="number" 
                                       name="duration" 
                                       class="kt-input @error('duration') is-invalid @enderror" 
                                       value="{{ old('duration', $plan?->duration ?? 1) }}" 
                                       required 
                                       min="-1">
                                <p class="text-sm text-muted-foreground mt-2">Duration of the interval (e.g. 1 Month). Set to -1 for unlimited.</p>
                                @error('duration') 
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>

                        <!-- Max Usage -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                Monthly Max Usage
                            </label>
                            <div class="flex-1 w-full">
                                <input type="number" 
                                       name="max_usage[monthly]" 
                                       class="kt-input @error('max_usage.monthly') is-invalid @enderror" 
                                       value="{{ old('max_usage.monthly', $plan?->max_usage['monthly'] ?? '') }}" 
                                       min="-1">
                                <p class="text-sm text-muted-foreground mt-2">Leave empty or set to -1 for unlimited</p>
                                @error('max_usage.monthly') 
                                    <div class="text-danger text-sm mt-1">{{ $message }}</div> 
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">Plan Settings</h2>
                        <p class="text-sm text-muted-foreground">Configure plan status and visibility</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-5">
                        <!-- Active -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                Active Status
                            </label>
                            <div class="flex-1 w-full">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           id="is_active" 
                                           class="form-check-input" 
                                           value="1" 
                                           {{ old('is_active', $plan?->is_active ?? true) ? 'checked' : '' }}>
                                    <label for="is_active" class="form-check-label">Active</label>
                                </div>
                            </div>
                        </div>

                        <!-- Popular -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                Popular Status
                            </label>
                            <div class="flex-1 w-full">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" 
                                           name="is_popular" 
                                           id="is_popular" 
                                           class="form-check-input" 
                                           value="1" 
                                           {{ old('is_popular', $plan?->is_popular ?? false) ? 'checked' : '' }}>
                                    <label for="is_popular" class="form-check-label">Mark as Popular</label>
                                </div>
                            </div>
                        </div>

                        <!-- Displayed -->
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                Visibility
                            </label>
                            <div class="flex-1 w-full">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" 
                                           name="is_displayed" 
                                           id="is_displayed" 
                                           class="form-check-input" 
                                           value="1" 
                                           {{ old('is_displayed', $plan?->is_displayed ?? true) ? 'checked' : '' }}>
                                    <label for="is_displayed" class="form-check-label">Display in pricing page</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">Features</h2>
                        <p class="text-sm text-muted-foreground">Add plan features</p>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-5">
                        <div class="flex flex-col lg:flex-row items-start gap-5">
                            <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                Features List
                            </label>
                            <div class="flex-1 w-full">
                                <div id="features-container">
                                    @php
                                        $features = old('features', $plan->features ?? []);
                                    @endphp
                                    
                                    @if(count($features) > 0)
                                        @foreach($features as $feature)
                                            <div class="flex items-center gap-3 mb-3">
                                                <input type="text" name="features[]" class="kt-input" value="{{ $feature }}" placeholder="Feature description">
                                                <button type="button" class="kt-btn kt-btn-icon kt-btn-sm kt-btn-light-danger remove-feature">
                                                    <i class="ki-filled ki-trash"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="flex items-center gap-3 mb-3">
                                            <input type="text" name="features[]" class="kt-input" placeholder="Feature description">
                                            <button type="button" class="kt-btn kt-btn-icon kt-btn-sm kt-btn-light-danger remove-feature">
                                                <i class="ki-filled ki-trash"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="kt-btn kt-btn-sm kt-btn-light-primary mt-2" id="add-feature">
                                    <i class="ki-filled ki-plus"></i> Add Feature
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('language-ai.plans.index') }}" class="kt-btn kt-btn-secondary">Discard</a>
                        <button type="submit" class="kt-btn kt-btn-primary" id="kt_submit">
                            <i class="ki-filled ki-check"></i> Save Changes
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
        // Feature repeater
        $('#add-feature').click(function() {
            const template = `
                <div class="flex items-center gap-3 mb-3">
                    <input type="text" name="features[]" class="kt-input" placeholder="Feature description">
                    <button type="button" class="kt-btn kt-btn-icon kt-btn-sm kt-btn-light-danger remove-feature">
                        <i class="ki-filled ki-trash"></i>
                    </button>
                </div>
            `;
            $('#features-container').append(template);
        });

        $(document).on('click', '.remove-feature', function() {
            if($('#features-container > div').length > 1) {
                $(this).closest('.flex').remove();
            } else {
                $(this).closest('.flex').find('input').val('');
            }
        });

        // AJAX Form Submit
        $('#plan-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = $('#kt_submit');
            
            btn.prop('disabled', true).html('<i class="ki-filled ki-loading"></i> Saving...');

            const loadingDialog = KendoDialog.loading({
                title: "Processing...",
                content: "Please wait while we save the plan..."
            });

            $.ajax({
                url: form.attr('action'),
                method: form.attr('method'),
                data: form.serialize(),
                success: function(response) {
                    loadingDialog.close();
                    KendoDialog.alert({
                        title: "Success",
                        content: response.message || "Plan successfully saved",
                        type: 'success',
                        onClose: function() {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            }
                        }
                    });
                },
                error: function(xhr) {
                    loadingDialog.close();
                    btn.prop('disabled', false).html('<i class="ki-filled ki-check"></i> Save Changes');
                    
                    let errorMessage = 'Something went wrong';
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join('<br>');
                    } else {
                        errorMessage = xhr.responseJSON?.message || 'Something went wrong';
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

