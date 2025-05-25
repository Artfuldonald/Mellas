<button {{ $attributes->merge([
    'type' => 'submit', // Default type is submit
    'class' => 'inline-flex items-center px-4 py-2 bg-pink-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-25'
    ]) }}>
    {{ $slot }} {{-- Button text goes here --}}
</button>