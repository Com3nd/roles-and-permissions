<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProductController extends Controller
{
    function __construct(){
        $this->middleware('permission:product-list|product-create|product-edit|product-delete',
            ['only'=>['show', 'index']]
        );

        $this->middleware('permission:product-create',
            ['only'=>['create', 'store']]
        );

        $this->middleware('permission:product-edit',
            ['only'=>['edit', 'update']]
        );

        $this->middleware('permission:product-delete',
            ['only'=>['destroy']]
        );


    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::latest()->paginate(5);

        return view('products.index', compact(['products']))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'details' => 'required',
        ]);

        $user = Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product Created Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required',
            'details' => 'required',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'user Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Product Deleted Successfully');
    }
}
