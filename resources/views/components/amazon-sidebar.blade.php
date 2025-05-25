{{-- resources/views/components/amazon-sidebar.blade.php --}}
<div 
    x-data="sidebarNavigation()"
    x-show="isOpen"
    @keydown.escape.window="close()"
    @toggle-sidebar.window="isOpen = !isOpen"
    class="fixed inset-0 z-50 bg-black/50"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
>
    <div 
        class="fixed left-0 top-0 h-full w-80 max-w-[80vw] bg-white shadow-lg transition-transform duration-300"
        :class="isOpen ? 'transform-none' : '-translate-x-full'"
        @click.outside="close()"
    >
        {{-- Sidebar Header --}}
        <div class="bg-pink-600 text-white p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    {{-- Back button (hidden on main view) --}}
                    <button 
                        x-show="currentView !== 'main'"
                        @click="navigateBack()"
                        class="mr-2 rounded-full p-1 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-white"
                    >
                        <x-heroicon-o-arrow-left class="h-5 w-5" />
                        <span class="sr-only">Back</span>
                    </button>
                    {{-- Current view title --}}
                    <span class="text-xl font-bold" x-text="currentTitle"></span>
                </div>
                <button 
                    @click="close()"
                    class="rounded-full p-1 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-white"
                >
                    <x-heroicon-o-x-mark class="h-6 w-6" />
                    <span class="sr-only">Close sidebar</span>
                </button>
            </div>
            {{-- Parent category (shown in subcategory views) --}}
            <div 
                x-show="parentTitle" 
                x-text="parentTitle"
                class="text-sm mt-1 opacity-80"
            ></div>
        </div>
        
        {{-- Sidebar Content --}}
        <div class="overflow-y-auto h-[calc(100%-64px)]">
            {{-- Main View (Categories) --}}
            <div x-show="currentView === 'main'">
                {{-- Digital Content & Devices --}}
                <div class="p-4 border-b">
                    <h3 class="text-base font-bold mb-2">Digital Content & Devices</h3>
                    <ul>
                        <li>
                            <button 
                                @click="navigateTo('music', 'Amazon Music', 'Digital Content & Devices')"
                                class="w-full flex items-center justify-between py-2 hover:text-pink-600 text-left"
                            >
                                Amazon Music <x-heroicon-o-chevron-right class="h-4 w-4" />
                            </button>
                        </li>
                        <li>
                            <button 
                                @click="navigateTo('kindle', 'Kindle E-readers & Books', 'Digital Content & Devices')"
                                class="w-full flex items-center justify-between py-2 hover:text-pink-600 text-left"
                            >
                                Kindle E-readers & Books <x-heroicon-o-chevron-right class="h-4 w-4" />
                            </button>
                        </li>
                        <li>
                            <button 
                                @click="navigateTo('appstore', 'Amazon Appstore', 'Digital Content & Devices')"
                                class="w-full flex items-center justify-between py-2 hover:text-pink-600 text-left"
                            >
                                Amazon Appstore <x-heroicon-o-chevron-right class="h-4 w-4" />
                            </button>
                        </li>
                    </ul>
                </div>
                
                {{-- Shop By Department --}}
                <div class="p-4 border-b">
                    <h3 class="text-base font-bold mb-2">Shop By Department</h3>
                    <ul>
                        <li>
                            <button 
                                @click="navigateTo('electronics', 'Electronics', 'Shop By Department')"
                                class="w-full flex items-center justify-between py-2 hover:text-pink-600 text-left"
                            >
                                Electronics <x-heroicon-o-chevron-right class="h-4 w-4" />
                            </button>
                        </li>
                        <li>
                            <button 
                                @click="navigateTo('computers', 'Computers', 'Shop By Department')"
                                class="w-full flex items-center justify-between py-2 hover:text-pink-600 text-left"
                            >
                                Computers <x-heroicon-o-chevron-right class="h-4 w-4" />
                            </button>
                        </li>
                        <li>
                            <button 
                                @click="navigateTo('smarthome', 'Smart Home', 'Shop By Department')"
                                class="w-full flex items-center justify-between py-2 hover:text-pink-600 text-left"
                            >
                                Smart Home <x-heroicon-o-chevron-right class="h-4 w-4" />
                            </button>
                        </li>
                        <li><a href="#" class="flex items-center justify-between py-2 hover:text-pink-600">
                            Arts & Crafts <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </a></li>
                        <li><a href="#" class="flex items-center justify-between py-2 hover:text-pink-600">
                            Automotive <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </a></li>
                        <li><a href="#" class="flex items-center justify-between py-2 hover:text-pink-600">
                            Baby <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </a></li>
                        <li><a href="#" class="flex items-center justify-between py-2 hover:text-pink-600">
                            Beauty and Personal Care <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </a></li>
                    </ul>
                </div>
                
                {{-- Programs & Features --}}
                <div class="p-4 border-b">
                    <h3 class="text-base font-bold mb-2">Programs & Features</h3>
                    <ul>
                        <li><a href="#" class="flex items-center justify-between py-2 hover:text-pink-600">
                            Gift Cards <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </a></li>
                        <li><a href="#" class="flex items-center justify-between py-2 hover:text-pink-600">
                            Shop By Interest <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </a></li>
                        <li><a href="#" class="flex items-center justify-between py-2 hover:text-pink-600">
                            Amazon Live <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </a></li>
                    </ul>
                </div>
                
                {{-- Help & Settings --}}
                <div class="p-4">
                    <h3 class="text-base font-bold mb-2">Help & Settings</h3>
                    <ul>
                        <li><a href="#" class="flex items-center justify-between py-2 hover:text-pink-600">
                            Your Account <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </a></li>
                        <li><a href="#" class="flex items-center justify-between py-2 hover:text-pink-600">
                            Customer Service <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </a></li>
                        <li><a href="#" class="flex items-center justify-between py-2 hover:text-pink-600">
                            Sign Out <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </a></li>
                    </ul>
                </div>
            </div>
            
            {{-- Subcategory Views --}}
            <div x-show="currentView === 'music'" class="p-4">
                <ul>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Music Unlimited <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Free Streaming Music <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Podcasts <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Open Web Player <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Download the App <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                </ul>
            </div>
            
            <div x-show="currentView === 'kindle'" class="p-4">
                <ul>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Kindle E-readers <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Kindle Unlimited <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Prime Reading <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Kindle Vella <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                </ul>
            </div>
            
            <div x-show="currentView === 'appstore'" class="p-4">
                <ul>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        All Apps and Games <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Games <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Amazon Coins <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Download Amazon Appstore <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                </ul>
            </div>
            
            <div x-show="currentView === 'electronics'" class="p-4">
                <ul>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Accessories & Supplies <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Camera & Photo <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Cell Phones & Accessories <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Computers & Accessories <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Headphones <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                </ul>
            </div>
            
            <div x-show="currentView === 'computers'" class="p-4">
                <ul>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Computer Accessories <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Computer Components <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Desktops <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Laptops <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Monitors <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                </ul>
            </div>
            
            <div x-show="currentView === 'smarthome'" class="p-4">
                <ul>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Amazon Smart Home <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Smart Home Lighting <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Smart Locks and Entry <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                    <li><a href="#" class="flex items-center justify-between py-3 hover:text-pink-600 border-b">
                        Security Cameras and Systems <x-heroicon-o-chevron-right class="h-4 w-4" />
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</div>