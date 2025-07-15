<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $topSellingProducts = Product::forCard()->limit(10)->inRandomOrder()->get(); 
        $allProducts = Product::forCard()->limit(10)->latest()->get();

        $electronicsCategory = Category::where('slug', 'electronics')->first();
        $electronicsProducts = $electronicsCategory 
            ? $electronicsCategory->products()->forCard()->limit(10)->get() 
            : collect();

        $groceriesCategory = Category::where('slug', 'groceries')->first();
        $groceriesProducts = $groceriesCategory 
            ? $groceriesCategory->products()->forCard()->limit(10)->get()
            : collect();

        return view('index', compact(
            'topSellingProducts',
            'allProducts',
            'electronicsProducts',
            'groceriesProducts'
        )); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}