@extends('layouts.app')

@section('content')
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-heading">
                        <h2 class="kt-card-title">Daftar Notifikasi</h2>
                    </div>
                    <div class="kt-card-toolbar">
                        @if (auth()->user()->unreadNotifications->count() > 0)
                            <a href="{{ route('notification.markAllAsRead') }}" class="kt-btn kt-btn-sm kt-btn-outline">
                                <i class="ki-filled ki-check"></i>
                                Tandai Semua Dibaca
                            </a>
                        @endif
                    </div>
                </div>

                <div class="kt-card-content">
                    @if (session('success'))
                        <div class="kt-alert kt-alert-success mb-5">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="kt-alert kt-alert-danger mb-5">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($notifications->count() > 0)
                        <div class="space-y-1">
                            @foreach ($notifications as $notification)
                                <a href="{{ route('notification.open', ['id' => encrypt($notification->id)]) }}" 
                                   class="block group">
                                    <div class="flex items-start gap-4 p-4 rounded-lg border border-transparent transition-all duration-200 hover:bg-muted/50 hover:border-border {{ $notification->unread() ? 'bg-primary/5 border-primary/20' : '' }}">
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 {{ $notification->unread() ? 'bg-primary' : 'bg-muted' }} rounded-full flex items-center justify-center transition-colors duration-200">
                                                <i class="ki-filled {{ $notification->unread() ? 'ki-notification-on text-primary-foreground' : 'ki-notification text-muted-foreground' }} text-xl"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-sm font-semibold text-foreground mb-1 group-hover:text-primary transition-colors duration-200 {{ $notification->unread() ? 'text-foreground' : 'text-muted-foreground' }}">
                                                        {{ $notification->data['title'] ?? 'Notifikasi' }}
                                                    </h4>
                                                    <p class="text-sm text-muted-foreground mb-2 line-clamp-2">
                                                        {{ $notification->data['message'] ?? 'Anda memiliki notifikasi baru' }}
                                                    </p>
                                                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                                        <i class="ki-filled ki-time text-xs"></i>
                                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                    @if ($notification->unread())
                                                        <div class="w-2.5 h-2.5 bg-primary rounded-full"></div>
                                                    @endif
                                                    <i class="ki-filled ki-arrow-right text-muted-foreground group-hover:text-primary transition-colors duration-200 text-sm"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-10">
                            <div class="flex justify-center mb-5">
                                <img alt="no notifications" class="dark:hidden max-h-[113px]"
                                    src="{{ asset('assets/media/illustrations/33.svg') }}" />
                                <img alt="no notifications" class="light:hidden max-h-[113px]"
                                    src="{{ asset('assets/media/illustrations/33-dark.svg') }}" />
                            </div>
                            <h3 class="text-lg font-semibold text-foreground mb-2">
                                Belum ada notifikasi
                            </h3>
                            <p class="text-muted-foreground text-center max-w-md">
                                Notifikasi akan muncul di sini ketika tersedia. Kami akan memberi tahu Anda tentang
                                pembaruan penting dan aktivitas akun.
                            </p>
                        </div>
                    @endif
                </div>

                @if ($notifications->count() > 0 && $notifications->hasPages())
                    <div class="kt-card-footer">
                        <div class="flex items-center justify-between w-full">
                            <div class="flex-1">
                                <span class="text-sm text-muted-foreground">
                                    Menampilkan {{ $notifications->firstItem() }} - {{ $notifications->lastItem() }} dari {{ $notifications->total() }} notifikasi
                                </span>
                            </div>
                            <div class="flex items-center gap-1 ml-auto">
                                {{-- Previous Page Link --}}
                                @if ($notifications->onFirstPage())
                                    <span class="flex items-center justify-center w-9 h-9 text-muted-foreground cursor-not-allowed">
                                        <i class="ki-filled ki-black-left text-sm"></i>
                                    </span>
                                @else
                                    <a href="{{ $notifications->previousPageUrl() }}" 
                                       class="flex items-center justify-center w-9 h-9 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-colors duration-200">
                                        <i class="ki-filled ki-black-left text-sm"></i>
                                    </a>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($notifications->getUrlRange(1, $notifications->lastPage()) as $page => $url)
                                    @if ($page == $notifications->currentPage())
                                        <span class="flex items-center justify-center w-9 h-9 bg-primary text-primary-foreground rounded-lg font-medium text-sm">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $url }}" 
                                           class="flex items-center justify-center w-9 h-9 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-colors duration-200 font-medium text-sm">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($notifications->hasMorePages())
                                    <a href="{{ $notifications->nextPageUrl() }}" 
                                       class="flex items-center justify-center w-9 h-9 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-colors duration-200">
                                        <i class="ki-filled ki-black-right text-sm"></i>
                                    </a>
                                @else
                                    <span class="flex items-center justify-center w-9 h-9 text-muted-foreground cursor-not-allowed">
                                        <i class="ki-filled ki-black-right text-sm"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
