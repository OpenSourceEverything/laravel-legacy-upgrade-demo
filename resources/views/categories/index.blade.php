@extends('layout')
@section('content')
<h1>Categories</h1><p><a class="button" href="/categories/create">Add category</a></p>
<table><thead><tr><th>Name</th><th>Manage</th></tr></thead><tbody>
@forelse($categories as $category)<tr><td>{{ $category->name }}</td>
<td><a href="/categories/{{ $category->id }}/edit">Edit</a><form method="post" action="/categories/{{ $category->id }}">{{ csrf_field() }}{{ method_field('DELETE') }}<button>Delete</button></form></td></tr>
@empty<tr><td colspan="2">No categories yet.</td></tr>@endforelse
</tbody></table>
@endsection
