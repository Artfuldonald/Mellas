{{-- resources/views/products/partials/_reviews_section.blade.php --}}
<div class="space-y-10">
    {{-- SECTION A: DISPLAY EXISTING REVIEWS --}}
    <div>
        <h3 class="text-xl font-semibold text-gray-900 mb-6">Customer Reviews</h3>
        @if(isset($reviews) && $reviews->isNotEmpty())
            <div class="space-y-8">
                @foreach($reviews as $review)
                    <div class="flex flex-col sm:flex-row">
                        <div class="flex-shrink-0 w-full sm:w-20 text-center mb-3 sm:mb-0 sm:mr-6">
                            <div class="w-12 h-12 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center text-xl font-semibold mx-auto">
                                {{ strtoupper(substr($review->user?->name ?? $review->reviewer_name ?? 'U', 0, 1)) }}
                            </div>
                            <p class="mt-1.5 text-sm font-medium text-gray-900 break-words">
                                {{ $review->user?->name ?? $review->reviewer_name ?? 'Anonymous User' }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $review->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="flex-1 bg-white p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-1">
                                @for ($j = 1; $j <= 5; $j++)
                                    <x-heroicon-s-star class="h-5 w-5 {{ $j <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" />
                                @endfor
                            </div>
                            <div class="mt-2 text-gray-600 prose prose-sm max-w-none">
                                {!! nl2br(e($review->comment)) !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($reviews instanceof \Illuminate\Pagination\LengthAwarePaginator && $reviews->hasPages())
            <div class="mt-8">
                {{ $reviews->links() }}
            </div>
            @endif
        @else
            <div class="bg-pink-50 border border-pink-200 text-center p-8 rounded-lg">
                <x-heroicon-o-chat-bubble-left-right class="mx-auto h-12 w-12 text-pink-400"/>
                <p class="mt-3 text-lg font-medium text-pink-700">No reviews yet for this product.</p>
                @auth
                    <p class="mt-1 text-sm text-pink-600">Be the first to share your thoughts!</p>
                @else
                    <p class="mt-1 text-sm text-pink-600">
                        <a href="{{ route('login') }}?redirect={{ url()->current() }}" class="font-medium hover:underline">Login</a> or
                        <a href="{{ route('register') }}?redirect={{ url()->current() }}" class="font-medium hover:underline">register</a> to write a review.
                    </p>
                @endauth
            </div>
        @endif
    </div>

    {{-- SECTION B: REVIEW SUBMISSION FORM (Only for Authenticated Users) --}}
    @auth
    <div id="reviews-form-section" class="mt-10 bg-white p-6 rounded-lg shadow-xl border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Write Your Review</h3>
        <p class="text-sm text-gray-600 mb-6">Share your experience with other customers.</p>

        {{-- Session Messages & Validation Errors --}}
        @include('admin.partials._session_messages') {{-- Assuming this partial also handles $errors --}}

        <form action="{{ route('reviews.store', $product->id) }}" method="POST" class="space-y-5">
            @csrf
            {{-- ALPINE STAR RATING COMPONENT --}}
            <div x-data="{ rating: {{ (int)old('rating', 0) }}, hoverRating: 0, maxStars: 5 }">
                <label class="block text-sm font-medium text-gray-700 mb-1">Your Rating <span class="text-red-500">*</span></label>
                <input type="number" name="rating" x-model.number="rating" class="sr-only" required min="1" max="5">
                <div class="mt-1 flex items-center space-x-0.5">
                    <template x-for="i in maxStars" :key="i">
                        <button type="button"
                                @click="rating = i"
                                @mouseenter="hoverRating = i"
                                @mouseleave="hoverRating = 0"
                                class="p-0.5 focus:outline-none rounded-full focus:ring-1 focus:ring-pink-400 focus:ring-offset-1"
                                :aria-label="`Rate ${i} out of ${maxStars}`">
                            <svg class="w-7 h-7 transition-colors"
                                 :class="(hoverRating >= i || rating >= i) ? 'text-yellow-400' : 'text-gray-300'"
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        </button>
                    </template>
                </div>
                @error('rating') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
            </div>
            {{-- END ALPINE STAR RATING --}}

            {{-- Comment Textarea --}}
            <div>
                <label for="comment" class="block text-sm font-medium text-gray-700">Your Review <span class="text-red-500">*</span></label>
                <textarea id="comment" name="comment" rows="4" required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm @error('comment') border-red-500 @enderror">{{ old('comment') }}</textarea>
                @error('comment') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
            </div>

            {{-- Submit Button --}}
            <div>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-pink-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 transition-colors">
                    Submit Review
                </button>
            </div>
        </form>
    </div>
    @endauth
</div>