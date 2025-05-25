<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
   
    public function index()
    {
          $featuredProducts = Product::where('is_featured', true)
                                   ->where('is_active', true)
                                   ->with(['images' => fn($q)=>$q->orderBy('position')->limit(1)])
                                   ->latest()
                                   ->take(8) // Or your desired number
                                   ->get();

        // $navCategories is now provided by the View Composer
        return view('index', compact('featuredProducts'));   
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