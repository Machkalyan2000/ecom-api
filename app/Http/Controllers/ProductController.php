<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('sort_price')) {
            $direction = $request->get('sort_price') === 'desc' ? 'desc' : 'asc';
            $query->orderBy('price', $direction);
        }

        return response()->json($query->get());
    }

}
