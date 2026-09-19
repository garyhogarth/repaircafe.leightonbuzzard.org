{{--
    Authenticated account navigation: shown on the public site header, and
    intended to mirror what's offered from inside the dashboard/admin panels
    (see their userMenuItems in App\Providers\Filament) so the same set of
    actions - Dashboard, Admin (if permitted), Account Settings, Log out -
    is available and consistent wherever a user is on the site.
--}}
<a
    href="{{ url('/dashboard') }}"
    class="inline-block px-5 py-1.5 bg-white border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
>
    Dashboard
</a>

@if (Auth::user()->can('access-admin-panel'))
    <a
        href="{{ url('/admin') }}"
        class="inline-block px-5 py-1.5 bg-white border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
    >
        Admin
    </a>
@endif

<details class="relative">
    <summary
        class="list-none [&::-webkit-details-marker]:hidden cursor-pointer inline-flex items-center justify-center w-9 h-9 bg-white border border-[#19140035] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-full text-[#1b1b18]"
        aria-label="Account menu"
    >
        <i class="fas fa-user text-sm"></i>
    </summary>

    <div class="absolute right-0 mt-2 w-48 bg-white border border-[#19140035] rounded-sm shadow-lg py-1 z-20 text-left">
        <div class="px-4 py-2 text-sm text-gray-500 border-b border-[#19140035] truncate">
            {{ Auth::user()->name }}
        </div>

        <a href="{{ route('settings.profile') }}" class="block px-4 py-2 text-sm text-[#1b1b18] hover:bg-gray-50">
            Account Settings
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-[#1b1b18] hover:bg-gray-50">
                Log out
            </button>
        </form>
    </div>
</details>
