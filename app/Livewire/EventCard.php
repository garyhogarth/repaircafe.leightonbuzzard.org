<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EventCard extends Component
{
    public Event $event;

    /**
     * Attendees who are neither volunteering nor fixing.
     */
    #[Computed]
    public function guestsCount(): int
    {
        return $this->event->users
            ->reject(fn ($user) => $user->pivot->volunteer || $user->pivot->fixer)
            ->count();
    }

    /**
     * How many items the current user has booked into this event.
     */
    #[Computed]
    public function myItemsCount(): int
    {
        if (! auth()->check()) {
            return 0;
        }

        return $this->event->items->where('user_id', auth()->id())->count();
    }

    public function render()
    {
        return view('livewire.event-card');
    }
}
