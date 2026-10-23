<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return view('categories.index', ['categories' => $request->user()->categories()->orderBy('name')->get()]);
    }

    public function create() { return view('categories.form', ['category' => new \App\Category]); }

    public function store(Request $request)
    {
        $this->validate($request, ['name' => 'required|string|max:120']);
        $request->user()->categories()->create($request->only('name'));
        return redirect('/categories');
    }

    public function edit(Request $request, $id)
    {
        return view('categories.form', ['category' => $request->user()->categories()->findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $category = $request->user()->categories()->findOrFail($id);
        $this->validate($request, ['name' => 'required|string|max:120']);
        $category->update($request->only('name'));
        return redirect('/categories');
    }

    public function destroy(Request $request, $id)
    {
        $category = $request->user()->categories()->findOrFail($id);
        abort_if($category->items()->exists(), 409, 'Move or remove the items first.');
        $category->delete();
        return redirect('/categories');
    }
}
