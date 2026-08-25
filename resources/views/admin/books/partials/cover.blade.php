{{-- The thumbnail inside a row's cover, whether or not the cover is a link.
     Kept apart so the two cases below are each a whole, readable element. --}}
@if ($book->coverUrl())
    <img src="{{ $book->coverUrl() }}" alt="" loading="lazy"
         decoding="async" referrerpolicy="no-referrer">
@endif
<span aria-hidden="true">{{ $book->category?->icon ?: '📘' }}</span>
