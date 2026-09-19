<div class="bg-white w-full shadow-lg rounded-2xl p-8 sm:p-10 text-left">
    <div class="flex flex-col md:flex-row md:items-start gap-8 md:gap-16">
        <div class="md:w-1/3 md:flex-shrink-0">
            <h3 class="font-bold text-2xl text-gray-900">
                {{ $event->starts_at->format('l jS \o\f F') }}
            </h3>
            <p class="font-semibold text-lg text-gray-800 mt-1">
                {{ $event->starts_at->format('g:i A') }} - {{ $event->ends_at->format('g:i A') }}
            </p>
            <p class="font-semibold text-gray-500 mt-1">
                {{ $event->venue->name }}
            </p>

            @auth
                <div class="mt-4">
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
                </div>
            @endauth
        </div>

        <div class="md:flex-1 text-center">
            <div class="uppercase tracking-wide text-sm text-teal-600 font-semibold">
                Volunteers attending: <strong>{{ $event->volunteers->count() }}</strong>
            </div>
            <p class="text-gray-700 mt-2">
                Skills available: {{ collect($event->skills())->flatten()->unique()->implode(', ') ?: 'To be confirmed' }}
            </p>
            <div class="mt-4 uppercase tracking-wide text-sm text-yellow-600 font-semibold">
                Guests attending: <strong>{{ $this->guestsCount }}</strong>
            </div>
            <div class="uppercase tracking-wide text-sm text-indigo-600 font-semibold">
                Items booked in: <strong>{{ $event->items_count }}</strong>
            </div>

            @auth
                <div class="mt-2 uppercase tracking-wide text-sm text-purple-600 font-semibold">
                    Your items booked in: <strong>{{ $this->myItemsCount }}</strong>
                </div>
            @endauth
        </div>
    </div>

    <div class="text-center mt-8">
        @guest
            <a href="{{ route('register') }}"
                class="inline-block bg-green-700 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg">
                Register to attend
            </a>
        @else
            <a href="{{ route('filament.dashboard.resources.items.create') }}"
                class="inline-block bg-green-700 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg">
                {{ $this->myItemsCount > 0 ? 'Book another item in' : 'Book an item in' }}
            </a>
        @endguest
    </div>
</div>
