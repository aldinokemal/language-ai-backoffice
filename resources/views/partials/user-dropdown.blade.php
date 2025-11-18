@auth
    <div data-kt-dropdown="true" data-kt-dropdown-offset="10px, 10px" data-kt-dropdown-offset-rtl="-20px, 10px"
        data-kt-dropdown-placement="bottom-start" data-kt-dropdown-placement-rtl="bottom-end" data-kt-dropdown-trigger="click">
        <div class="cursor-pointer shrink-0 flex items-center gap-2" data-kt-dropdown-toggle="true">
            <img alt="{{ auth()->user()->name }}" class="size-9 rounded-full border-2 border-mono/25 shrink-0 cursor-pointer"
                src="{{ getUserImage(auth()->user()) }}" />
            <div class="flex flex-col leading-tight">
                <span class="text-sm font-semibold text-foreground">{{ auth()->user()->name }}</span>
                <span class="text-xs text-muted-foreground">
                    @php
                        $currentRole = session('role');
                    @endphp
                    {{ $currentRole?->userRole?->name ?? '-' }}
                </span>
            </div>
        </div>
        <div class="kt-dropdown-menu w-[250px]" data-kt-dropdown-menu="true">
            <div class="flex items-center justify-between px-2.5 py-1.5 gap-1.5">
                <div class="flex items-center gap-2">
                    <img alt="{{ auth()->user()->name }}" class="size-9 shrink-0 rounded-full border-2 border-green-500"
                        src="{{ getUserImage(auth()->user()) }}" />
                    <div class="flex flex-col gap-1.5">
                        <span class="text-sm text-foreground font-semibold leading-none">
                            {{ auth()->user()->name }}
                        </span>
                        <a class="text-xs text-secondary-foreground hover:text-primary font-medium leading-none"
                            href="#">
                            {{ auth()->user()->email }}
                        </a>
                    </div>
                </div>
            </div>
            <ul class="kt-dropdown-menu-sub">
                <li>
                    <div class="kt-dropdown-menu-separator"></div>
                </li>
                <li>
                    <a class="kt-dropdown-menu-link" href="{{ url('/my-account') }}">
                        <i class="ki-filled ki-profile-circle"></i>
                        Akun Saya
                    </a>
                </li>
                @if (auth()->user()->organizations->count() > 1)
                    <li>
                        <a class="kt-dropdown-menu-link" href="#" data-kt-modal-toggle="#switch_org_modal">
                            <i class="ki-filled ki-arrow-right-left"></i>
                            Pindah Organisasi
                        </a>
                    </li>
                @endif
                @if (session('org')->organizationRoles->count() > 1)
                    <li>
                        <a class="kt-dropdown-menu-link" href="#" data-kt-modal-toggle="#switch_role_modal">
                            <i class="ki-filled ki-arrow-right-left"></i>
                            Pindah Role
                        </a>
                    </li>
                @endif
                <li>
                    <div class="kt-dropdown-menu-separator"></div>
                </li>
            </ul>
            <div class="px-2.5 pt-1.5 mb-2.5 flex flex-col gap-3.5">
                <div class="flex items-center gap-2 justify-between">
                    <span class="flex items-center gap-2">
                        <i class="ki-filled ki-moon text-base text-muted-foreground"></i>
                        <span class="font-medium text-2sm">Mode Gelap</span>
                    </span>
                    <input class="kt-switch" data-kt-theme-switch-state="dark" data-kt-theme-switch-toggle="true"
                        name="dark_mode" type="checkbox" value="1" />
                </div>
                <a class="kt-btn kt-btn-outline justify-center w-full" href="#"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Keluar
                </a>
            </div>
        </div>
    </div>
@endauth
