<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold mb-4 text-gray-800">Customer Reviews</h2>

        <div class="grid md:grid-cols-2 gap-6 mb-6">
            <!-- Rating Summary -->
            <div class="border border-pink-100 rounded-lg p-4 bg-pink-50">
                <h3 class="text-base font-semibold mb-3 text-gray-800">Overall Rating</h3>
                <div class="flex items-center gap-3">
                    <span class="text-3xl font-bold text-gray-900">{{ $product['rating'] ?? '0' }}</span>
                    <div>
                        <div class="flex items-center gap-1 mb-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= floor($product['rating'] ?? 0) ? 'text-yellow-400 fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <p class="text-xs text-gray-600">Based on {{ number_format($product['review_count'] ?? 0) }} reviews</p>
                    </div>
                </div>
            </div>

            <!-- Rating Distribution -->
            <div class="border border-gray-200 rounded-lg p-4 bg-white">
                <h3 class="text-base font-semibold mb-3 text-gray-800">Rating Breakdown</h3>
                <div class="space-y-2">
                    @foreach ($ratingDistribution ?? [] as $rating)
                        <div class="flex items-center gap-2">
                            <span class="text-xs w-6">{{ $rating['stars'] }}★</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-pink-600 h-2 rounded-full" style="width: {{ $rating['percentage'] }}%"></div>
                            </div>
                            <span class="text-xs text-gray-600 w-8">{{ $rating['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Individual Reviews -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Recent Reviews</h3>
            @auth
                <button class="border border-pink-300 hover:border-pink-400 text-pink-700 px-3 py-1 rounded text-sm transition-colors bg-pink-50 hover:bg-pink-100">
                    Write a Review
                </button>
            @endauth
        </div>

        @if(count($reviews ?? []) > 0)
            <div class="space-y-4">
                @foreach ($reviews ?? [] as $index => $review)
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 bg-pink-100 rounded-full flex items-center justify-center text-pink-600 font-medium text-sm">
                                {{ strtoupper(substr($review['author'] ?? 'U', 0, 1)) }}
                            </div>

                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-semibold text-gray-800 text-sm">{{ $review['author'] ?? 'Anonymous' }}</h4>
                                    @if ($review['verified'] ?? false)
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Verified Purchase</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg class="w-3 h-3 {{ $i <= ($review['rating'] ?? 0) ? 'text-yellow-400 fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($review['date'] ?? now())->format('M j, Y') }}</span>
                                </div>

                                <div>
                                    <h5 class="font-medium mb-1 text-gray-800 text-sm">{{ $review['title'] ?? 'Review' }}</h5>
                                    <p class="text-gray-600 leading-relaxed text-sm">{{ $review['content'] ?? 'No content provided.' }}</p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button class="flex items-center gap-1 text-gray-600 hover:text-pink-600 text-xs transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                        </svg>
                                        Helpful ({{ $review['helpful'] ?? 0 }})
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center">
                <button class="border border-pink-300 hover:border-pink-400 text-pink-700 px-4 py-2 rounded text-sm transition-colors bg-pink-50 hover:bg-pink-100">
                    Load More Reviews
                </button>
            </div>
        @else
            <div class="text-center py-6 bg-pink-50 rounded-lg border border-pink-100">
                <p class="text-gray-600 mb-3 text-sm">No reviews yet. Be the first to review this product!</p>
                @auth
                    <button class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded text-sm transition-colors">
                        Write a Review
                    </button>
                @endauth
            </div>
        @endif
    </div>
</div>
