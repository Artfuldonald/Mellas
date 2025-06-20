{{--views/products/partials/reviews.blade.php--}}
<div class="space-y-8">
    <div>
        <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>

        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <!-- Rating Summary -->
            <div class="border rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Overall Rating</h3>
                <div class="flex items-center gap-4">
                    <span class="text-4xl font-bold">{{ number_format($product->rating, 1) }}</span>
                    <div>
                        <div class="flex items-center gap-1 mb-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <i data-lucide="star" class="w-5 h-5 {{ $i <= floor($product->rating) ? 'text-yellow-400 fill-current' : 'text-gray-300' }}"></i>
                            @endfor
                        </div>
                        <p class="text-sm text-gray-600">Based on {{ number_format($product->review_count) }} reviews</p>
                    </div>
                </div>
            </div>

            <!-- Rating Distribution -->
            <div class="border rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Rating Breakdown</h3>
                <div class="space-y-3">
                    @foreach ($ratingDistribution as $rating)
                        <div class="flex items-center gap-3">
                            <span class="text-sm w-8">{{ $rating['stars'] }}★</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $rating['percentage'] }}%"></div>
                            </div>
                            <span class="text-sm text-gray-600 w-12">{{ $rating['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Individual Reviews -->
    @if($product->approvedReviews->count() > 0)
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold">Recent Reviews</h3>
                @auth
                    <button class="border border-gray-300 hover:border-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                        Write a Review
                    </button>
                @endauth
            </div>

            <div class="space-y-6">
                @foreach ($product->approvedReviews->take(5) as $index => $review)
                    <div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-medium">
                                {{ strtoupper(substr($review->user->name, 0, 1)) }}
                            </div>

                            <div class="flex-1 space-y-3">
                                <div class="flex items-center gap-3">
                                    <h4 class="font-semibold">{{ $review->user->name }}</h4>
                                    @if ($review->is_verified)
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Verified Purchase</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-1">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i data-lucide="star" class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400 fill-current' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="text-sm text-gray-600">{{ $review->created_at->format('M j, Y') }}</span>
                                </div>

                                <div>
                                    <h5 class="font-medium mb-2">{{ $review->title }}</h5>
                                    <p class="text-gray-600 leading-relaxed">{{ $review->content }}</p>
                                </div>

                                <div class="flex items-center gap-4">
                                    <button class="flex items-center gap-1 text-gray-600 hover:text-gray-800 text-sm transition-colors">
                                        <i data-lucide="thumbs-up" class="w-4 h-4"></i>
                                        Helpful ({{ $review->helpful_count }})
                                    </button>
                                    <button class="flex items-center gap-1 text-gray-600 hover:text-gray-800 text-sm transition-colors">
                                        <i data-lucide="thumbs-down" class="w-4 h-4"></i>
                                        Not Helpful
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if ($index < $product->approvedReviews->take(5)->count() - 1)
                            <hr class="mt-6 border-gray-200">
                        @endif
                    </div>
                @endforeach
            </div>

            @if($product->approvedReviews->count() > 5)
                <div class="text-center">
                    <button class="border border-gray-300 hover:border-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                        Load More Reviews
                    </button>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-gray-600">No reviews yet. Be the first to review this product!</p>
            @auth
                <button class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                    Write a Review
                </button>
            @endauth
        </div>
    @endif
</div>
