{{-- resources/views/index.blade.php --}}
<x-app-layout>
  <x-hero-section />
  <x-category-section /> {{-- Pass $categories if fetched --}}
  
  {{-- Pass the fetched products to the featured product component --}}
  <x-featured-product :products="$featuredProducts" /> 

</x-app-layout>