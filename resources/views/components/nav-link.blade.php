@props([

    'href'=>'#',
    'active' => false
    ])

<a href="{{$href}}" class="text-gray-600 hover:text-pink-500  {{$active ? 'text-pink-500' : 'text-gray-600'}}">
    {{$slot}}
</a>