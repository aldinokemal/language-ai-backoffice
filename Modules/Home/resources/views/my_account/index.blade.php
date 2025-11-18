@extends('layouts.app')

@section('title', 'My Account')
@section('description', 'Manage your account settings and preferences')

<style>
    .avatar-upload-container {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .avatar-preview-container {
        position: relative;
        display: inline-block;
    }

    .avatar-preview {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e5e7eb;
        transition: border-color 0.3s ease;
    }

    .avatar-preview:hover {
        border-color: #3b82f6;
    }

    .remove-avatar-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 24px;
        height: 24px;
        background-color: #ef4444;
        color: white;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
        transition: background-color 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .remove-avatar-btn:hover {
        background-color: #dc2626;
    }

    .file-input-wrapper {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .file-input {
        display: block;
        width: 100%;
        font-size: 0.875rem;
        color: #6b7280;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        cursor: pointer;
        background-color: #f9fafb;
        transition: border-color 0.3s ease;
    }

    .file-input:hover {
        border-color: #3b82f6;
    }

    .file-input::-webkit-file-upload-button {
        margin-right: 1rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        border: none;
        font-size: 0.875rem;
        font-weight: 500;
        background-color: #3b82f6;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .file-input::-webkit-file-upload-button:hover {
        background-color: #2563eb;
    }
</style>


@section('content')
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="flex grow gap-5 lg:gap-7.5">
            <!-- Sidebar Navigation -->
            <div class="hidden lg:block w-[230px] shrink-0">
                <div class="w-[230px]" data-kt-sticky="true" data-kt-sticky-animation="true"
                    data-kt-sticky-class="fixed z-4 left-auto top-[1.5rem]" data-kt-sticky-name="scrollspy"
                    data-kt-sticky-offset="200" data-kt-sticky-target="#scrollable_content">
                    <div class="flex flex-col grow relative before:absolute before:left-[11px] before:top-0 before:bottom-0 before:border-l before:border-border"
                        data-kt-scrollspy="true" data-kt-scrollspy-offset="110px"
                        data-kt-scrollspy-target="#scrollable_content">

                        <!-- Basic Settings -->
                        <a class="flex items-center rounded-lg pl-2.5 pr-2.5 py-2.5 gap-1.5 active border border-transparent text-sm text-foreground hover:text-primary hover:font-medium kt-scrollspy-active:bg-secondary-active kt-scrollspy-active:text-primary kt-scrollspy-active:font-medium hover:rounded-lg"
                            data-kt-scrollspy-anchor="true" href="#basic_settings">
                            <span
                                class="flex w-1.5 relative before:absolute before:top-0 before:size-1.5 before:rounded-full before:-translate-x-2/4 before:-translate-y-2/4 kt-scrollspy-active:before:bg-primary">
                            </span>
                            Basic Settings
                        </a>

                        <!-- Account ID -->
                        <a class="flex items-center rounded-lg pl-2.5 pr-2.5 py-2.5 gap-1.5 border border-transparent text-sm text-foreground hover:text-primary hover:font-medium kt-scrollspy-active:bg-secondary-active kt-scrollspy-active:text-primary kt-scrollspy-active:font-medium hover:rounded-lg"
                            data-kt-scrollspy-anchor="true" href="#account_id">
                            <span
                                class="flex w-1.5 relative before:absolute before:top-0 before:size-1.5 before:rounded-full before:-translate-x-2/4 before:-translate-y-2/4 kt-scrollspy-active:before:bg-primary">
                            </span>
                            Account ID
                        </a>

                        <!-- Password -->
                        <a class="flex items-center rounded-lg pl-2.5 pr-2.5 py-2.5 gap-1.5 border border-transparent text-sm text-foreground hover:text-primary hover:font-medium kt-scrollspy-active:bg-secondary-active kt-scrollspy-active:text-primary kt-scrollspy-active:font-medium hover:rounded-lg"
                            data-kt-scrollspy-anchor="true" href="#password">
                            <span
                                class="flex w-1.5 relative before:absolute before:top-0 before:size-1.5 before:rounded-full before:-translate-x-2/4 before:-translate-y-2/4 kt-scrollspy-active:before:bg-primary">
                            </span>
                            Password
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex flex-col items-stretch grow gap-5 lg:gap-7.5" id="scrollable_content">

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="alert alert-success" style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger" style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Basic Settings -->
                <div class="kt-card pb-2.5">
                    <div class="kt-card-header" id="basic_settings">
                        <h3 class="kt-card-title">
                            Basic Settings
                        </h3>
                    </div>
                    <div class="kt-card-content">
                        <form id="basicSettingsForm">
                            @csrf
                            <div class="grid gap-5">
                                <!-- Avatar Upload -->
                                <div class="flex items-center flex-wrap gap-2.5">
                                    <label class="kt-form-label max-w-56">
                                        Photo
                                    </label>
                                    <div class="flex items-center justify-between flex-wrap grow gap-2.5">
                                        <span class="text-sm text-secondary-foreground">
                                            150x150px JPEG, PNG Image
                                        </span>
                                        <div class="avatar-upload-container">
                                            <!-- Image Preview -->
                                            <div class="avatar-preview-container">
                                                <img id="avatarPreview"
                                                     src="{{ getUserImage($user) }}"
                                                     alt="Avatar Preview"
                                                     class="avatar-preview">
                                                <button type="button" id="removeAvatar"
                                                        class="remove-avatar-btn"
                                                        style="display: none;"
                                                        title="Remove avatar">
                                                    ×
                                                </button>
                                            </div>

                                            <!-- File Input -->
                                            <div class="file-input-wrapper">
                                                <input type="file"
                                                       id="avatarInput"
                                                       name="avatar"
                                                       accept="image/png,image/jpg,image/jpeg"
                                                       class="file-input">
                                                <input type="hidden" name="avatar_remove" id="avatarRemove" value="0">
                                                <small class="text-xs text-gray-500">Max size: 5MB</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Name -->
                                <div class="w-full">
                                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                        <label class="kt-form-label flex items-center gap-1 max-w-56">
                                            Name
                                        </label>
                                        <input class="kt-input" type="text" name="name" value="{{ $user->name }}"
                                            required />
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div class="w-full">
                                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                        <label class="kt-form-label flex items-center gap-1 max-w-56">
                                            Phone number
                                        </label>
                                        <input class="kt-input" type="text" name="phone" value="{{ $user->phone }}"
                                            placeholder="Enter phone" required />
                                    </div>
                                </div>

                                <div class="flex justify-end pt-2.5">
                                    <button type="submit" class="kt-btn kt-btn-primary">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Account ID -->
                <div class="kt-card pb-2.5">
                    <div class="kt-card-header" id="account_id">
                        <h3 class="kt-card-title">
                            Account ID
                        </h3>
                    </div>
                    <div class="kt-card-content">
                        <form id="accountIdForm">
                            @csrf
                            <div class="grid gap-5">
                                <!-- Email -->
                                <div class="w-full">
                                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                        <label class="kt-form-label max-w-56">
                                            Email
                                        </label>
                                        <div class="grow">
                                            <div class="flex gap-2">
                                                <input class="kt-input" type="email" name="email"
                                                    value="{{ $user->email }}" required id="emailInput" />
                                                <button type="button" class="kt-btn kt-btn-outline" id="sendEmailOtp"
                                                    style="display: none;">
                                                    Send OTP
                                                </button>
                                            </div>
                                            <input class="kt-input mt-2" type="text" name="otp"
                                                placeholder="Enter OTP" style="display: none;" id="emailOtpInput"
                                                maxlength="6" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Username -->
                                <div class="w-full">
                                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                        <label class="kt-form-label max-w-56">
                                            Username
                                        </label>
                                        <input class="kt-input" type="text" name="username"
                                            value="{{ $user->username }}" required pattern="[a-zA-Z0-9]+" />
                                    </div>
                                </div>

                                <div class="flex justify-end pt-2.5">
                                    <button type="submit" class="kt-btn kt-btn-primary">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Password -->
                <div class="kt-card pb-2.5">
                    <div class="kt-card-header" id="password">
                        <h3 class="kt-card-title">
                            Password
                        </h3>
                    </div>
                    <div class="kt-card-content">
                        <form id="passwordForm">
                            @csrf
                            <!-- Hidden username field for accessibility and password managers -->
                            <div style="position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden;">
                                <input type="text" name="username" value="{{ $user->email }}"
                                    autocomplete="username" tabindex="-1" readonly />
                            </div>
                            <div class="grid gap-5">
                                <!-- Current Password -->
                                <div class="w-full">
                                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                        <label class="kt-form-label max-w-56">
                                            Current Password
                                        </label>
                                        <input class="kt-input" placeholder="Your current password" type="password"
                                            name="current_password" required minlength="8"
                                            autocomplete="current-password" />
                                    </div>
                                </div>

                                <!-- New Password -->
                                <div class="w-full">
                                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                        <label class="kt-form-label max-w-56">
                                            New Password
                                        </label>
                                        <input class="kt-input" placeholder="New password" type="password"
                                            name="new_password" required minlength="8" id="newPassword"
                                            autocomplete="new-password" />
                                    </div>
                                </div>

                                <!-- Confirm New Password -->
                                <div class="w-full">
                                    <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                        <label class="kt-form-label max-w-56">
                                            Confirm New Password
                                        </label>
                                        <input class="kt-input" placeholder="Confirm new password" type="password"
                                            name="new_password_confirmation" required minlength="8" id="confirmPassword"
                                            autocomplete="new-password" />
                                    </div>
                                </div>

                                <div class="flex justify-end pt-2.5">
                                    <button type="submit" class="kt-btn kt-btn-primary">
                                        Update Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Form Handling -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const originalEmail = '{{ $user->email }}';

            // Avatar handling
            const avatarInput = document.getElementById('avatarInput');
            const avatarPreview = document.getElementById('avatarPreview');
            const removeAvatarBtn = document.getElementById('removeAvatar');
            const avatarRemoveInput = document.getElementById('avatarRemove');
            const defaultAvatar = '/assets/media/avatars/blank.png';

            // Handle file selection
            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file type
                    const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg'];
                    if (!allowedTypes.includes(file.type)) {
                        showMessage('Please select a PNG, JPG or JPEG image', 'error');
                        avatarInput.value = '';
                        return;
                    }

                    // Validate file size (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        showMessage('Image size must be less than 5MB', 'error');
                        avatarInput.value = '';
                        return;
                    }

                    // Create preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                        removeAvatarBtn.style.display = 'block';
                        avatarRemoveInput.value = '0';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Handle remove avatar
            removeAvatarBtn.addEventListener('click', function() {
                avatarPreview.src = defaultAvatar;
                avatarInput.value = '';
                removeAvatarBtn.style.display = 'none';
                avatarRemoveInput.value = '1';
            });

            // Show remove button if user has existing avatar
            if (avatarPreview.src !== defaultAvatar && !avatarPreview.src.includes('blank.png')) {
                removeAvatarBtn.style.display = 'block';
            }

            // Email OTP handling
            const emailInput = document.getElementById('emailInput');
            const sendEmailOtp = document.getElementById('sendEmailOtp');
            const emailOtpInput = document.getElementById('emailOtpInput');

            emailInput.addEventListener('input', function() {
                if (this.value !== originalEmail && this.value.length > 0) {
                    sendEmailOtp.style.display = 'block';
                    emailOtpInput.style.display = 'block';
                } else {
                    sendEmailOtp.style.display = 'none';
                    emailOtpInput.style.display = 'none';
                }
            });

            sendEmailOtp.addEventListener('click', function() {
                axios.post('{{ route('my-account.sendEmailOTP') }}', {
                        email: emailInput.value
                    })
                    .then(response => {
                        showMessage(response.data.message, 'success');
                    })
                    .catch(error => {
                        const message = error.response?.data?.message || 'Failed to send OTP';
                        showMessage(message, 'error');
                    });
            });

            // Form submissions
            document.getElementById('basicSettingsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                proceedWithSubmit(this, '{{ route('my-account.updateBasicSettings') }}');
            });

            document.getElementById('accountIdForm').addEventListener('submit', function(e) {
                e.preventDefault();
                proceedWithSubmit(this, '{{ route('my-account.updateAccountID') }}');
            });

            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;

                if (newPassword !== confirmPassword) {
                    showMessage('Passwords do not match', 'error');
                    return;
                }

                proceedWithSubmit(this, '{{ route('my-account.updatePassword') }}');
            });

            function proceedWithSubmit(form, url) {
                // Add validation for basic settings form
                if (form.id === 'basicSettingsForm') {
                    const phoneInput = form.querySelector('input[name="phone"]');
                    if (phoneInput) {
                        const phonePattern = /^\+?[1-9]\d{1,14}$/;
                        if (!phonePattern.test(phoneInput.value)) {
                            showMessage('Please enter a valid international phone number (e.g., +1234567890)', 'error');
                            return;
                        }
                    }
                }

                const formData = new FormData(form);

                axios.post(url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                    .then(response => {
                        const data = response.data;
                        if (data.success || data.message) {
                            showMessage(data.message || 'Updated successfully', 'success');
                            // Reset OTP fields if successful
                            if (form.id === 'accountIdForm') {
                                emailOtpInput.style.display = 'none';
                                sendEmailOtp.style.display = 'none';
                                emailOtpInput.value = '';
                            }
                            if (form.id === 'passwordForm') {
                                form.reset();
                            }
                            if (form.id === 'basicSettingsForm') {
                                // Reset avatar-related states
                                avatarRemoveInput.value = '0';
                            }
                        } else {
                            showMessage(data.message || 'Update failed', 'error');
                        }
                    })
                    .catch(error => {
                        const message = error.response?.data?.message || 'An error occurred';
                        showMessage(message, 'error');
                    });
            }

            function showMessage(message, type) {
                if (type === 'success') {
                    // Success toast
                    KTToast.show({
                        message: message,
                        variant: 'success'
                    });
                } else {
                    // Destructive toast for errors
                    KTToast.show({
                        message: message,
                        variant: 'destructive'
                    });
                }
            }
        });
    </script>
@endsection
