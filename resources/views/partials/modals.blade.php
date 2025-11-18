<!-- Menu Search Modal -->
<div class="kt-modal" data-kt-modal="true" id="search_modal">
    <div class="kt-modal-content max-w-[600px] top-[15%]">
        <div class="kt-modal-header py-4 px-5">
            <i class="ki-filled ki-magnifier text-muted-foreground text-xl"></i>
            <input class="kt-input kt-input-ghost"
                   name="query"
                   placeholder="Cari menu..."
                   type="text"
                   value=""
                   id="menu_search_input"/>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body p-0 pb-5">
            <div class="kt-scrollable-y-auto" data-kt-scrollable="true" data-kt-scrollable-max-height="400px">

                <!-- Empty State / Initial State -->
                <div id="menu_search_empty" class="flex flex-col text-center py-9 gap-5">
                    <div class="flex justify-center">
                        <img alt="search" class="dark:hidden max-h-[113px]" src="{{ asset('assets/media/illustrations/33.svg') }}"/>
                        <img alt="search" class="light:hidden max-h-[113px]" src="{{ asset('assets/media/illustrations/33-dark.svg') }}"/>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <h3 class="text-base font-semibold text-mono text-center">
                            Mulai mengetik untuk mencari menu...
                        </h3>
                        <span class="text-sm font-medium text-center text-secondary-foreground">
                            Cari menu berdasarkan nama atau kategori
                        </span>
                    </div>
                </div>

                <!-- No Results State -->
                <div id="menu_search_no_results" class="hidden">
                    <div class="flex flex-col text-center py-9 gap-5">
                        <div class="flex justify-center">
                            <i class="ki-filled ki-file-deleted text-6xl text-muted-foreground"></i>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <h3 class="text-base font-semibold text-mono text-center">
                                Tidak ada menu ditemukan
                            </h3>
                            <span class="text-sm font-medium text-center text-secondary-foreground">
                                Coba gunakan kata kunci yang berbeda
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Search Results -->
                <div id="menu_search_results" class="hidden">
                    <div class="px-5 py-2">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            Hasil Pencarian
                        </span>
                    </div>
                    <div id="menu_results_container">
                        <!-- Results will be populated via JavaScript -->
                    </div>
                </div>

                <!-- Loading State -->
                <div id="menu_search_loading" class="hidden">
                    <div class="flex flex-col text-center py-9 gap-5">
                        <div class="flex justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                        </div>
                        <span class="text-sm font-medium text-center text-secondary-foreground">
                            Mencari menu...
                        </span>
                    </div>
                </div>

            </div>

            <!-- Footer with shortcuts -->
            <div class="border-t border-border px-5 py-3">
                <div class="flex items-center justify-between text-xs text-muted-foreground">
                    <span>
                        <kbd class="px-1.5 py-0.5 text-xs bg-secondary rounded">↑↓</kbd> untuk navigasi
                    </span>
                    <span>
                        <kbd class="px-1.5 py-0.5 text-xs bg-secondary rounded">Enter</kbd> untuk pilih
                    </span>
                    <span>
                        <kbd class="px-1.5 py-0.5 text-xs bg-secondary rounded">Esc</kbd> untuk tutup
                    </span>
                </div>
                <div class="mt-2 text-center">
                    <span class="text-xs text-muted-foreground">
                        Tekan <kbd class="px-1.5 py-0.5 text-xs bg-secondary rounded">⌘K</kbd> atau
                        <kbd class="px-1.5 py-0.5 text-xs bg-secondary rounded">Ctrl+K</kbd> untuk membuka pencarian
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notifications Drawer -->
<div class="hidden kt-drawer kt-drawer-end max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border bg-background shadow-xl backdrop-blur-sm"
     data-kt-drawer="true"
     data-kt-drawer-container="body"
     id="notifications_drawer">

    <!-- Header - Fixed -->
    <div class="kt-drawer-header border-b border-border bg-muted rounded-t-xl">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-primary/20 rounded-lg">
                <i class="ki-filled ki-notification-on text-primary text-xl"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-foreground text-lg font-bold">Notifikasi</span>
                @if (auth()->user()->unreadNotifications->count() > 0)
                    <span class="text-xs text-primary font-medium">{{ auth()->user()->unreadNotifications->count() }} belum dibaca</span>
                @endif
            </div>
        </div>
        <button class="kt-drawer-close kt-btn kt-btn-sm kt-btn-icon kt-btn-light hover:bg-muted hover:shadow-sm transition-all duration-200" data-kt-drawer-dismiss="true">
            <i class="ki-filled ki-cross text-muted-foreground"></i>
        </button>
    </div>

    <!-- Tabs - Fixed -->
    <div class="px-6 py-3 bg-background border-b border-border">
        <div class="kt-tabs kt-tabs-line" data-kt-tabs="true" id="notifications_tabs">
            <div class="flex items-center gap-6">
                <button class="kt-tab-toggle py-2.5 px-1 active relative font-medium text-sm text-secondary-foreground hover:text-primary transition-colors duration-200" data-kt-tab-toggle="#notifications_tab_unread">
                    Belum Dibaca
                    @if (auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute -top-0.5 -right-1 min-w-[18px] h-[18px] bg-primary text-primary-foreground text-[10px] font-bold rounded-full flex items-center justify-center">
                            {{ auth()->user()->unreadNotifications->count() > 99 ? '99+' : auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </button>
                <button class="kt-tab-toggle py-2.5 px-1 font-medium text-sm text-muted-foreground hover:text-primary transition-colors duration-200" data-kt-tab-toggle="#notifications_tab_all">
                    Semua
                </button>
            </div>
        </div>
    </div>

    <!-- Content Area - Scrollable -->
    <div class="kt-drawer-content kt-scrollable bg-muted/30">
        <!-- Unread Notifications Tab -->
        <div class="h-full" id="notifications_tab_unread">
            @if (auth()->user()->unreadNotifications->count() > 0)
                <div class="flex flex-col">
                    @foreach (auth()->user()->unreadNotifications as $notification)
                        <a href="{{ route('notification.open', ['id' => encrypt($notification->id)]) }}"
                           class="flex gap-4 px-6 py-4 border-b border-border hover:bg-muted hover:shadow-sm transition-all duration-200 cursor-pointer group bg-primary/5">
                            <div class="relative shrink-0 mt-1">
                                <div class="w-9 h-9 flex items-center justify-center rounded-full bg-primary text-primary-foreground group-hover:scale-110 transition-transform duration-200">
                                    <i class="ki-filled ki-notification-on text-sm"></i>
                                </div>
                                <span class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-blue-500 rounded-full border-2 border-background animate-pulse"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col gap-2">
                                    <h4 class="text-sm font-bold text-foreground group-hover:text-primary transition-colors duration-200 line-clamp-1">
                                        {{ $notification->data['title'] ?? 'Notifikasi' }}
                                    </h4>
                                    <p class="text-sm text-secondary-foreground line-clamp-2 leading-relaxed">
                                        {{ $notification->data['message'] ?? 'Anda memiliki notifikasi baru' }}
                                    </p>
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-xs text-muted-foreground font-medium">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                        <span class="text-xs text-primary font-semibold px-2 py-1 bg-primary/20 rounded-full">
                                            Baru
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200 self-center">
                                <i class="ki-filled ki-right text-muted-foreground text-sm"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-full text-center px-6 py-12">
                    <div class="mb-6 p-4 bg-primary/20 rounded-full">
                        <i class="ki-filled ki-check text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-foreground mb-2">
                        Tidak ada notifikasi yang belum dibaca
                    </h3>
                    <p class="text-sm text-secondary-foreground leading-relaxed max-w-xs">
                        Semua notifikasi sudah dibaca
                    </p>
                </div>
            @endif
        </div>

        <!-- All Notifications Tab -->
        <div class="hidden h-full" id="notifications_tab_all">
            @if (auth()->user()->notifications->count() > 0)
                <div class="flex flex-col">
                    @foreach (auth()->user()->notifications as $notification)
                        <a href="{{ route('notification.open', ['id' => encrypt($notification->id)]) }}"
                           class="flex gap-4 px-6 py-4 border-b border-border hover:bg-muted hover:shadow-sm transition-all duration-200 cursor-pointer group {{ $notification->unread() ? 'bg-primary/5' : 'bg-transparent' }}">
                            <div class="relative shrink-0 mt-1">
                                <div class="w-9 h-9 flex items-center justify-center rounded-full {{ $notification->unread() ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground' }} group-hover:scale-110 transition-transform duration-200">
                                    <i class="ki-filled {{ $notification->unread() ? 'ki-notification-on' : 'ki-notification' }} text-sm"></i>
                                </div>
                                @if ($notification->unread())
                                    <span class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-blue-500 rounded-full border-2 border-background"></span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col gap-2">
                                    <h4 class="text-sm {{ $notification->unread() ? 'font-bold text-foreground' : 'font-semibold text-secondary-foreground' }} group-hover:text-primary transition-colors duration-200 line-clamp-1">
                                        {{ $notification->data['title'] ?? 'Notifikasi' }}
                                    </h4>
                                    <p class="text-sm {{ $notification->unread() ? 'text-secondary-foreground' : 'text-muted-foreground' }} line-clamp-2 leading-relaxed">
                                        {{ $notification->data['message'] ?? 'Anda memiliki notifikasi baru' }}
                                    </p>
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-xs text-muted-foreground font-medium">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                        @if ($notification->unread())
                                            <span class="text-xs text-primary font-semibold px-2 py-1 bg-primary/20 rounded-full">
                                                Baru
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200 self-center">
                                <i class="ki-filled ki-right text-muted-foreground text-sm"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-full text-center px-6 py-12">
                    <div class="mb-6 p-4 bg-muted rounded-full">
                        <i class="ki-filled ki-notification text-4xl text-muted-foreground"></i>
                    </div>
                    <h3 class="text-lg font-bold text-foreground mb-2">
                        Belum ada notifikasi
                    </h3>
                    <p class="text-sm text-secondary-foreground leading-relaxed max-w-xs">
                        Notifikasi akan muncul di sini ketika tersedia
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Footer - Fixed -->
    <div class="kt-drawer-footer border-t border-border bg-muted rounded-b-xl">
        <div class="grid grid-cols-2 gap-4">
            <a href="{{ route('notification.markAllAsRead') }}"
               class="kt-btn kt-btn-outline kt-btn-sm justify-center flex items-center gap-2.5 py-2.5 px-4 border-primary text-primary hover:bg-primary hover:text-primary-foreground hover:border-primary transition-all duration-200 font-medium shadow-sm">
                <i class="ki-filled ki-check text-sm"></i>
                <span class="text-sm">Tandai Semua</span>
            </a>
            <a href="{{ route('notifications') }}"
               class="kt-btn kt-btn-outline kt-btn-sm justify-center flex items-center gap-2.5 py-2.5 px-4 border-border text-secondary-foreground hover:bg-secondary hover:text-secondary-foreground hover:border-secondary transition-all duration-200 font-medium shadow-sm">
                <i class="ki-filled ki-eye text-sm"></i>
                <span class="text-sm">Lihat Semua</span>
            </a>
        </div>
    </div>

</div>

<!-- Switch Role Modal -->
<div class="kt-modal" data-kt-modal="true" id="switch_role_modal">
    <div class="kt-modal-content max-w-[600px] top-[25%]">
        <div class="kt-modal-header py-4 px-5">
            <h3 class="kt-modal-title">
                Pindah Role ({{ session('org')->organization->name }})
            </h3>
            <button class="kt-btn kt-btn-xs kt-btn-icon kt-btn-light" data-kt-modal-dismiss="true">
                <i class="ki-outline ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body pb-10">
            <div class="flex flex-col gap-2.5">
                @foreach (session('org')->organizationRoles as $role)
                    <a href="{{ route('switch.role', ['role_id' => encrypt($role->role_id)]) }}"
                       class="kt-btn kt-btn-sm kt-btn-light flex justify-between items-center {{ session('role')->role_id === $role->role_id ? 'disabled' : '' }}">
                        {{ $role->userRole->name }}
                        @if(session('role')->role_id === $role->role_id)
                            <i class="ki-outline ki-check text-base"></i>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Switch Organization Modal -->
<div class="kt-modal" data-kt-modal="true" id="switch_org_modal">
    <div class="kt-modal-content max-w-[600px] top-[25%]">
        <div class="kt-modal-header py-4 px-5">
            <h3 class="kt-modal-title">
                Pindah Organisasi ({{ session('org')->organization->name }})
            </h3>
            <button class="kt-btn kt-btn-xs kt-btn-icon kt-btn-light" data-kt-modal-dismiss="true">
                <i class="ki-outline ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body pb-10">
            <div class="flex flex-col gap-2.5">
                @foreach (auth()->user()->organizations()->with('organization')->get() as $organization)
                    <a href="{{ route('switch.organization', ['user_organization_id' => encrypt($organization->id)]) }}"
                       class="kt-btn kt-btn-sm kt-btn-light flex justify-between items-center {{ session('org')->id === $organization->id ? 'disabled' : '' }}">
                        {{ $organization->organization->name }}
                        @if(session('org')->id === $organization->id)
                            <i class="ki-outline ki-check text-base"></i>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
