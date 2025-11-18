@if($menu->icon)
<span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
    <i class="ki-filled {{ $menu->icon }}"></i>
</span>
@endif
<span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono">
    {{ $menu->name }}
</span>
@if(!empty($menu->children))
<span class="kt-menu-arrow text-muted-foreground kt-menu-item-here:text-muted-foreground kt-menu-item-show:text-foreground kt-menu-link-hover:text-foreground">
    <span class="inline-flex kt-menu-item-show:hidden">
        <i class="ki-filled ki-down text-xs"></i>
    </span>
    <span class="hidden kt-menu-item-show:inline-flex">
        <i class="ki-filled ki-up text-xs"></i>
    </span>
</span>
@endif