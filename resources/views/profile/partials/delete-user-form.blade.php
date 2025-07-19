<section class="p-4 border-l-4 border-red-400 bg-red-50">
    <header>
        <h2 class="text-lg font-semibold text-red-900">
            Delete Account
        </h2>
        <p class="mt-1 text-sm text-red-700">
            Once your account is deleted, all of its resources and data will be permanently deleted. This action cannot be undone.
        </p>
    </header>

    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="mt-4 inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
        Delete Account
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                Are you sure you want to delete your account?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
            </p>

            <div class="mt-6">
                <label for="password_delete" class="sr-only">Password</label>
                <input id="password_delete" name="password" type="password" class="mt-1 block w-3/4 border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="Password">
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" x-on:click="$dispatch('close')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancel
                </button>
                <button type="submit" class="ml-3 inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Delete Account
                </button>
            </div>
        </form>
    </x-modal>
</section>