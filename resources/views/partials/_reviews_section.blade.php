{{-- resources/views/products/partials/_reviews_section.blade.php --}}
<div class="space-y-10">
    <div>
        <h3 class="text-xl font-semibold text-gray-900 mb-6">Customer Reviews</h3>
        @if($reviews->isNotEmpty())
            <div class="space-y-8">
                @foreach($reviews as $review)
                    <div class="flex flex-col sm:flex-row">
                        <div class="mt-0 sm:mt-0 sm:ml-0 sm:mr-6 mb-3 sm:mb-0 text-center">
                            {{-- User Avatar Placeholder --}}
                            <div class="w-12 h-12 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center text-xl font-semibold mx-auto">
                                {{ strtoupper(substr($review->user?->name ?? $review->reviewer_name ?? 'A', 0, 1)) }}
                            </div>
                            <p class="mt-1.5 text-sm font-medium text-gray-900">
                                {{ $review->user?->name ?? $review->reviewer_name ?? 'Anonymous' }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $review->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="flex-1 bg-white p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-1">
                                @for ($i = 0; $i < 5; $i++)
                                    <x-heroicon-s-star class="h-5 w-5 {{ $i < $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" />
                                @endfor
                                @if($review->title)
                                    <p class="ml-3 text-sm font-medium text-gray-900">{{ $review->title }}</p>
                                @endif
                            </div>
                            <div class="mt-2 text-gray-600 prose prose-sm max-w-none">
                                {!! nl2br(e($review->comment)) !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {{-- Pagination for reviews if you have many --}}
            {{-- <div class="mt-8"> {{ $reviews->links() }} </div> --}}
        @else
            <div class="bg-pink-50 border border-pink-200 text-center p-8 rounded-lg">
                <x-heroicon-o-chat-bubble-left-right class="mx-auto h-12 w-12 text-pink-400"/>
                <p class="mt-3 text-lg font-medium text-pink-700">No reviews yet for this product.</p>
                <p class="mt-1 text-sm text-pink-600">Be the first to share your thoughts!</p>
            </div>
        @endif
    </div>

    {{-- Review Form --}}
    <div id="reviews-form-section" class="mt-10 bg-white p-6 rounded-lg shadow-lg border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Write a Review</h3>
        <p class="text-sm text-gray-600 mb-4">Share your thoughts with other customers.</p>
        <form action="{{ route('reviews.store', $product->id) }}" method="POST" class="space-y-4"> {{-- TODO: Create this route --}}
            @csrf
            <div>
                <label for="rating" class="block text-sm font-medium text-gray-700">Your Rating</label>
                <div class="mt-1 flex items-center space-x-1">
                    @for ($i = 1; $i <= 5; $i++)
                    <label class="cursor-pointer">
                        <input type="radio" name="rating" value="{{ $i }}" class="sr-only peer" required>
                        <x-heroicon-s-star class="w-6 h-6 text-gray-300 peer-checked:text-yellow-400 peer-hover:text-yellow-300 transition-colors"/>
                    </label>
                    @endfor
                </div>
                @error('rating') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            @guest
            <div>
                <label for="reviewer_name" class="block text-sm font-medium text-gray-700">Your Name</label>
                <input type="text" name="reviewer_name" id="reviewer_name" value="{{ old('reviewer_name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm">
                @error('reviewer_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="reviewer_email" class="block text-sm font-medium text-gray-700">Your Email (optional, not shown)</label>
                <input type="email" name="reviewer_email" id="reviewer_email" value="{{ old('reviewer_email') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm">
                @error('reviewer_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            @endguest

            <div>
                <label for="review_title" class="block text-sm font-medium text-gray-700">Review Title (optional)</label>
                <input type="text" name="title" id="review_title" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm">
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="comment" class="block text-sm font-medium text-gray-700">Your Review</label>
                <textarea id="comment" name="comment" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm">{{ old('comment') }}</textarea>
                @error('comment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-pink-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2">
                    Submit Review
                </button>
            </div>
        </form>
    </div>
</div>