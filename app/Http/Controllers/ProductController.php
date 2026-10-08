<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Menampilkan semua product.
     */
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $selectedCategory = request('category');
        $products = Product::when(
            $selectedCategory !== null && $selectedCategory !== '',
            fn ($query) => $query->where('category', $selectedCategory)
        )->latest()->paginate(10)->withQueryString();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Menampilkan form tambah product.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    /**
     * Menyimpan product baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|exists:categories,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail product.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Menampilkan form edit product.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Memperbarui product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|exists:categories,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product berhasil diperbarui.');
    }

    /**
     * Menonaktifkan product.
     *
     * Product tidak dihapus dari database karena
     * mungkin sudah digunakan pada transaction_details.
     */
    public function destroy(Product $product)
    {
        $product->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product berhasil dinonaktifkan.'
            );
    }
}