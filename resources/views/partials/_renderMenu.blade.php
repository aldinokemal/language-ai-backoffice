@foreach ($children as $menu)
    @php
        $isActive = request()->is(trim($menu->url ?? '', '/')) || request()->is(trim($menu->url ?? '', '/').'/*');
        $hasChildren = !empty($menu->children);
        $isChildrenOpen = isChildOpen($menu->children ?? []);
        $menuClasses = 'kt-menu-item ' . ($isActive ? 'here active ' : '') . ($isChildrenOpen ? 'here show' : '');
    @endphp

    <div class="{{ $menuClasses }}" 
         @if($hasChildren) data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click" @endif>

        @if (!$hasChildren)
            <a href="{{ url($menu->url) }}" 
               class="kt-menu-link py-2 px-2.5 rounded-md kt-menu-item-active:bg-secondary kt-menu-link-hover:bg-secondary" 
               tabindex="0">
                <span class="kt-menu-title text-sm text-foreground kt-menu-item-active:font-medium kt-menu-item-active:text-mono kt-menu-link-hover:text-mono">
                    {{ $menu->name }}
                </span>
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