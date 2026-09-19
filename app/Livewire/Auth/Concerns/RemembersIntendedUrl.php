<?php

namespace App\Livewire\Auth\Concerns;

use Illuminate\Support\Facades\Session;

trait RemembersIntendedUrl
{
    /**
     * Remember where the user came from, so a successful login/registration
     * can return them there instead of always landing on the dashboard.
     */
    public function rememberIntendedUrl(): void
    {
        if (Session::has('url.intended')) {
            return;
        }

        $previous = Session::previousUrl();
        $previousPath = $previous ? (parse_url($previous, PHP_URL_PATH) ?? '') : '';

        $authPathPrefixes = ['/login', '/register', '/forgot-password', '/reset-password', '/verify-email'];

        $isAuthPage = collect($authPathPrefixes)->contains(fn ($prefix) => str_starts_with($previousPath, $prefix));

        if ($previous && ! $isAuthPage) {
            Session::put('url.intended', $previous);
        }
    }
}
