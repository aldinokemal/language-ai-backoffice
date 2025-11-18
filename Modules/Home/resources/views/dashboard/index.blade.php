@extends('layouts.app')

@section('title', 'Dasbor')
@section('description', 'Ringkasan dasbor komprehensif Anda')


@section('content')
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- begin: grid -->
            <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5 items-stretch">
                <div class="lg:col-span-2">
                    <div class="kt-card h-full">
                        <div class="kt-card-content flex flex-col place-content-center gap-5">
                            <div class="flex justify-center">
                                <img alt="welcome" class="dark:hidden max-h-[180px]"
                                    src="{{ asset('assets/media/illustrations/32.svg') }}" />
                                <img alt="welcome" class="light:hidden max-h-[180px]"
                                    src="{{ asset('assets/media/illustrations/32-dark.svg') }}" />
                            </div>
                            <div class="flex flex-col gap-4">
                                <div class="flex flex-col gap-3 text-center">
                                    <h2 class="text-xl font-semibold text-mono">
                                        Selamat datang kembali, {{ auth()->user()->name ?? 'Pengguna' }}!
                                    </h2>
                                    <p class="text-sm font-medium text-secondary-foreground">
                                        Tingkatkan alur kerja Anda dengan dasbor intuitif kami. Lacak kemajuan,
                                        <br />
                                        kelola tugas, dan tetap terorganisir - semua dalam satu tempat.
                                    </p>
                                </div>
                                <div class="flex justify-center">
                                    <a class="kt-btn kt-btn-primary" href="#">
                                        Buat Proyek
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-1">
                    <div class="kt-card h-full">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Statistik Cepat</h3>
                            <div class="kt-menu" data-kt-menu="true">
                                <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px"
                                    data-kt-menu-item-placement="bottom-start" data-kt-menu-item-toggle="dropdown"
                                    data-kt-menu-item-trigger="click">
                                    <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                        <i class="ki-filled ki-dots-vertical text-lg"></i>
                                    </button>
                                    <div class="kt-menu-dropdown kt-menu-default w-full max-w-[200px]"
                                        data-kt-menu-dismiss="true">
                                        <div class="kt-menu-item">
                                            <a class="kt-menu-link" href="#">
                                                <span class="kt-menu-icon">
                                                    <i class="ki-filled ki-chart-simple"></i>
                                                </span>
                                                <span class="kt-menu-title">Lihat Analitik</span>
                                            </a>
                                        </div>
                                        <div class="kt-menu-item">
                                            <a class="kt-menu-link" href="#">
                                                <span class="kt-menu-icon">
                                                    <i class="ki-filled ki-exit-down"></i>
                                                </span>
                                                <span class="kt-menu-title">Ekspor Data</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5 lg:pt-4">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-sm font-normal text-secondary-foreground">Total Pengguna</span>
                                <div class="flex items-center gap-2.5">
                                    <span class="text-3xl font-semibold text-mono">
                                        {{ $stats['total_users'] ?? '0' }}
                                    </span>
                                    <span class="kt-badge kt-badge-outline kt-badge-success kt-badge-sm">
                                        +{{ $stats['new_users_this_month'] ?? '0' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 mb-1.5">
                                <div class="bg-green-500 h-2 w-full max-w-[60%] rounded-xs"></div>
                                <div class="bg-destructive h-2 w-full max-w-[25%] rounded-xs"></div>
                                <div class="bg-violet-500 h-2 w-full max-w-[15%] rounded-xs"></div>
                            </div>
                            <div class="flex items-center flex-wrap gap-4 mb-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="rounded-full size-2 kt-badge-success"></span>
                                    <span class="text-sm font-normal text-foreground">Aktif</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="rounded-full size-2 kt-badge-destructive"></span>
                                    <span class="text-sm font-normal text-foreground">Tidak Aktif</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="rounded-full size-2 kt-badge-info"></span>
                                    <span class="text-sm font-normal text-foreground">Menunggu</span>
                                </div>
                            </div>
                            <div class="border-b border-input"></div>
                            <div class="grid gap-3">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-1.5">
                                        <i class="ki-filled ki-users text-base text-muted-foreground"></i>
                                        <span class="text-sm font-normal text-mono">Pengguna Aktif</span>
                                    </div>
                                    <div class="flex items-center text-sm font-medium text-foreground gap-6">
                                        <span class="lg:text-right">{{ $stats['active_users'] ?? '0' }}</span>
                                        <span class="lg:text-right">
                                            <i class="ki-filled ki-arrow-up text-green-500"></i>
                                            {{ $stats['active_users_growth'] ?? '0' }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-1.5">
                                        <i class="ki-filled ki-chart-line text-base text-muted-foreground"></i>
                                        <span class="text-sm font-normal text-mono">Proyek</span>
                                    </div>
                                    <div class="flex items-center text-sm font-medium text-foreground gap-6">
                                        <span class="lg:text-right">{{ $stats['total_projects'] ?? '0' }}</span>
                                        <span class="lg:text-right">
                                            <i class="ki-filled ki-arrow-up text-green-500"></i>
                                            {{ $stats['projects_growth'] ?? '0' }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-1.5">
                                        <i class="ki-filled ki-notification-status text-base text-muted-foreground"></i>
                                        <span class="text-sm font-normal text-mono">Tugas</span>
                                    </div>
                                    <div class="flex items-center text-sm font-medium text-foreground gap-6">
                                        <span class="lg:text-right">{{ $stats['completed_tasks'] ?? '0' }}</span>
                                        <span class="lg:text-right">
                                            <i class="ki-filled ki-arrow-up text-green-500"></i>
                                            {{ $stats['tasks_completion_rate'] ?? '0' }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end: grid -->

            @auth
                <!-- Recent Activity -->
                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5 items-stretch">
                    <div class="lg:col-span-2">
                        <div class="kt-card h-full">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Aktivitas Terbaru</h3>
                                <a href="#" class="kt-btn kt-btn-outline kt-btn-sm">
                                    Lihat Semua
                                </a>
                            </div>
                            <div class="kt-card-content">
                                @if (isset($recentActivity) && $recentActivity->count() > 0)
                                    <div class="flex flex-col gap-4">
                                        @foreach ($recentActivity as $activity)
                                            <div class="flex items-center gap-3 p-3 rounded-lg bg-accent/10">
                                                <div class="kt-avatar size-8">
                                                    <div class="kt-avatar-image">
                                                        <img alt="{{ $activity->user->name ?? 'User' }}"
                                                            src="{{ $activity->user->avatar_url ?? asset('assets/media/avatars/300-1.png') }}">
                                                    </div>
                                                </div>
                                                <div class="flex flex-col flex-1">
                                                    <span class="text-sm font-medium text-foreground">
                                                        {{ $activity->description }}
                                                    </span>
                                                    <span class="text-xs text-muted-foreground">
                                                        {{ $activity->created_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex flex-col text-center py-12 gap-5">
                                        <div class="flex justify-center">
                                            <img alt="no activity" class="dark:hidden max-h-[100px]"
                                                src="{{ asset('assets/media/illustrations/33.svg') }}" />
                                            <img alt="no activity" class="light:hidden max-h-[100px]"
                                                src="{{ asset('assets/media/illustrations/33-dark.svg') }}" />
                                        </div>
                                        <div class="flex flex-col gap-1.5">
                                            <h3 class="text-base font-semibold text-mono text-center">
                                                Tidak ada aktivitas terbaru
                                            </h3>
                                            <span class="text-sm font-medium text-center text-secondary-foreground">
                                                Aktivitas terbaru Anda akan muncul di sini
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="lg:col-span-1">
                        <div class="kt-card h-full">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Aksi Cepat</h3>
                            </div>
                            <div class="kt-card-content flex flex-col gap-5">
                                <div class="text-sm text-foreground">
                                    Aksi yang sering digunakan untuk membantu Anda menyelesaikan pekerjaan lebih cepat
                                </div>
                                <div class="flex flex-col gap-3">
                                    <a href="#" class="kt-btn kt-btn-outline justify-start">
                                        <i class="ki-filled ki-user-plus"></i>
                                        Tambah Pengguna Baru
                                    </a>
                                    <a href="#" class="kt-btn kt-btn-outline justify-start">
                                        <i class="ki-filled ki-plus"></i>
                                        Buat Proyek
                                    </a>
                                    <a href="#" class="kt-btn kt-btn-outline justify-start">
                                        <i class="ki-filled ki-chart-line"></i>
                                        Lihat Laporan
                                    </a>
                                    <a href="#" class="kt-btn kt-btn-outline justify-start">
                                        <i class="ki-filled ki-setting-2"></i>
                                        Pengaturan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endauth
        </div>
    </div>
    <!-- End of Container -->
@endsection
