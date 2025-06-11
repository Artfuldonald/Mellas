{{-- resources/views/components/input-error.blade.php --}}
@props(['messages'])

@if (!empty($messages))
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 dark:text-red-400 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            @if (is_array($message))
               
                @foreach ($message as $subMessage)
                    @if(is_string($subMessage)) 
                        <li>{{ $subMessage }}</li>
                    @else                       
                        {{-- <li>[Complex error message - check logs]</li> --}}
                    @endif
                @endforeach
            @elseif (is_string($message))
                <li>{{ $message }}</li>
            @else
                {{-- Optionally log or display a generic error if message is not string or array --}}
                {{-- <li>[Invalid error message format - check logs]</li> --}}
            @endif
        @endforeach
    </ul>
@endif