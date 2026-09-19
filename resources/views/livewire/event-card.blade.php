<div class="relative flex flex-wrap p-6 min-w-0 break-words bg-white/90 backdrop-blur-sm w-full shadow-lg rounded-2xl text-left">
    <div class="basis-full sm:basis-1/2">
        <h3 class="font-semibold text-xl text-gray-800">
            {{ $event->starts_at->format('l jS \o\f F') }}
        </h3>
        <p class="font-semibold text-gray-800">
            {{ $event->starts_at->format('g:i A') }} - {{ $event->ends_at->format('g:i A') }}
        </p>
        <p class="font-semibold text-gray-500 mb-2">
            {{ $event->venue->name }}
        </p>

        @auth
            @if ($event->fixers->contains(auth()->user()))
                <span class="text-sm bg-green-100 text-green-800 font-bold py-1 px-2 rounded">
                    <i class="fas fa-wrench"></i> Fixing
                </span>
            @elseif ($event->volunteers->contains(auth()->user()))
                <span class="text-sm bg-blue-100 text-blue-800 font-bold py-1 px-2 rounded">
                    <i class="fas fa-clipboard"></i> Helping
                </span>
            @elseif ($event->users->contains(auth()->user()))
                <span class="text-sm bg-yellow-100 text-yellow-800 font-bold py-1 px-2 rounded">
                    <i class="fas fa-handshake"></i> Attending
                </span>
            @endif
        @endauth
    </div>

    <div class="basis-full sm:basis-1/2 pt-4 sm:pt-0">
        <div class="uppercase tracking-wide text-sm text-teal-600 font-semibold">
            Volunteers attending: <strong>{{ $event->volunteers->count() }}</strong>
        </div>
        <div class="text-xs text-gray-600">
            Skills available: {{ collect($event->skills())->flatten()->unique()->implode(', ') ?: 'To be confirmed' }}
        </div>
        <div class="mt-2 uppercase tracking-wide text-sm text-yellow-600 font-semibold">
            Guests attending: <strong>{{ $event->users->count() - $event->volunteers->count() }}</strong>
        </div>
        <div class="uppercase tracking-wide text-sm text-indigo-600 font-semibold">
            Items booked in: <strong>{{ $event->items_count }}</strong>
        </div>
    </div>

    @guest
        <div class="basis-full pt-4">
            <a href="{{ route('register') }}"
                class="inline-block bg-green-700 hover:bg-green-600 text-white py-2 px-4 rounded">
                Register to attend
            </a>
        </div>
    @endguest
</div>
