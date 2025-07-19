<section>
    <header class="pb-4 border-b">
        <h2 class="text-lg font-semibold text-gray-900">
            Profile Information
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Update your account's profile information and email address.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('patch')

        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input id="name" name="name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input id="email" name="email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 text-sm text-gray-800">
                    <p>
                        Your email address is unverified.
                        <button form="send-verification" class="underline text-pink-600 hover:text-pink-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                            Click here to re-send the verification email.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-pink-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-pink-700 active:bg-pink-800 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Save
            </button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">
                    Saved.
                </p>
            @endif
        </div>
    </form>
</section>