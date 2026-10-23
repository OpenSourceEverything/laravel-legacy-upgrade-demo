@extends('layout')
@section('content')
<h1>Inventory</h1><p>Items, stock balances and a history of every adjustment.</p>
<p><a class="button" href="/items/create">Add item</a></p>
<table><thead><tr><th>SKU</th><th>Item</th><th>Category</th><th>On hand</th><th>Unit price</th></tr></thead><tbody>
@forelse($items as $item)<tr><td>{{ $item->sku }}</td><td><a href="/items/{{ $item->id }}">{{ $item->name }}</a></td><td>{{ $item->category->name }}</td><td>{{ $item->quantity }}</td><td>${{ number_format($item->price_cents / 100, 2) }}</td></tr>
@empty<tr><td colspan="5">No inventory yet.</td></tr>@endforelse
</tbody></table>
@endsection
