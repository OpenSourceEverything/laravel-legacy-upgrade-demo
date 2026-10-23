<?php

namespace App\Http\Controllers;

use App\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        return view('items.index', ['items' => $request->user()->items()->with('category')->orderBy('name')->get()]);
    }

    public function create(Request $request) { return $this->form($request, new Item); }

    public function store(Request $request)
    {
        $this->validateInput($request);
        $item = $request->user()->items()->create($request->only('category_id', 'sku', 'name', 'price_cents'));
        return redirect('/items/'.$item->id);
    }

    public function show(Request $request, $id)
    {
        return view('items.show', ['item' => $request->user()->items()->with('category', 'movements')->findOrFail($id)]);
    }

    public function edit(Request $request, $id)
    {
        return $this->form($request, $request->user()->items()->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $item = $request->user()->items()->findOrFail($id);
        $this->validateInput($request, $item->id);
        $item->update($request->only('category_id', 'sku', 'name', 'price_cents'));
        return redirect('/items/'.$item->id);
    }

    public function destroy(Request $request, $id)
    {
        $item = $request->user()->items()->findOrFail($id);
        // Do not erase stock history. Unused items can still be removed.
        abort_if($item->movements()->exists(), 409, 'Items with stock history cannot be deleted.');
        $item->delete();
        return redirect('/items');
    }

    public function adjust(Request $request, $id)
    {
        $this->validate($request, ['delta' => 'required|integer|between:-100000,100000|not_in:0', 'reason' => 'required|string|max:200']);
        DB::transaction(function () use ($request, $id) {
            $item = $request->user()->items()->lockForUpdate()->findOrFail($id);
            $quantity = $item->quantity + (int) $request->input('delta');
            abort_if($quantity < 0 || $quantity > 1000000, 422, 'Adjustment would put stock outside its allowed range.');
            $item->quantity = $quantity;
            $item->save();
            // The balance and its explanation must commit or roll back together.
            $item->movements()->create($request->only('delta', 'reason'));
        });
        return redirect('/items/'.$id);
    }

    private function form(Request $request, Item $item)
    {
        return view('items.form', ['item' => $item, 'categories' => $request->user()->categories()->orderBy('name')->get()]);
    }

    private function validateInput(Request $request, $id = 'NULL')
    {
        $this->validate($request, [
            'category_id' => 'required|integer|exists:categories,id,user_id,'.$request->user()->id,
            'sku' => 'required|string|max:32|alpha_dash|unique:items,sku,'.$id.',id,user_id,'.$request->user()->id,
            'name' => 'required|string|max:120',
            'price_cents' => 'required|integer|min:0|max:10000000',
        ]);
    }
}
