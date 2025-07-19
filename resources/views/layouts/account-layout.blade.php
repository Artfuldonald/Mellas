{{--views/layouts/account-layout.blade.php --}}
{{-- This layout is used for the account section of the application --}}
{{-- It includes a sidebar for navigation and a main content area for displaying user-specific information --}}
<x-app-layout :title="$title ?? 'My Account'">
    <div class="bg-gray-100 py-8">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

                {{-- Sidebar Navigation --}}
                <aside class="hidden md:block md:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-4 sticky top-24">
                        <nav class="space-y-1">
                            {{-- We will create a Blade component for the links to avoid repetition --}}
                            <x-account.nav-link :href="route('profile.overview')" :active="request()->routeIs('profile.overview')">
                                <x-heroicon-o-user class="w-5 h-5 mr-3"/> My Account
                            </x-account.nav-link>

                            <x-account.nav-link :href="route('profile.orders.index')" :active="request()->routeIs('profile.orders.*')">
                                <x-heroicon-o-shopping-bag class="w-5 h-5 mr-3"/>
                                <span>Orders</span>
                            </x-account.nav-link>
                           
                            <x-account.nav-link :href="route('profile.inbox')" :active="request()->routeIs('profile.inbox')">
                                <x-heroicon-o-inbox class="w-5 h-5 mr-3 "/>
                                <span>Inbox</span>
                            </x-account.nav-link>

                            <x-account.nav-link :href="route('wishlist.index')" :active="request()->routeIs('wishlist.index')">
                                <x-heroicon-o-heart class="w-5 h-5 mr-3"/> Wishlist
                            </x-account.nav-link>

                            <hr class="my-4">

                            <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account Settings</h3>

                            <x-account.nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                                <x-heroicon-o-user-circle class="w-5 h-5 mr-3"/> Profile Details
                            </x-account.nav-link>

                            <x-account.nav-link :href="route('profile.addresses.show')" :active="request()->routeIs('profile.addresses.*')">
                                <x-heroicon-o-book-open class="w-5 h-5 mr-3"/> Address Book                               
                            </x-account.nav-link>

                            <x-account.nav-link href="#" active="">
                                <x-heroicon-o-newspaper class="w-5 h-5 mr-3"/> Newsletter
                            </x-account.nav-link>

                            <hr class="my-4">

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-pink-50 hover:text-pink-600">
                                    <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5 mr-3"/> Logout
                                </button>
                            </form>
                        </nav>
                    </div>
                </aside>

                {{-- Main Content Area --}}
                <main class="md:col-span-3">
                    {{ $slot }}
                </main>

            </div>
        </div>
    </div>
</x-app-layout>