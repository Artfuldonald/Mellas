<x-account-layout title="Profile Details">
    <div class="space-y-6">
        {{-- Update Profile Info Card --}}
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        {{-- Update Password Card --}}
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        {{-- Delete Account Card --}}
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6">
                <div class="max-w-xl">
                     @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-account-layout>