@extends('layouts.app')

@section('title', $product['name'] . ' | ' . $product['brand'])
@section('description', $product['description'])

@section('content')
<div class="bg-white">
    <div class="container mx-auto px-4 py-8">
        <!-- Main Product Section -->
        <section class="mb-12">
            @include('products.partials.product-details', ['product' => $product])
        </section>
        
        <hr class="my-12 border-gray-200">
        
        <!-- Product Reviews Section -->
        <section class="mb-12">
            @include('products.partials.product-reviews', [
                'product' => $product,
                'reviews' => $reviews, // From controller
                'ratingDistribution' => $ratingDistribution // From controller
            ])
        </section>
        
        <hr class="my-12 border-gray-200">
        
        <!-- Related Products Section -->
        <section>
            @include('products.partials.related-products', ['relatedProducts' => $relatedProducts])
        </section>
    </div>
</div>
@endsection