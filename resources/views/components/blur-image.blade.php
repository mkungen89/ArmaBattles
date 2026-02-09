@props(['src', 'alt' => '', 'class' => ''])
<img src="{{ $src }}" alt="{{ $alt }}" loading="lazy"
     class="blur-up {{ $class }}"
     onload="this.classList.add('loaded')"
     onerror="this.classList.add('loaded')">
