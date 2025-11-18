@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')

@section('content')
    <div class="flex items-center justify-center grow bg-center bg-no-repeat page-bg">
        <div class="kt-card max-w-[370px] w-full">
            <!-- Back button -->
            <div class="pl-10 pt-5">
                <a href="{{ route('login') }}"
                    class="inline-flex items-center text-sm text-secondary-foreground hover:text-foreground transition-colors">
                    <i class="ki-filled ki-black-left mr-1"></i>
                    Login
                </a>
            </div>

            <form id="forgot-password-form" action="{{ route('forgot-password.post') }}"
                class="kt-card-content flex flex-col gap-5 p-10 pt-5" method="post">
                @csrf
                <div class="text-center">
                    <h3 class="text-lg font-medium text-mono">
                        Email Anda
                    </h3>
                    <span class="text-sm text-secondary-foreground">
                        Masukkan email untuk reset kata sandi
                    </span>
                </div>

                @if ($errors->any())
                    <div class="kt-alert kt-alert-light kt-alert-destructive" id="error_alert">
                        <div class="kt-alert-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-triangle-alert" aria-hidden="true">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                                <path d="M12 9v4"></path>
                                <path d="M12 17h.01"></path>
                            </svg>
                        </div>
                        <div class="kt-alert-content">
                            @foreach ($errors->all() as $error)
                                <div class="kt-alert-title">{{ $error }}</div>
                            @endforeach
                        </div>
                        <button class="kt-alert-close" data-kt-dismiss="#error_alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-x" aria-hidden="true">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono">
                        Email
                    </label>
                    <input class="kt-input" name="email" placeholder="email@email.com" type="email"
                        value="{{ old('email') }}" required />
                </div>

                <button type="submit" class="kt-btn kt-btn-primary flex justify-center grow">
                    Lanjutkan
                    <i class="ki-filled ki-black-right"></i>
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('forgot-password-form');
                if (form) {
                    form.addEventListener('submit', function() {
                        const submitButton = form.querySelector('button[type="submit"]');
                        if (submitButton) {
                            submitButton.disabled = true;
                            submitButton.innerHTML = 'Processing...';
                            submitButton.classList.add('kt-btn-loading');
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
