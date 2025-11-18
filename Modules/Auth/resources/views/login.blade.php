@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <form action="{{ url()->current() }}" class="kt-card-content flex flex-col gap-5 p-10" id="sign_in_form" method="post">
		@csrf
        <div class="mb-4 h-16 flex items-center justify-between">
            <div class="w-10"></div>
            <a href="/" class="flex justify-center items-center">
                <img src="{{ asset('assets/media/app/icon.png') }}" alt="Logo" class="h-24" id="logo">
            </a>
            <button type="button" class="kt-btn kt-btn-outline kt-btn-sm flex items-center gap-2" onclick="toggleTheme()">
                <i class="ki-outline ki-night-day text-gray-500 dark:text-gray-400" id="theme-icon"></i>
            </button>
        </div>

        @if ($errors->any())
            <div class="kt-alert kt-alert-light kt-alert-destructive" id="error_alert">
                <div class="kt-alert-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert" aria-hidden="true">
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x" aria-hidden="true">
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
            <input class="kt-input" placeholder="email@email.com" type="email" name="email" value="{{ old('email') }}" />
        </div>
        <div class="flex flex-col gap-1">
            <div class="flex items-center justify-between gap-1">
                <label class="kt-form-label font-normal text-mono">
                    Kata Sandi
                </label>
                <a class="text-sm kt-link shrink-0" href="{{ route('forgot-password') }}" tabindex="-1">
                    Lupa Kata Sandi?
                </a>
            </div>
            <div class="kt-input" data-kt-toggle-password="true">
                <input name="password" placeholder="Masukkan Kata Sandi" type="password" value="" />
                <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5"
                    data-kt-toggle-password-trigger="true" type="button">
                    <span class="kt-toggle-password-active:hidden">
                        <i class="ki-filled ki-eye text-muted-foreground">
                        </i>
                    </span>
                    <span class="hidden kt-toggle-password-active:block">
                        <i class="ki-filled ki-eye-slash text-muted-foreground">
                        </i>
                    </span>
                </button>
            </div>
        </div>
        <label class="kt-label">
            <input class="kt-checkbox kt-checkbox-sm" name="check" type="checkbox" value="1" />
            <span class="kt-checkbox-label">
                Ingat saya
            </span>
        </label>
        <button class="kt-btn kt-btn-primary flex justify-center grow" id="sign_in_button">
            Masuk
        </button>
    </form>


@endsection

@push('scripts')
    <script src="{{ asset('assets/vendors/ktui/ktui.min.js') }}"></script>
    <script>
        function disableSubmitButton() {
            const button = document.getElementById('sign_in_button');
            button.disabled = true;
        }

        // Theme toggle functionality
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.classList.contains('dark') ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            html.classList.remove(currentTheme);
            html.classList.add(newTheme);

            localStorage.setItem('kt-theme', newTheme);
            updateThemeIcon(newTheme);
            updateLogo(newTheme);
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('theme-icon');
            icon.className = theme === 'dark' ?
                'ki-outline ki-night-day text-gray-400' :
                'ki-outline ki-night-day text-gray-500';
        }

        function updateLogo(theme) {
            const logo = document.getElementById('logo');
            logo.src = theme === 'dark' ?
                '{{ asset('assets/media/app/icon.png') }}' :
                '{{ asset('assets/media/app/icon.png') }}';
        }

        // Set initial theme icon and logo on page load
        document.addEventListener('DOMContentLoaded', function() {
            const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            updateThemeIcon(currentTheme);
            updateLogo(currentTheme);

            const form = document.getElementById('sign_in_form');
            const signInButton = document.getElementById('sign_in_button');

            form.addEventListener('submit', function() {
                disableSubmitButton();
            });

            form.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    signInButton.click();
                }
            });
        });
    </script>
@endpush
