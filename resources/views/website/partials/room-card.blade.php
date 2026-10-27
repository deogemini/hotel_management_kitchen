<article class="room-card">
    <a class="room-photo" href="{{ route('public.room', ['roomType' => $room->slug] + request()->only('check_in_date', 'check_out_date', 'adults', 'children')) }}">
        @if(! $room->image_is_placeholder || app()->isLocal())
            <img src="{{ $room->image_url }}" alt="{{ $room->type }} room" loading="lazy">
            @if($room->image_is_placeholder)<span class="photo-note photo-note-card">Sample photo</span>@endif
        @else
            <span class="room-placeholder" aria-hidden="true"><span>HR</span><small>Room photo coming soon</small></span>
        @endif
        <span class="room-photo-arrow" aria-hidden="true">↗</span>
    </a>
    <div class="room-card-body">
        <div class="room-card-title"><div><p class="eyebrow">ROOM CATEGORY</p><h3>{{ $room->type }}</h3></div><div class="room-rate"><strong>{{ number_format($room->rate, 0) }}</strong><span>{{ $site['settings']->currency_code ?: config('website.currency') }} / night</span></div></div>
        <p class="room-summary">{{ $room->description ?: 'Contact the lodge for more details about this room category.' }}</p>
        @if($room->features->isNotEmpty())<div class="feature-tags">@foreach($room->features->take(3) as $feature)<span>{{ $feature }}</span>@endforeach</div>@endif
        @if($room->available !== null)<p class="availability-note">{{ $room->available > 0 ? $room->available.' available for selected dates' : 'No availability for selected dates' }}</p>@endif
        <a class="text-link" href="{{ route('public.room', $room->slug) }}">View room details <span aria-hidden="true">→</span></a>
    </div>
</article>
