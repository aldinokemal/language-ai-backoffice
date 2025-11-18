@extends('layouts.auth')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
<div class="flex items-center justify-center grow bg-center bg-no-repeat page-bg">
    <div class="kt-card max-w-[370px] w-full">
        <form action="{{ route('password.update') }}" class="kt-card-content flex flex-col gap-5 p-10" method="post">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            
            <div class="text-center">
                <h3 class="text-lg font-medium text-mono">
                    Atur Ulang Kata Sandi
                </h3>
                <span class="text-sm text-secondary-foreground">
                    Masukkan kata sandi baru Anda
                </span>
            </div>
            
            @if($errors->has('email'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
                    {{ $errors->first('email') }}
                </div>
            @endif

            @if($errors->has('message'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
                    {{ $errors->first('message') }}
                </div>
            @endif

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono">
                    Email
                </label>
                <input class="kt-input" name="email" type="email" value="{{ $email }}" readonly/>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono">
                    Kata Sandi Baru
                </label>
                <input class="kt-input" name="password" placeholder="Masukkan kata sandi baru" type="password" required/>
                @error('password')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono">
                    Konfirmasi Kata Sandi
                </label>
                <input class="kt-input" name="password_confirmation" placeholder="Konfirmasi kata sandi baru" type="password" required/>
            </div>
            
            <button type="submit" class="kt-btn kt-btn-primary flex justify-center grow">
                Atur Ulang Kata Sandi
                <i class="ki-filled ki-black-right"></i>
            </button>

            <div class="text-center">
                <a class="text-xs text-secondary-foreground hover:text-primary" href="{{ route('login') }}">
                    Kembali ke Login
                </a>
            </div>
        </form>
    </div>
</div>
@endsection 