<!-- resources/views/components/admin-sidebar.blade.php -->
<aside id="adminSidebar"
       class="fixed inset-y-0 left-0 z-20 flex flex-col h-full w-64
              bg-pink-600 text-white rounded-r-md
              transition-transform duration-300 ease-in-out transform -translate-x-full
              md:relative md:inset-y-auto md:left-auto md:z-auto md:transform-none md:h-auto
              md:flex-shrink-0 md:rounded-lg md:my-4 md:ml-4 md:shadow-lg md:border md:border-pink-700/50
              md:transition-all md:duration-300 md:ease-in-out
             "
>
    <!-- Sidebar header -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-white/20 flex-shrink-0">
        <div class="flex items-center overflow-hidden">
            <a href="{{ route('admin.dashboard') }}" class="flex-shrink-0 flex items-center group">
                 <span class="flex items-center justify-center h-8 w-8 rounded-full bg-white/10 group-hover:bg-white/20 transition-colors">
                      {{-- Use dynamic component for the header icon --}}
                      <x-dynamic-component component="heroicon-o-swatch" class="h-5 w-5 text-pink-100"/>
                 </span>
                <span id="sidebarBrandText" class="ml-2 text-sm font-semibold text-white whitespace-nowrap transition-opacity duration-300">Admin Panel</span>
            </a>
        </div>
        <button id="sidebarToggleBtn" class="p-1 rounded-md text-pink-100 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white hidden md:block">
            <x-heroicon-o-chevron-double-left id="sidebarToggleIconOpen" class="h-5 w-5"/>
            <x-heroicon-o-chevron-double-right id="sidebarToggleIconClosed" class="h-5 w-5" style="display: none;"/>
        </button>
    </div>

    <!-- Sidebar Navigation -->
    <div class="flex-1 overflow-y-auto">
        <nav id="adminNav" class="px-2 py-4 space-y-1">
            {{-- Pass SHORT icon names --}}
            <x-admin-nav-item route="admin.dashboard" icon="home" class="sidebar-nav-item" data-route-name="admin.dashboard">Dashboard</x-admin-nav-item>

            <x-admin-nav-dropdown title="Catalog" icon="archive-box" :child-routes="json_encode(['admin.products.*', 'admin.categories.*', 'admin.attributes.*'])">
                <x-admin-nav-item route="admin.products.index" icon="cube">Products</x-admin-nav-item>
                <x-admin-nav-item route="admin.categories.index" icon="folder">Categories</x-admin-nav-item>
                <x-admin-nav-item route="admin.attributes.index" icon="tag">Attributes</x-admin-nav-item>
                <x-admin-nav-item route="admin.brands.index" icon="building-storefront">Brands</x-admin-nav-item>
            </x-admin-nav-dropdown>

            <x-admin-nav-item route="admin.orders.index" icon="shopping-cart" class="sidebar-nav-item" data-route-name="admin.orders.index">Orders</x-admin-nav-item>
            <x-admin-nav-item route="admin.customers.index" icon="users" class="sidebar-nav-item" data-route-name="admin.customers.index">Customers</x-admin-nav-item> 
            <x-admin-nav-item route="admin.reviews.index" icon="chat-bubble-left-ellipsis" class="sidebar-nav-item" data-route-name="admin.reviews.index">Reviews</x-admin-nav-item>

            <x-admin-nav-dropdown title="Settings" icon="cog-6-tooth" :child-routes="json_encode(['admin.admin-users.*', 'admin.shipping-zones.*', 'admin.tax-rates.*'])"> {{-- Add tax pattern --}}
                <x-admin-nav-item route="admin.shipping-zones.index" icon="truck">Shipping</x-admin-nav-item>
                <x-admin-nav-item route="admin.tax-rates.index" icon="banknotes">Taxes</x-admin-nav-item> 
                <x-admin-nav-item route="admin.admin-users.index" icon="shield-check">Administrators</x-admin-nav-item>
                <x-admin-nav-item route="admin.settings.edit" icon="cog-6-tooth" class="sidebar-nav-item" data-route-name="admin.settings.edit">Store Settings</x-admin-nav-item>
            </x-admin-nav-dropdown>

            <x-admin-nav-item route="admin.discounts.index" icon="receipt-percent" class="sidebar-nav-item" data-route-name="admin.discounts.index">Discounts</x-admin-nav-item>
        </nav>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-3" id="logoutFormContainer"
    onsubmit="return confirm('Are you sure you want to log out?');">
    @csrf
    <button type="submit" class="w-full text-left text-base font-medium text-pink-100 hover:text-white hover:bg-pink-700
                 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-75           px-3 py-2.5
                 rounded-lg
                 flex items-center space-x-3
                 transition-colors duration-150 ease-in-out
                 sidebar-text-element">
      <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5 flex-shrink-0" />
      <span class="flex-1 min-w-0 sidebar-text">Logout</span>
    </button>
    </form>

    <!-- User profile section -->
    {{-- ... (user profile section remains the same) ... --}}
     <div class="border-t border-white/20 p-4 flex-shrink-0">
       <div id="userProfileContainer" class="flex items-center justify-between">
            <a href="{{ route('profile.edit') }}" class="flex items-center overflow-hidden group flex-1 min-w-0">
               <div class="h-8 w-8 rounded-full bg-white/20 flex-shrink-0 flex items-center justify-center text-white group-hover:bg-white/20 transition-colors">
                   <x-heroicon-o-user class="h-5 w-5"/>
               </div>
                <div id="userProfileText" class="ml-3 flex-1 min-w-0 transition-opacity duration-300">
                   <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Admin User' }}</p>
                   <p class="text-xs text-pink-200 truncate">{{ Auth::user()->email ?? 'admin@example.com' }}</p>
                </div>
            </a>
            <a href="{{ route('profile.edit') }}" id="userProfileButton" class="p-1 rounded-md text-pink-100 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white transition-opacity duration-300 flex-shrink-0">
                <x-heroicon-o-cog-6-tooth class="h-5 w-5" />
            </a>
       </div>
       
    </div>

    {{-- JavaScript Block (remains the same as the previous combined version) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- Elements ---
            const sidebar = document.getElementById('adminSidebar');
            const desktopToggleBtn = document.getElementById('sidebarToggleBtn');
            const mobileToggleBtn = document.getElementById('mobileSidebarToggleBtn');
            const toggleIconOpen = document.getElementById('sidebarToggleIconOpen');
            const toggleIconClosed = document.getElementById('sidebarToggleIconClosed');
            const brandText = document.getElementById('sidebarBrandText');
            const userProfileContainer = document.getElementById('userProfileContainer');
            const userProfileText = document.getElementById('userProfileText');
            const userProfileButton = document.getElementById('userProfileButton');
            const logoutFormContainer = document.getElementById('logoutFormContainer');
            const nav = document.getElementById('adminNav');
            let pageOverlay = null;

            // --- Get Current Route Name ---
            const currentRouteName = document.body.dataset.currentRoute || '';

            // --- Desktop State & Functions ---
            let isDesktopSidebarOpen = localStorage.getItem('desktopSidebarOpen') !== 'false';

            function applyDesktopSidebarState() {
                if (!sidebar) return;
                const isCurrentlyCollapsed = !isDesktopSidebarOpen;

                // Apply main sidebar classes
                sidebar.classList.toggle('md:w-64', !isCurrentlyCollapsed);
                sidebar.classList.toggle('md:w-16', isCurrentlyCollapsed);
                sidebar.classList.toggle('sidebar-collapsed', isCurrentlyCollapsed); // Used as a flag by JS

                // Toggle desktop button icons
                if (toggleIconOpen && toggleIconClosed) {
                    toggleIconOpen.style.display = isDesktopSidebarOpen ? 'block' : 'none';
                    toggleIconClosed.style.display = isDesktopSidebarOpen ? 'none' : 'block';
                }

                // Toggle visibility/opacity of text elements
                const elementsToToggle = [brandText, userProfileText, userProfileButton, logoutFormContainer];
                elementsToToggle.forEach(el => {
                    if (el) {
                        el.style.visibility = isDesktopSidebarOpen ? 'visible' : 'hidden';
                        el.style.opacity = isDesktopSidebarOpen ? '1' : '0';
                        if (!el.classList.contains('transition-opacity')) {
                            el.classList.add('transition-opacity', 'duration-300');
                        }
                    }
                });

                // Adjust user profile container alignment
                if(userProfileContainer) {
                    userProfileContainer.classList.toggle('justify-center', isCurrentlyCollapsed);
                    userProfileContainer.classList.toggle('justify-between', !isCurrentlyCollapsed);
                }

                // Adjust nav items styling and tooltips
                if (nav) {
                    const navItems = nav.querySelectorAll('.sidebar-nav-item, .sidebar-nav-dropdown-trigger');
                    navItems.forEach(item => {
                        const textSpan = item.querySelector('.sidebar-text');
                        const icon = item.querySelector('.sidebar-icon');

                        // --- *** REVISED ALIGNMENT LOGIC *** ---
                        // Always ensure justify-start is present by default
                        item.classList.add('justify-start');
                        // ONLY add justify-center if collapsed, remove justify-start
                        if (isCurrentlyCollapsed) {
                            item.classList.remove('justify-start');
                            item.classList.add('justify-center');
                        } else {
                            // Ensure justify-center is removed when expanded
                            item.classList.remove('justify-center');
                        }
                        // --- *** END REVISED ALIGNMENT LOGIC *** ---


                        if (icon) {
                            // Apply margin only when expanded
                            icon.classList.toggle('mr-3', !isCurrentlyCollapsed);
                            // Remove centering if needed (though justify-center on parent should handle it)
                            // icon.classList.toggle('mx-auto', isCurrentlyCollapsed);
                        }
                        if (textSpan) {
                            textSpan.classList.toggle('hidden', isCurrentlyCollapsed);
                            if (isCurrentlyCollapsed) { item.setAttribute('title', textSpan.textContent.trim()); }
                            else { item.removeAttribute('title'); }
                        }

                        // Close dropdowns when collapsing sidebar
                        if (isCurrentlyCollapsed) {
                            const dropdownContent = item.nextElementSibling;
                            if (dropdownContent && dropdownContent.classList.contains('sidebar-dropdown-content')) {
                                const arrow = item.querySelector('.sidebar-arrow');
                                dropdownContent.classList.remove('is-open');
                                dropdownContent.style.maxHeight = '0px';
                                if(arrow) arrow.classList.remove('rotate-90', 'is-open');
                            }
                        } else {
                            // Re-apply stored open state when expanding sidebar
                            const dropdownTrigger = item.closest('.admin-nav-dropdown-container')?.querySelector('[data-dropdown-trigger]');
                            if(dropdownTrigger){ // Check if it's part of a dropdown
                                const dropdownContent = dropdownTrigger.nextElementSibling;
                                if (dropdownContent && dropdownContent.classList.contains('sidebar-dropdown-content')) {
                                    const dropdownId = triggerToDropdownId(dropdownTrigger);
                                    const storedState = dropdownId ? localStorage.getItem(dropdownId) === 'true' : false;
                                    if (storedState) {
                                        const arrow = dropdownTrigger.querySelector('.sidebar-arrow');
                                        dropdownContent.classList.add('is-open');
                                        // Recalculate scrollHeight in case content changed
                                        dropdownContent.style.maxHeight = dropdownContent.scrollHeight + 'px';
                                        dropdownTrigger.setAttribute('aria-expanded', 'true');
                                        if(arrow) arrow.classList.add('rotate-90', 'is-open');
                                    }
                                }
                            }
                        }
                    });
                }
                // Save the overall sidebar state
                localStorage.setItem('desktopSidebarOpen', isDesktopSidebarOpen);
            }

            // --- Mobile Functions ---
            function toggleMobileSidebar(forceOpen = null) {
                // ... (This function remains the same) ...
                 if (!sidebar) return;
                const shouldBeOpen = forceOpen === null ? !sidebar.classList.contains('mobile-open') : forceOpen;
                sidebar.classList.toggle('mobile-open', shouldBeOpen);
                sidebar.classList.toggle('-translate-x-full', !shouldBeOpen);
                sidebar.classList.toggle('translate-x-0', shouldBeOpen);
                togglePageOverlay(shouldBeOpen);
                document.body.style.overflow = shouldBeOpen ? 'hidden' : '';
            }

            function togglePageOverlay(show) {
                // ... (This function remains the same) ...
                 if (show && !pageOverlay) {
                     pageOverlay = document.createElement('div');
                     pageOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-10 md:hidden';
                     pageOverlay.addEventListener('click', () => toggleMobileSidebar(false));
                     document.body.appendChild(pageOverlay);
                 } else if (!show && pageOverlay) {
                     pageOverlay.remove();
                     pageOverlay = null;
                 }
             }

            // --- Helper to generate a unique ID for storing dropdown state ---
            function triggerToDropdownId(triggerElement) {
                // Use the dropdown title or another unique attribute if available
                const title = triggerElement.getAttribute('title');
                // Sanitize title to create a valid key (remove spaces, special chars)
                const safeKey = title ? title.replace(/[^a-zA-Z0-9]/g, '-') : null;
                // Fallback if title is missing or empty
                const fallbackId = triggerElement.getAttribute('data-dropdown-trigger-id'); // Add this attribute if needed

                if (safeKey) {
                    return `sidebarDropdownState-${safeKey}`;
                } else if (fallbackId) {
                     return `sidebarDropdownState-${fallbackId}`;
                } else {
                    // As a last resort, maybe use index, but this is less reliable if order changes
                    console.warn("Dropdown trigger needs a title or data-dropdown-trigger-id for persistent state.", triggerElement);
                    return null;
                }
            }


            // --- Initialize Active States & Dropdown Logic ---
            function initializeNavItems() {
                if (!nav) return;

                // 1. Handle Dropdowns
                document.querySelectorAll('.admin-nav-dropdown-container').forEach((container, index) => { // Added index for fallback ID
                    const trigger = container.querySelector('[data-dropdown-trigger]');
                    const content = container.querySelector('[data-dropdown-content]');
                    const arrow = trigger?.querySelector('.sidebar-arrow');
                    const icon = trigger?.querySelector('.sidebar-icon');

                    if (!trigger || !content) return;

                    // --- Generate ID for localStorage ---
                    // Add a data-attribute if title isn't reliable enough
                    // trigger.setAttribute('data-dropdown-trigger-id', `dropdown-${index}`); // Example if needed
                    const dropdownId = triggerToDropdownId(trigger);

                    // --- Check Active State (for styling the trigger only) ---
                    const childRoutesJson = trigger.getAttribute('data-child-routes');
                    let childRoutes = [];
                    try { childRoutes = JSON.parse(childRoutesJson || '[]'); } catch (e) { console.error("Invalid JSON:", childRoutesJson, e); return; }

                    let isActive = false;
                    if (currentRouteName) {
                        for (const pattern of childRoutes) {
                            if (pattern) {
                                const regexPattern = '^' + pattern.replace('.', '\\.').replace('*', '.*') + '$';
                                try { if (new RegExp(regexPattern).test(currentRouteName)) { isActive = true; break; } }
                                catch (regexError) { console.error("Invalid regex:", regexPattern, regexError); }
                            }
                        }
                    }

                    // Apply active styles to TRIGGER only
                    if (isActive) {
                        trigger.classList.remove('text-pink-100', 'hover:bg-white/5', 'hover:text-white');
                        trigger.classList.add('bg-white/10', 'text-white');
                        if (icon) { icon.classList.remove('text-pink-200', 'group-hover:text-white'); icon.classList.add('text-white'); }
                        // Don't style arrow based on active route, only on open state
                    }

                    // --- Apply Initial Open/Closed State from localStorage ---
                    let isInitiallyOpen = false;
                    if (dropdownId) { // Only proceed if we have an ID
                        isInitiallyOpen = localStorage.getItem(dropdownId) === 'true';
                    }

                    if (isInitiallyOpen) {
                        content.classList.add('is-open');
                        content.style.maxHeight = content.scrollHeight + 'px';
                        trigger.setAttribute('aria-expanded', 'true');
                        if (arrow) { arrow.classList.add('rotate-90', 'is-open'); }
                    } else {
                        content.classList.remove('is-open');
                        content.style.maxHeight = '0px';
                        trigger.setAttribute('aria-expanded', 'false');
                        if (arrow) { arrow.classList.remove('rotate-90', 'is-open'); }
                    }


                    // Add Click Listener for Toggling & Saving State
                    trigger.addEventListener('click', (e) => {
                        if (sidebar?.classList.contains('sidebar-collapsed') && window.innerWidth >= 768) return;

                        const isOpen = content.classList.toggle('is-open');
                        content.style.maxHeight = isOpen ? content.scrollHeight + 'px' : '0px';
                        trigger.setAttribute('aria-expanded', isOpen);
                        if (arrow) { arrow.classList.toggle('rotate-90', isOpen); arrow.classList.toggle('is-open', isOpen); }

                        // --- Save state to localStorage ---
                        if (dropdownId) {
                            localStorage.setItem(dropdownId, isOpen);
                        }
                        // ---------------------------------
                    });
                }); // End dropdown loop

                // 2. Handle Standalone Items Active State (remains the same)
                document.querySelectorAll('.sidebar-nav-item').forEach(item => {
                    const routeName = item.getAttribute('data-route-name');
                    const icon = item.querySelector('.sidebar-icon');
                    if (routeName && currentRouteName && currentRouteName === routeName) {
                        item.classList.remove('text-pink-100', 'hover:bg-white/5', 'hover:text-white');
                        item.classList.add('bg-white/10', 'text-white');
                         if (icon) { icon.classList.remove('text-pink-200', 'group-hover:text-white'); icon.classList.add('text-white'); }
                    }
                }); // End standalone item loop
            } // End initializeNavItems

            // --- Attach Event Listeners for Toggles ---
            if (desktopToggleBtn) { desktopToggleBtn.addEventListener('click', () => { isDesktopSidebarOpen = !isDesktopSidebarOpen; applyDesktopSidebarState(); }); }
            if (mobileToggleBtn) { mobileToggleBtn.addEventListener('click', (e) => { e.stopPropagation(); toggleMobileSidebar(); }); }

            // --- Initial Setup ---
            initializeNavItems(); // Set active states, initial open states, and attach listeners
            applyDesktopSidebarState(); // Apply initial desktop collapsed/expanded state

        }); // End DOMContentLoaded
    </script>

</aside>