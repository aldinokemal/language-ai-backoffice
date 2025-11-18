<!-- Sidebar -->
<div class="flex-col fixed top-0 bottom-0 z-20 hidden lg:flex items-stretch shrink-0 w-(--sidebar-width) dark [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]"
    data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start flex top-0 bottom-0" id="sidebar">

    <!-- Sidebar Header -->
    <div class="flex flex-col gap-2.5" id="sidebar_header">
        <div class="flex items-center gap-2.5 px-3.5 h-[70px]">
            <a href="#">
                <img class="h-9 rounded-md" src="{{ asset('assets/media/app/icon.png') }}"
                    alt="{{ config('app.name') }}" />
            </a>
            <div class="text-lg font-medium text-mono">
                {{ config('app.name') }}
            </div>
        </div>
        @auth
            <div class="flex items-center gap-2.5 px-3.5 w-full">
                <button class="kt-btn kt-btn-icon kt-btn-secondary px-3 [&_i]:text-white w-full justify-between"
                    data-kt-modal-toggle="#search_modal">
                    <span class="flex items-center gap-2">
                        Cari Menu<i class="ki-filled ki-magnifier"></i>
                    </span>
                    <kbd class="px-1.5 py-0.5 text-xs bg-black/20 rounded text-white/70">⌘K</kbd>
                </button>
            </div>
        @endauth
    </div>
    <!-- End of Sidebar Header -->

    <!-- Sidebar menu -->
    <div class="flex items-stretch grow shrink-0 justify-center my-5" id="sidebar_menu">
        <div class="kt-scrollable-y-auto grow" data-kt-scrollable="true"
            data-kt-scrollable-dependencies="#sidebar_header, #sidebar_footer" data-kt-scrollable-height="auto"
            data-kt-scrollable-offset="0px" data-kt-scrollable-wrappers="#sidebar_menu">
            <!-- Primary Menu -->
            <hr class="mt-2 mb-5">
            <div class="mb-5">
                <div class="kt-menu flex flex-col w-full gap-1.5 px-3.5" data-kt-menu="true"
                    data-kt-menu-accordion-expand-all="false" id="sidebar_primary_menu">
                    @auth
                        <!-- Hardcoded Dashboard Menu -->
                        <div class="kt-menu-item {{ request()->routeIs('dashboard') ? 'here active' : '' }}">
                            <a href="{{ route('dashboard') }}"
                                class="kt-menu-link gap-2.5 py-2 px-2.5 rounded-md kt-menu-item-active:bg-accent/60 kt-menu-link-hover:bg-accent/60"
                                tabindex="0">
                                <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                    <i class="ki-filled ki-element-11"></i>
                                </span>
                                <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono">
                                    Dashboard
                                </span>
                            </a>
                        </div>

                        @foreach (session('menu', []) as $menu)
                            @php
                                $isActive = request()->is(trim($menu->url ?? '', '/'));
                                $hasChildren = !empty($menu->children);
                                $isChildrenOpen = isChildOpen($menu->children ?? []);
                                $menuClasses =
                                    'kt-menu-item ' .
                                    ($isActive ? 'here active ' : '') .
                                    ($isChildrenOpen ? 'here show' : '');
                            @endphp

                            <div class="{{ $menuClasses }}"
                                @if ($hasChildren) data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click" @endif>
                                @if (!$hasChildren)
                                    <a href="{{ url($menu->url) }}"
                                        class="kt-menu-link gap-2.5 py-2 px-2.5 rounded-md kt-menu-item-active:bg-accent/60 kt-menu-link-hover:bg-accent/60"
                                        tabindex="0">
                                        @include('partials._menuContent', ['menu' => $menu])
                                    </a>
                                @else
                                    <div class="kt-menu-link gap-2.5 py-2 px-2.5 rounded-md kt-menu-item-hover:bg-transparent kt-menu-item-here:bg-transparent"
                                        tabindex="0">
                                        @include('partials._menuContent', ['menu' => $menu])
                                    </div>

                                    <div class="kt-menu-accordion gap-px ps-7 {{ $isChildrenOpen ? 'here show' : '' }}">
                                        @include('partials._renderMenu', ['children' => $menu->children])
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endauth
                </div>
            </div>
            <!-- End of Primary Menu -->
        </div>
    </div>
    <!-- End of Sidebar menu-->

    <!-- Footer -->
    @auth
        <div class="flex flex-center justify-between shrink-0 ps-4 pe-3.5 mb-3.5" id="sidebar_footer">
            <!-- User -->
            @include('partials.user-dropdown')
            <!-- End of User -->

            <div class="flex items-center gap-1.5">
                <!-- Notifications -->
                <button class="kt-btn kt-btn-ghost kt-btn-icon size-8 hover:bg-background hover:[&_i]:text-primary relative"
                    data-kt-drawer-toggle="#notifications_drawer" data-kt-tooltip="#notifications_tooltip"
                    data-kt-tooltip-placement="top">
                    <i
                        class="ki-filled ki-notification-status text-lg {{ auth()->user()->unreadNotifications->count() > 0 ? 'text-primary' : '' }}"></i>
                    @if (auth()->user()->unreadNotifications->count() > 0)
                        <span
                            class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                            {{ auth()->user()->unreadNotifications->count() > 99 ? '99+' : auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </button>

                <a class="kt-btn kt-btn-ghost kt-btn-icon size-8 hover:bg-background hover:[&_i]:text-primary"
                    href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    data-kt-tooltip="#logout_tooltip" data-kt-tooltip-placement="top">
                    <i class="ki-filled ki-exit-right"></i>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>

                <!-- Tooltip content -->
                <div id="notifications_tooltip" class="kt-tooltip">
                    Notifications
                </div>
                <div id="logout_tooltip" class="kt-tooltip">
                    Logout
                </div>
            </div>
        </div>
    @endauth
    <!-- End of Footer -->
</div>
<!-- End of Sidebar -->
