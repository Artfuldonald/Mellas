<x-account-layout title="Account Overview">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">Account Overview</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Account Details --}}
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold text-gray-800">ACCOUNT DETAILS</h3>
                <p class="mt-2 text-gray-600">{{ $user->name }}</p>
                <p class="text-gray-500 text-sm">{{ $user->email }}</p>
                <a href="{{ route('profile.edit') }}" class="mt-4 inline-block text-pink-600 hover:underline text-sm font-medium">Change Password</a>
            </div>

            {{-- Address Book --}}
            <div class="border rounded-lg p-4">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">ADDRESS BOOK</h3>  
                        <a href="{{ route('profile.addresses.show') }}" class="text-pink-600 hover:underline text-sm font-medium">
                            Manage
                        </a>                    
                        <a href="{{ $defaultAddress ? route('profile.addresses.edit', $defaultAddress->id) : route('profile.addresses.show') }}" class="text-pink-600 hover:text-pink-800 text-sm" title="Edit Default Address">
                            <x-heroicon-o-pencil class="w-4 h-4"/>
                        </a>
                    </div>                
                @if ($defaultAddress)
                    <p class="mt-2 text-sm text-gray-500">Your default shipping address:</p>
                    <div class="mt-1 text-gray-600 leading-relaxed text-sm">                       
                        <p class="font-semibold">{{ $defaultAddress->first_name }} {{ $defaultAddress->last_name }}</p>
                        <p>{{ $defaultAddress->gps_address }}</p>
                        <p>{{ $defaultAddress->city }}, {{ $defaultAddress->state }}</p>
                        <p>{{ $defaultAddress->country }}</p>
                        <p>{{ $defaultAddress->phone }}</p>
                    </div>
                @else                   
                    <p class="mt-2 text-sm text-gray-500">You have no saved addresses.</p>
                    <a href="{{ route('profile.addresses.create') }}" class="mt-4 inline-block text-pink-600 hover:underline text-sm font-medium">
                        Add an address
                    </a>
                @endif
            </div>

            {{-- Store Credit --}}
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold text-gray-800">MELLA'S STORE CREDIT</h3>
                <p class="mt-2 text-lg font-bold text-gray-700">GH₵ 0.00</p>
            </div>

            {{-- Newsletter --}}
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold text-gray-800">NEWSLETTER PREFERENCES</h3>
                <p class="mt-2 text-sm text-gray-600">You are currently subscribed to our newsletter.</p>
                <a href="#" class="mt-4 inline-block text-pink-600 hover:underline text-sm font-medium">Edit Newsletter Preferences</a>
            </div>
        </div>
    </div>
</x-account-layout>