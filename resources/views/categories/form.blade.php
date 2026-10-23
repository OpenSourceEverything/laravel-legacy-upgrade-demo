@extends('layout')
@section('content')
<h1>{{ $category->exists ? 'Edit category' : 'Add category' }}</h1>
<form method="post" action="{{ $category->exists ? '/categories/'.$category->id : '/categories' }}">{{ csrf_field() }}
@if($category->exists){{ method_field('PUT') }}@endif
<label>Name<input name="name" required maxlength="120" value="{{ old('name', $category->name) }}"></label>
<button>Save category</button></form>
@endsection
