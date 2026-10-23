@extends('layout')
@section('content')
<h1>{{ $item->exists ? 'Edit item' : 'Add item' }}</h1>
<form method="post" action="{{ $item->exists ? '/items/'.$item->id : '/items' }}">{{ csrf_field() }}
@if($item->exists){{ method_field('PUT') }}@endif
<label>Category<select name="category_id" required><option value="">Choose category</option>
@foreach($categories as $category)<option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select></label>
<label>SKU<input name="sku" required maxlength="32" value="{{ old('sku', $item->sku) }}"></label>
<label>Name<input name="name" required maxlength="120" value="{{ old('name', $item->name) }}"></label>
<label>Unit price in cents<input name="price_cents" type="number" min="0" max="10000000" required value="{{ old('price_cents', $item->price_cents ?: 0) }}"></label>
<p>Use a stock adjustment after saving to change the quantity.</p><button>Save item</button></form>
@endsection
