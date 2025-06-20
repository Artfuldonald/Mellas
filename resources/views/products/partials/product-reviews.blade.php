<div x-data="productReviews({
        productId: {{ $product['id'] }},
        isAuthenticated: {{ auth()->check() ? 'true' : 'false' }}
    })" class="space-y-8">

    {{-- Review Summary and Breakdown --}}
    <div>
        <h2 class="text-xl font-bold mb-4 text-gray-800">Customer Reviews</h2>
        <div class="grid md:grid-cols-2 gap-6 mb-6">
            {{-- Rating Summary --}}
            <div class="border border-pink-100 rounded-lg p-4 bg-pink-50">
                <h3 class="text-base font-semibold mb-3 text-gray-800">Overall Rating</h3>
                <div class="flex items-center gap-3">
                    <span class="text-3xl font-bold text-gray-900">{{ number_format($product['rating'], 1) }}</span>
                    <div>
                        <div class="flex items-center gap-1 mb-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= floor($product['rating']) ? 'text-yellow-400 fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <p class="text-xs text-gray-600">Based on {{ $product['review_count'] }} {{ Str::plural('review', $product['review_count']) }}</p>
                    </div>
                </div>
            </div>
            {{-- Rating Distribution --}}
            <div class="border border-gray-200 rounded-lg p-4 bg-white">
                <h3 class="text-base font-semibold mb-3 text-gray-800">Rating Breakdown</h3>
                <div class="space-y-2">
                    @foreach ($ratingDistribution as $rating)
                        <div class="flex items-center gap-2">
                            <span class="text-xs w-10 text-right">{{ $rating['stars'] }} ★</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2"><div class="bg-pink-600 h-2 rounded-full" style="width: {{ $rating['percentage'] }}%"></div></div>
                            <span class="text-xs text-gray-600 w-8 text-right">{{ $rating['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Individual Reviews & Write Button --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Recent Reviews</h3>
            <button @click="openForm()" class="border border-pink-300 hover:border-pink-400 text-pink-700 px-3 py-1 rounded text-sm transition-colors bg-pink-50 hover:bg-pink-100">
                Write a Review
            </button>
        </div>

        @forelse ($reviews as $review)
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex gap-3">
                    <div class="w-8 h-8 bg-pink-100 rounded-full flex items-center justify-center text-pink-600 font-medium text-sm flex-shrink-0">
                        {{ strtoupper(substr($review->reviewer_name, 0, 1)) }}
                    </div>
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center gap-2">
                            <h4 class="font-semibold text-gray-800 text-sm">{{ $review->reviewer_name }}</h4>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-3 h-3 {{ $i <= $review->rating ? 'text-yellow-400 fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-600">{{ $review->created_at->format('M j, Y') }}</span>
                        </div>
                        <div>
                            <h5 class="font-medium mb-1 text-gray-800 text-sm">{{ $review->title }}</h5>
                            <p class="text-gray-600 leading-relaxed text-sm prose max-w-none">{{ $review->comment }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-6 bg-pink-50 rounded-lg border border-pink-100">
                <p class="text-gray-600 mb-3 text-sm">No reviews yet. Be the first to review this product!</p>
                <button @click="openForm()" class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    Write a Review
                </button>
            </div>
        @endforelse

        @if($reviews->hasPages())
        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>

    {{-- "Write a Review" Modal Form --}}
    <div x-show="isFormOpen" class="fixed z-50 inset-0 overflow-y-auto" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isFormOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeForm()" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>
            <div x-show="isFormOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form @submit.prevent="submitReview()">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Write a Review</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Your Rating*</label>
                                <div class="flex items-center gap-1 mt-1" @mouseleave="hoverRating = 0">
                                    <template x-for="i in 5" :key="i">
                                        <svg @mouseenter="hoverRating = i" @click="newReview.rating = i" class="w-6 h-6 cursor-pointer transition-colors" :class="i <= (hoverRating || newReview.rating) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    </template>
                                </div>
                            </div>
                            <template x-if="!isAuthenticated">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="reviewer_name" class="block text-sm font-medium text-gray-700">Name*</label>
                                        <input type="text" x-model="newReview.reviewer_name" id="reviewer_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="reviewer_email" class="block text-sm font-medium text-gray-700">Email*</label>
                                        <input type="email" x-model="newReview.reviewer_email" id="reviewer_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm">
                                    </div>
                                </div>
                            </template>
                            <div>
                                <label for="review_title" class="block text-sm font-medium text-gray-700">Review Title*</label>
                                <input type="text" x-model="newReview.title" id="review_title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="review_comment" class="block text-sm font-medium text-gray-700">Your Review*</label>
                                <textarea x-model="newReview.comment" id="review_comment" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" :disabled="isLoading"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-pink-600 text-base font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:ml-3 sm:w-auto sm:text-sm disabled:bg-pink-400">
                            <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="isLoading ? 'Submitting...' : 'Submit Review'"></span>
                        </button>
                        <button type="button" @click="closeForm()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function productReviews(config) {
        return {
            productId: config.productId,
            isAuthenticated: config.isAuthenticated,
            isFormOpen: false,
            isLoading: false,
            hoverRating: 0,
            newReview: {
                rating: 0,
                title: '',
                comment: '',
                reviewer_name: '',
                reviewer_email: '',
            },
            
            openForm() { this.isFormOpen = true; document.body.style.overflow = 'hidden'; },
            closeForm() { this.isFormOpen = false; document.body.style.overflow = ''; },

            resetForm() {
                this.newReview.rating = 0;
                this.newReview.title = '';
                this.newReview.comment = '';
                if (!this.isAuthenticated) {
                    this.newReview.reviewer_name = '';
                    this.newReview.reviewer_email = '';
                }
            },

            submitReview() {
                if (this.newReview.rating === 0) {
                    window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'error', message: 'Please select a star rating.' }}));
                    return;
                }
                this.isLoading = true;

                fetch(`{{ route('reviews.store') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        ...this.newReview,
                        product_id: this.productId,
                    })
                })
                .then(res => res.json().then(data => ({ ok: res.ok, data })))
                .then(({ ok, data }) => {
                    window.dispatchEvent(new CustomEvent('toast-show', {
                        detail: { type: ok ? 'success' : 'error', message: data.message }
                    }));

                    if (ok) {
                        this.closeForm();
                        this.resetForm();
                    }
                })
                .catch(err => {
                    console.error('Review submission error:', err);
                    window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'error', message: 'A network error occurred.' }}));
                })
                .finally(() => {
                    this.isLoading = false;
                });
            }
        }
    }
</script>
@endpush