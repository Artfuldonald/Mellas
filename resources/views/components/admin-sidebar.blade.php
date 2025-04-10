<!-- resources/views/components/admin-sidebar.blade.php -->
<aside id="adminSidebar"
       class="fixed {{-- Mobile: Fixed position --}}
              inset-y-0 left-0 z-20 {{-- Mobile: Layering --}}
              flex flex-col {{-- Keep flex column --}}
              h-full {{-- Take full viewport height on mobile --}}
              {{-- Width is controlled by JS/CSS --}}
              bg-pink-600 text-white             
              rounded-r-md {{-- Keep rounded right edge for desktop --}}
              {{-- Mobile animation --}}
              transition-transform duration-300 ease-in-out
              transform -translate-x-full {{-- Mobile: Default hidden --}}
              {{-- Desktop overrides --}}
              md:relative md:inset-y-auto md:left-auto md:z-auto {{-- Desktop: Reset positioning --}}
              md:transform-none {{-- Desktop: Don't translate --}}
              md:h-auto {{-- Desktop: Height based on layout (remove if using floating style) --}}
              md:flex-shrink-0 {{-- Desktop: Prevent shrinking in flex layout --}}
              md:rounded-lg md:my-4 md:ml-4 md:shadow-lg md:border md:border-pink-700/50 {{-- Desktop: Floating styles --}}
              md:transition-all md:duration-300 md:ease-in-out {{-- Desktop: Width transition --}}
             "
>
    <!-- Sidebar header -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-white/20 flex-shrink-0">
        <div class="flex items-center">
            <div class="flex-shrink-0 flex items-center">                
                <span id="sidebarBrandText" class="ml-2 text-sm font-semibold text-white transition-opacity">Admin Panel</span>
            </div>
        </div>
        {{-- Desktop Toggle button - Hidden on mobile, Adjust colors --}}
        <button id="sidebarToggleBtn" class="p-1 rounded-md text-pink-100 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white hidden md:block">
            {{-- Icon for OPEN state (shows when sidebar is open, clicking collapses) --}}
            <x-heroicon-o-chevron-double-left id="sidebarToggleIconOpen" class="h-5 w-5"/>
            {{-- Icon for CLOSED state (shows when sidebar is collapsed, clicking opens) --}}
            <x-heroicon-o-chevron-double-right id="sidebarToggleIconClosed" class="h-5 w-5" style="display: none;"/>
        </button>
    </div>

    <!-- Sidebar content -->
    <div class="flex-1 overflow-y-auto">
        <nav id="adminNav" class="px-2 py-4 space-y-1">
            {{-- Use the cleaned-up nav item/dropdown components --}}
            <x-admin-nav-item route="admin.dashboard" icon="home">Dashboard</x-admin-nav-item>
            <x-admin-nav-dropdown title="Catalog" icon="archive-box" :child-routes="['admin.products', 'admin.categories', 'admin.attributes']">
                <x-admin-nav-item route="admin.products" icon="cube">Products</x-admin-nav-item>
                <x-admin-nav-item route="admin.categories" icon="folder">Categories</x-admin-nav-item>
                <x-admin-nav-item route="admin.attributes" icon="tag">Attributes</x-admin-nav-item>
            </x-admin-nav-dropdown>
            <x-admin-nav-item route="admin.orders" icon="shopping-cart">Orders</x-admin-nav-item>
            <x-admin-nav-item route="admin.users" icon="users">Customers</x-admin-nav-item>
            <x-admin-nav-item route="admin.settings" icon="cog-6-tooth">Settings</x-admin-nav-item>
        </nav>
    </div>

    <!-- User profile section -->
    <div class="border-t border-white/20 p-4 flex-shrink-0">
       <div id="userProfileContainer" class="flex items-center justify-between">
            <div class="flex items-center">
               {{-- Avatar --}}
               <div class="h-8 w-8 rounded-full bg-white/20 flex items-center justify-center text-white">...</div>
                <div id="userProfileText" class="ml-3">
                   <p class="text-sm font-medium text-white">Admin User</p>
                   <p class="text-xs text-pink-200">admin@example.com</p>
                </div>
            </div>
            <button id="userProfileButton" class="text-pink-100 hover:bg-white/10">
                {{-- Settings Icon --}}
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 
                    0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061
                     2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 
                     1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978
                      0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0
                       01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 
                       01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 
                       0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
            </button>
       </div>
    </div>

    {{-- Updated JavaScript Block --}}
   <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('adminSidebar');
            const desktopToggleBtn = document.getElementById('sidebarToggleBtn');
            const mobileToggleBtn = document.getElementById('mobileSidebarToggleBtn');
            const toggleIconOpen = document.getElementById('sidebarToggleIconOpen'); // Desktop open icon
            const toggleIconClosed = document.getElementById('sidebarToggleIconClosed'); // Desktop closed icon
            const brandText = document.getElementById('sidebarBrandText');
            const userProfileContainer = document.getElementById('userProfileContainer');
            const userProfileText = document.getElementById('userProfileText');
            const userProfileButton = document.getElementById('userProfileButton');
            const nav = document.getElementById('adminNav');
            let pageOverlay = null;

            // --- Desktop State ---
            let isDesktopSidebarOpen = localStorage.getItem('desktopSidebarOpen') !== 'false';

            function applyDesktopSidebarState() {
                const isCurrentlyCollapsed = !isDesktopSidebarOpen;

                sidebar.classList.toggle('md:w-64', !isCurrentlyCollapsed);
                sidebar.classList.toggle('md:w-16', isCurrentlyCollapsed);
                sidebar.classList.toggle('sidebar-collapsed', isCurrentlyCollapsed);

                // Ensure icons exist before trying to style them
                if (toggleIconOpen && toggleIconClosed) {
                    toggleIconOpen.style.display = isDesktopSidebarOpen ? 'block' : 'none';
                    toggleIconClosed.style.display = isDesktopSidebarOpen ? 'none' : 'block';
                } else {
                    // Log error if icons aren't found - helps debugging
                    console.error("Desktop toggle icons not found!");
                }


                if(brandText) brandText.style.display = isDesktopSidebarOpen ? 'inline' : 'none';
                if(userProfileText) userProfileText.style.display = isDesktopSidebarOpen ? 'block' : 'none';
                if(userProfileButton) userProfileButton.style.display = isDesktopSidebarOpen ? 'block' : 'none';
                if(userProfileContainer) {
                    userProfileContainer.classList.toggle('justify-center', isCurrentlyCollapsed);
                    userProfileContainer.classList.toggle('justify-between', !isCurrentlyCollapsed);
                }

                localStorage.setItem('desktopSidebarOpen', isDesktopSidebarOpen);
            }

            // --- Mobile State ---
            function toggleMobileSidebar(forceOpen = null) {
                const shouldBeOpen = forceOpen === null ? !sidebar.classList.contains('mobile-open') : forceOpen;
                sidebar.classList.toggle('mobile-open', shouldBeOpen);
                togglePageOverlay(shouldBeOpen);
            }

            function togglePageOverlay(show) {
                 if (show && !pageOverlay) {
                     pageOverlay = document.createElement('div');
                     pageOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-10 md:hidden'; // z-index below sidebar
                     pageOverlay.addEventListener('click', () => toggleMobileSidebar(false)); // Close on click
                     document.body.appendChild(pageOverlay);
                 } else if (!show && pageOverlay) {
                     pageOverlay.remove();
                     pageOverlay = null;
                 }
             }

            // --- Event Listeners ---
            if (desktopToggleBtn) {
                desktopToggleBtn.addEventListener('click', () => {
                    isDesktopSidebarOpen = !isDesktopSidebarOpen;
                    applyDesktopSidebarState();
                });
            }

            if (mobileToggleBtn) {
                mobileToggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleMobileSidebar();
                });
            }

             
             // --- SIMPLIFIED Dropdown Logic ---
             if (nav) {
                nav.addEventListener('click', function(event) {
                    const trigger = event.target.closest('.sidebar-nav-dropdown-trigger');
                    if (!trigger) return;
                    event.preventDefault();

                    const content = trigger.nextElementSibling;
                    const arrow = trigger.querySelector('.sidebar-arrow');

                    if (content && content.classList.contains('sidebar-dropdown-content')) {
                        // Simple class toggle
                        const isOpen = content.classList.toggle('is-open'); // Toggle and get new state
                        if (arrow) {
                           arrow.classList.toggle('is-open', isOpen); // Sync arrow class
                        }
                        // NO MORE max-height logic here
                    }
                });

                // NO MORE initial max-height setting loop needed
             }
             // --- End SIMPLIFIED Dropdown Logic ---


            // Apply initial desktop state on page load
            applyDesktopSidebarState();
        });
    </script>
</aside>