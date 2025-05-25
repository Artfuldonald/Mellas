<x-admin-layout :title="'Edit Customer: ' . $customer->name">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Edit Customer: {{ $customer->name }}</h1>
            <a href="{{ route('admin.customers.show', $customer) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Back to Customer Details
            </a>
        </div>

        {{-- Session Messages --}}
        @include('admin.partials._session_messages')

        {{-- Edit Form Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            {{-- Make sure the route model binding variable name matches controller ($customer) --}}
            <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="px-4 py-5 sm:p-6 space-y-6">
                    {{-- Name --}}
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $customer->name)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $customer->email)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    {{-- Password - DO NOT include password change here for security. Use the "Send Reset Link" functionality. --}}

                     {{-- Admin Status (Display Only) --}}
                     <div class="mt-6 border-t pt-6">
                        <p class="text-sm font-medium text-gray-700">Account Type:</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $customer->is_admin ? 'Administrator' : 'Customer' }}</p>
                        <p class="mt-1 text-xs text-gray-500">Account type cannot be changed here.</p>
                     </div>

                </div>

                {{-- Action Buttons --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end space-x-3">
                     <a href="{{ route('admin.customers.show', $customer) }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancel
                    </a>
                    <x-primary-button>
                         {{ __('Update Customer') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>