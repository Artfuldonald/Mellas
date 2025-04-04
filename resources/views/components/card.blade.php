<!-- resources/views/components/card.blade.php -->
@props(['title' => null, 'icon' => null, 'actions' => null])

<div {{ $attributes->merge(['class' => 'bg-card text-card-foreground rounded-lg border border-border shadow-sm']) }}>
    @if($title || $icon || $actions)
    <div class="flex items-center justify-between p-6 border-b border-border">
        <div class="flex items-center space-x-2">
            @if($icon)
            <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                {{ $icon }}
            </div>
            @endif
            
            @if($title)
            <h3 class="text-lg font-medium">{{ $title }}</h3>
            @endif
        </div>
        
        @if($actions)
        <div>
            {{ $actions }}
        </div>
        @endif
    </div>
    @endif
    
    <div class="p-6">
        {{ $slot }}
    </div>
</div>