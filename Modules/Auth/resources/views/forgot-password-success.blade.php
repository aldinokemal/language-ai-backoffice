@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')

@section('content')
<div class="flex items-center justify-center grow bg-center bg-no-repeat page-bg">
    <div class="kt-card max-w-[440px] w-full">
        <div class="kt-card-content p-10">
            <div class="flex justify-center py-10">
                <img alt="image" class="dark:hidden max-h-[130px]" src="{{ asset('assets/media/illustrations/30.svg') }}"/>
                <img alt="image" class="light:hidden max-h-[130px]" src="{{ asset('assets/media/illustrations/30-dark.svg') }}"/>
            </div>
            <h3 class="text-lg font-medium text-mono text-center mb-3">
                Periksa email Anda
            </h3>
            <div class="text-sm text-center text-secondary-foreground mb-7.5">
                Silakan klik tautan yang dikirim ke email
                <a class="text-sm text-foreground font-medium hover:text-primary" href="#">
                    {{ session('email') }}
                </a>
                <br/>
                untuk reset kata sandi Anda. Terima kasih
                <br/><br/>
                <span class="text-xs text-secondary-foreground">
                    Cek pada inbox / spam
                </span>
            </div>
            
            
            @if (session('message'))
            <div class="kt-alert kt-alert-light kt-alert-success mb-5" id="success_alert">
                <div class="kt-alert-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle" aria-hidden="true">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="9,11 12,14 22,4"></polyline>
                    </svg>
                </div>
                <div class="kt-alert-content">
                    <div class="kt-alert-title">{{ session('message') }}</div>
                </div>
                <button class="kt-alert-close" data-kt-dismiss="#success_alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x" aria-hidden="true">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
            @endif

            @if ($errors->any())
            <div class="kt-alert kt-alert-light kt-alert-destructive mb-5" id="error_alert">
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
            
            <div class="flex justify-center mb-5">
                <a class="kt-btn kt-btn-primary flex justify-center" href="{{ route('login') }}">
                    Lewati untuk sekarang
                </a>
            </div>
            <div class="flex items-center justify-center gap-1">
                <span class="text-xs text-secondary-foreground">
                    Belum menerima email?
                </span>
                <form method="POST" action="{{ route('resend-email') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-medium link hover:underline bg-transparent border-0 p-0">
                        Kirim ulang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection