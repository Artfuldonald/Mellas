@php

$classes = 'bg-white rounded-lg shadow-md overflow-hidden  border border-transparent group hover:border-pink-500 group transition-colors duration-300'
    
@endphp

<div {{ $attributes(['class' => $classes]) }}>
    {{$slot}}
</div>