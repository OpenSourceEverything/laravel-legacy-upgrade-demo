@extends('layout')
@section('content')
<h1>{{ $item->name }}</h1><p>{{ $item->sku }} · {{ $item->category->name }}</p>
<p>On hand: <strong>{{ $item->quantity }}</strong> · Unit price: ${{ number_format($item->price_cents / 100, 2) }}</p>
<a href="/items/{{ $item->id }}/edit">Edit item</a>
<form method="post" action="/items/{{ $item->id }}">{{ csrf_field() }}{{ method_field('DELETE') }}<button>Delete unused item</button></form>
<h2>Adjust stock</h2><form method="post" action="/items/{{ $item->id }}/adjust">{{ csrf_field() }}
<label>Change in units<input name="delta" type="number" required min="-100000" max="100000" placeholder="For example, 5 received or -2 issued"></label>
<label>Reason<input name="reason" required maxlength="200"></label><button>Record adjustment</button></form>
<h2>Movement history</h2><table><thead><tr><th>Change</th><th>Reason</th><th>Recorded</th></tr></thead><tbody>
@forelse($item->movements as $movement)<tr><td>{{ $movement->delta }}</td><td>{{ $movement->reason }}</td><td>{{ $movement->created_at }}</td></tr>
@empty<tr><td colspan="3">No adjustments yet.</td></tr>@endforelse</tbody></table>
@endsection
