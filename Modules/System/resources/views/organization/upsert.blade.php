@extends('layouts.app')

@section('title', $data ? 'Edit Organisasi' : 'Tambah Organisasi')

@push('styles')
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
@endpush

@section('toolbar-actions')
    <a href="{{ url($url) }}" class="kt-btn kt-btn-secondary kt-btn-sm">
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
                        <h2 class="kt-card-title">
                            {{ $data ? 'Perbarui informasi organisasi' : 'Tambahkan organisasi baru ke sistem' }}
                        </h2>
                    </div>
                </div>
                <div class="kt-card-content">
                    <form action="{{ $data ? route('organizations.update', customEncrypt($data->id)) : route('organizations.store') }}"
                          method="POST" id="organizationForm" enctype="multipart/form-data">
                        @csrf
                        @method($data ? 'PUT' : 'POST')

                        <div class="grid gap-6">
                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="form-label min-w-0 lg:w-56 text-foreground font-medium">
                                    Logo Organisasi
                                </label>
                                <div class="flex-1">
                                    <div class="avatar-upload-container">
                                        <div class="avatar-preview-container">
                                            <img id="logoPreview" class="avatar-preview" 
                                                 src="{{ $data ? getOrganizationLogo($data) : asset('assets/media/avatars/blank.png') }}" 
                                                 alt="Logo Preview">
                                            <button type="button" id="removeLogo" class="remove-avatar-btn" style="display: none;">
                                                ×
                                            </button>
                                        </div>
                                        <div class="file-input-wrapper">
                                            <input type="file" id="logoInput" name="logo" class="file-input" 
                                                   accept=".png,.jpg,.jpeg">
                                            <input type="hidden" id="logoRemove" name="logo_remove" value="0">
                                            <small class="text-sm text-secondary-foreground">
                                                Gambar akan digunakan sebagai logo organisasi (JPEG, JPG, PNG)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="kt-label min-w-0 lg:w-56">
                                    Kode Organisasi
                                </label>
                                <div class="flex-1">
                                    <input class="kt-input" name="code" id="code" readonly
                                           value="{{ old('code', $data?->code) }}" placeholder="Otomatis dibuat" />
                                    <div class="kt-form-text">Kode akan dibuat secara otomatis</div>
                                </div>
                            </div>

                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="kt-label required min-w-0 lg:w-56">
                                    Nama Organisasi
                                </label>
                                <div class="flex-1">
                                    <input class="kt-input" type="text" name="name" id="name"
                                           value="{{ old('name', $data?->name) }}" required />
                                </div>
                            </div>

                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="kt-label required min-w-0 lg:w-56">
                                    Alamat
                                </label>
                                <div class="flex-1">
                                    <textarea class="kt-textarea" name="address" id="address" rows="3">{{ old('address', $data?->address) }}</textarea>
                                </div>
                            </div>

                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="kt-label required min-w-0 lg:w-56">
                                    Nomor Telepon
                                </label>
                                <div class="flex-1">
                                    <input class="kt-input" type="text" name="phone" id="phone"
                                           value="{{ old('phone', $data?->phone) }}" />
                                </div>
                            </div>

                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="kt-label required min-w-0 lg:w-56">
                                    Alamat Email
                                </label>
                                <div class="flex-1">
                                    <input class="kt-input" type="email" name="email" id="email"
                                           value="{{ old('email', $data?->email) }}" />
                                </div>
                            </div>

                            <div class="flex flex-col lg:flex-row items-start gap-5">
                                <label class="kt-label min-w-0 lg:w-56">
                                    Website
                                </label>
                                <div class="flex-1">
                                    <input class="kt-input" type="url" name="website" id="website"
                                           value="{{ old('website', $data?->website) }}" placeholder="http://www.johnston.org/ex-cupiditate-explicabo-accusamus-quis-rerum-sed" />
                                </div>
                            </div>

                            <div id="message"></div>

                            <div class="flex justify-end gap-3 pt-4">
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
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logo handling
            const logoInput = document.getElementById('logoInput');
            const logoPreview = document.getElementById('logoPreview');
            const removeLogoBtn = document.getElementById('removeLogo');
            const logoRemoveInput = document.getElementById('logoRemove');
            const defaultLogo = '{{ asset('assets/media/avatars/blank.png') }}';

            // Handle file selection
            logoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file type
                    const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg'];
                    if (!allowedTypes.includes(file.type)) {
                        KendoDialog.alert({
                            title: "Error",
                            content: "Format file tidak didukung. Harap gunakan PNG, JPG, atau JPEG.",
                            type: "error"
                        });
                        this.value = '';
                        return;
                    }

                    // Validate file size (max 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        KendoDialog.alert({
                            title: "Error",
                            content: "Ukuran file terlalu besar. Maksimal 2MB.",
                            type: "error"
                        });
                        this.value = '';
                        return;
                    }

                    // Create preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        logoPreview.src = e.target.result;
                        removeLogoBtn.style.display = 'block';
                        logoRemoveInput.value = '0';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Handle remove logo
            removeLogoBtn.addEventListener('click', function() {
                logoPreview.src = defaultLogo;
                logoInput.value = '';
                removeLogoBtn.style.display = 'none';
                logoRemoveInput.value = '1';
            });

            // Show remove button if organization has existing logo
            const currentSrc = logoPreview.src;
            if (currentSrc && !currentSrc.includes('blank.png') && !currentSrc.includes('avatar')) {
                removeLogoBtn.style.display = 'block';
            }

            // Form submission
            const form = document.getElementById('organizationForm');
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                kendo.ui.progress($(document.body), true);
                const saveBtn = document.getElementById('btn-save');
                saveBtn.disabled = true;
                const originalText = saveBtn.innerHTML;
                saveBtn.innerHTML = 'Menyimpan...';

                const formData = new FormData(form);

                try {
                    const response = await axios.post(form.action, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });

                    document.getElementById('message').innerHTML = `
                        <div class="kt-alert kt-alert-success">
                            <div class="kt-alert-text">${response.data.message}</div>
                        </div>
                    `;

                    @if(empty($data))
                        setTimeout(() => {
                            window.location.href = '{{ url($url) }}';
                        }, 1000);
                    @endif
                } catch (error) {
                    document.getElementById('message').innerHTML = `
                        <div class="kt-alert kt-alert-danger">
                            <div class="kt-alert-text">${error.response?.data?.message || 'Terjadi kesalahan saat menyimpan data'}</div>
                        </div>
                    `;
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalText;
                    kendo.ui.progress($(document.body), false);
                }
            });
        });
    </script>
@endpush
