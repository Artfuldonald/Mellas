<x-app-layout>
    {{-- Hero Section --}}
    <section class="relative bg-gradient-to-br from-pink-50 via-white to-pink-100 overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23f8fafc" fill-opacity="0.4"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-40"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                {{-- Hero Content --}}
                <div class="text-center lg:text-left">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight">
                        Discover Your
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-pink-600">
                            Perfect Style
                        </span>
                    </h1>
                    <p class="mt-6 text-lg sm:text-xl text-gray-600 max-w-2xl">
                        Shop the latest trends with unbeatable prices. From electronics to fashion, find everything you need in one place.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('products.index') }}" 
                           class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-pink-500 to-pink-600 rounded-xl hover:from-pink-600 hover:to-pink-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <x-heroicon-o-shopping-bag class="w-5 h-5 mr-2" />
                            Shop Now
                        </a>
                        <a href="#categories" 
                           class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-pink-600 bg-white border-2 border-pink-200 rounded-xl hover:bg-pink-50 hover:border-pink-300 transition-all duration-200">
                            <x-heroicon-o-squares-2x2 class="w-5 h-5 mr-2" />
                            Browse Categories
                        </a>
                    </div>
                </div>
                
                {{-- Hero Image/Animation --}}
                <div class="relative">
                    <div class="relative z-10 bg-white rounded-3xl shadow-2xl p-8 transform rotate-3 hover:rotate-0 transition-transform duration-500">
                        <div class="aspect-square bg-gradient-to-br from-pink-100 to-pink-200 rounded-2xl flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-24 h-24 bg-pink-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <x-heroicon-o-gift class="w-12 h-12 text-white" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-800">Special Offers</h3>
                                <p class="text-gray-600 mt-2">Up to 50% off</p>
                            </div>
                        </div>
                    </div>
                    {{-- Floating Elements --}}
                    <div class="absolute -top-4 -right-4 w-20 h-20 bg-pink-400 rounded-full opacity-20 animate-pulse"></div>
                    <div class="absolute -bottom-6 -left-6 w-16 h-16 bg-pink-300 rounded-full opacity-30 animate-bounce"></div>
                </div>
            </div>
        </div>
    </section>

  

    {{-- Categories Section --}}
    @if(isset($navCategories) && $navCategories->isNotEmpty())
    <section id="categories" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Shop by 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-pink-600">Category</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Discover our wide range of products across different categories
                </p>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @foreach($navCategories->take(12) as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                       class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100 hover:border-pink-200">
                        <div class="text-center">
                            @if($category->image)
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full overflow-hidden bg-pink-50 flex items-center justify-center">
                                    <img src="{{ Storage::url($category->image) }}" 
                                         alt="{{ $category->name }}" 
                                         class="w-12 h-12 object-contain">
                                </div>
                            @else
                                <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-pink-100 to-pink-200 rounded-full flex items-center justify-center group-hover:from-pink-200 group-hover:to-pink-300 transition-all">
                                    <x-heroicon-o-tag class="w-8 h-8 text-pink-600"/>
                                </div>
                            @endif
                            <h3 class="font-semibold text-gray-900 group-hover:text-pink-600 transition-colors text-sm">
                                {{ $category->name }}
                            </h3>
                        </div>
                    </a>
                @endforeach
            </div>
            
            @if($navCategories->count() > 12)
                <div class="text-center mt-8">
                    <a href="{{ route('products.index') }}" 
                       class="inline-flex items-center px-6 py-3 text-pink-600 font-semibold hover:text-pink-700 transition-colors">
                        View All Categories
                        <x-heroicon-o-arrow-right class="w-4 h-4 ml-2" />
                    </a>
                </div>
            @endif
        </div>
    </section>
    @endif    

    

    @pushOnce('scripts')
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('section').forEach(section => {
            observer.observe(section);
        });
    </script>

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
    </style>
    @endPushOnce
</x-app-layout>