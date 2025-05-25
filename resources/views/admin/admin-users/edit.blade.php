{{-- resources/views/admin/admin-users/edit.blade.php --}}
<x-admin-layout :title="'Edit Administrator: ' . $admin_user->name">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Edit Administrator: {{ $admin_user->name }}</h1>
            <a href="{{ route('admin.admin-users.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Back to Administrators
            </a>
        </div>

        {{-- Session Messages --}}
        @include('admin.partials._session_messages')

        {{-- Edit Form Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <form action="{{ route('admin.admin-users.update', $admin_user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="px-4 py-5 sm:p-6 space-y-6">
                    {{-- Name --}}
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $admin_user->name)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $admin_user->email)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                     {{-- Password (Optional Update) --}}
                    <div>
                        <x-input-label for="password" :value="__('New Password (Optional)')" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        <p class="mt-1 text-xs text-gray-500">Leave blank to keep the current password.</p>
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                    </div>

                    {{-- Administrator Status --}}
                    <fieldset class="mt-4 border-t border-gray-200 pt-6">
                        <legend class="text-sm font-medium text-gray-900">Administrator Status</legend>
                         <p class="mt-1 text-xs text-gray-500">Carefully consider demoting administrators.</p>
                         @if(Auth::id() === $admin_user->id)
                             <p class="mt-2 text-sm text-yellow-700 bg-yellow-50 p-3 rounded-md border border-yellow-200">You cannot change your own administrator status.</p>
                         @endif
                         @if(Auth::id() !== $admin_user->id && \App\Models\User::isAdmin()->count() <= 1 && $admin_user->is_admin)
                              <p class="mt-2 text-sm text-red-700 bg-red-50 p-3 rounded-md border border-red-200">Cannot demote the last remaining administrator.</p>
                         @endif

                        <div class="mt-4 space-y-4">
                            <div class="flex items-center">
                                <input id="is_admin_true" name="is_admin" type="radio" value="1"
                                       {{ old('is_admin', $admin_user->is_admin) ? 'checked' : '' }}
                                       {{ Auth::id() === $admin_user->id ? 'disabled' : '' }} {{-- Disable if editing self --}}
                                       class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50">
                                <label for="is_admin_true" class="ml-3 block text-sm font-medium text-gray-700">
                                    Is Administrator
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="is_admin_false" name="is_admin" type="radio" value="0"
                                       {{ !old('is_admin', $admin_user->is_admin) ? 'checked' : '' }}
                                       {{ Auth::id() === $admin_user->id ? 'disabled' : '' }} {{-- Disable if editing self --}}
                                       {{ (Auth::id() !== $admin_user->id && \App\Models\User::isAdmin()->count() <= 1 && $admin_user->is_admin) ? 'disabled' : '' }} {{-- Disable if last admin --}}
                                       class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50">
                                <label for="is_admin_false" class="ml-3 block text-sm font-medium text-gray-700">
                                    Regular User (Demote)
                                </label>
                            </div>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('is_admin')" />
                    </fieldset>

                </div>

                {{-- Action Buttons --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end space-x-3">
                     <a href="{{ route('admin.admin-users.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancel
                    </a>
                    <x-primary-button>
                         {{ __('Update Administrator') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>