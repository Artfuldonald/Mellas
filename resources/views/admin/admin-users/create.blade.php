{{-- resources/views/admin/admin-users/create.blade.php --}}
<x-admin-layout title="Add Administrator">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Add New Administrator</h1>
            <a href="{{ route('admin.admin-users.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Back to Administrators
            </a>
        </div>

        {{-- Session Messages --}}
        @include('admin.partials._session_messages')

        {{-- Create Form Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <form action="{{ route('admin.admin-users.store') }}" method="POST">
                @csrf

                <div class="px-4 py-5 sm:p-6 space-y-6">
                    {{-- Name --}}
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                     {{-- Password --}}
                    <div>
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('password')" />
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end space-x-3">
                     <a href="{{ route('admin.admin-users.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancel
                    </a>
                    <x-primary-button>
                         {{ __('Create Administrator') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>