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

        @if (session('success'))
            <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                <strong class="font-bold">Please correct the errors below:</strong>
                <ul class="mt-1 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('reviews.store', $product->id) }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Your Rating <span class="text-red-500">*</span></label>
                <div class="mt-1 flex items-center space-x-0.5" x-data="{
                    rating: {{ (int)old('rating', 0) }},
                    hoverRating: 0,
                    maxRating: 5,
                    setRating(value) { this.rating = value; },
                    setHoverRating(value) { this.hoverRating = value; },
                    clearHoverRating() { this.hoverRating = 0; }
                }">
                    <input type="number" name="rating" x-model.number="rating" class="hidden" required min="1" max="5"> {{-- This input submits the value --}}

                    <template x-for="starIndex in maxRating" :key="starIndex">
                        <button type="button"
                                @click="setRating(starIndex)"
                                @mouseenter="setHoverRating(starIndex)"
                                @mouseleave="clearHoverRating()"
                                class="p-0.5 focus:outline-none rounded-full focus:ring-2 focus:ring-pink-400 focus:ring-offset-1"
                                :aria-label="`Rate ${starIndex} out of ${maxRating}`">
                            
                        </button>
                    </template>
                </div>
                @error('rating') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="comment" class="block text-sm font-medium text-gray-700">Your Review <span class="text-red-500">*</span></label>
                <textarea id="comment" name="comment" rows="4" required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm @error('comment') border-red-500 @enderror">{{ old('comment') }}</textarea>
                @error('comment') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-pink-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 transition-colors">
                    Submit Review
                </button>
            </div>
        </form>
    </div>
    @endauth
</div>