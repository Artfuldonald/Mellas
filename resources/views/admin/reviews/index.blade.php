<x-admin-layout title="Manage Reviews">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">Customer Reviews</h1>
                <p class="mt-2 text-sm text-gray-700">Approve, unapprove, or delete customer reviews.</p>
            </div>
        </div>

        <!-- Session Messages -->
        @include('admin.partials._session_messages')

        <!-- Tabs -->
        <div class="mt-4">
            <div class="sm:hidden">
                <label for="tabs" class="sr-only">Select a tab</label>
                <select id="tabs" name="tabs" onchange="window.location = this.value;" class="block w-full rounded-md border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                    <option value="{{ route('admin.reviews.index', ['status' => 'pending']) }}" {{ request('status', 'pending') == 'pending' ? 'selected' : '' }}>
                        Pending ({{ $pendingCount }})
                    </option>
                    <option value="{{ route('admin.reviews.index', ['status' => 'approved']) }}" {{ request('status') == 'approved' ? 'selected' : '' }}>
                        Approved ({{ $approvedCount }})
                    </option>
                </select>
            </div>
            <div class="hidden sm:block">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}"
                           class="{{ request('status', 'pending') != 'approved' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm">
                            Pending
                            <span class="{{ request('status', 'pending') != 'approved' ? 'bg-pink-100 text-pink-600' : 'bg-gray-100 text-gray-900' }} ml-3 hidden rounded-full py-0.5 px-2.5 text-xs font-medium md:inline-block">{{ $pendingCount }}</span>
                        </a>
                        <a href="{{ route('admin.reviews.index', ['status' => 'approved']) }}"
                           class="{{ request('status') == 'approved' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm">
                            Approved
                            <span class="{{ request('status') == 'approved' ? 'bg-pink-100 text-pink-600' : 'bg-gray-100 text-gray-900' }} ml-3 hidden rounded-full py-0.5 px-2.5 text-xs font-medium md:inline-block">{{ $approvedCount }}</span>
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Reviews Table -->
        <div class="mt-8 flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        @if ($reviews->isEmpty())
                            <div class="text-center p-12 bg-white">
                                <x-heroicon-o-chat-bubble-left-ellipsis class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No reviews found</h3>
                                <p class="mt-1 text-sm text-gray-500">There are no {{ request('status', 'pending') }} reviews at this time.</p>
                            </div>
                        @else
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Reviewer</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Review</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Product</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Submitted</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($reviews as $review)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                            <div class="font-medium text-gray-900">{{ $review->reviewer_name }}</div>
                                            <div class="text-gray-500">{{ $review->reviewer_email }}</div>
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500">
                                            <div class="flex items-center">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                @endfor
                                            </div>
                                            <div class="font-medium text-gray-900 mt-1">{{ $review->title }}</div>
                                            <div class="text-gray-500 prose prose-sm max-w-xs line-clamp-3">{{ $review->comment }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <a href="{{ route('products.show', $review->product->slug) }}" target="_blank" class="text-pink-600 hover:text-pink-900 hover:underline">
                                                {{ $review->product->name }}
                                            </a>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $review->created_at->format('M d, Y') }}</td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <div class="flex items-center justify-end space-x-3">
                                                @if(!$review->is_approved)
                                                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" onsubmit="return confirm('Approve this review?');">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="text-green-600 hover:text-green-800" title="Approve">
                                                            <x-heroicon-o-check-circle class="w-5 h-5"/>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.reviews.unapprove', $review) }}" method="POST" onsubmit="return confirm('Unapprove this review?');">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="text-yellow-600 hover:text-yellow-800" title="Unapprove">
                                                            <x-heroicon-o-x-circle class="w-5 h-5"/>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Are you sure you want to PERMANENTLY DELETE this review?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                        <x-heroicon-o-trash class="w-5 h-5"/>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                    @if($reviews->hasPages())
                        <div class="mt-5 px-4 sm:px-0">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>