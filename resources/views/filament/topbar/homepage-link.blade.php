{{--
    Shown in both the admin and dashboard panel topbars (see their
    renderHook(PanelsRenderHook::TOPBAR_END, ...) calls) so there's always
    a visible, one-click way back to the main site - matching the public
    header's Dashboard/Admin buttons, so users don't feel stuck once
    they're inside a panel.
--}}
<x-filament::icon-button
    tag="a"
    :href="route('home')"
    icon="heroicon-o-home"
    color="gray"
    icon-size="lg"
    label="Repair Cafe Homepage"
    tooltip="Back to the Repair Cafe website"
    class="fi-topbar-homepage-btn"
/>
