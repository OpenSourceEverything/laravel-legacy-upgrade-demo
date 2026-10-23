@extends('layout')
@section('content')
<h1>Staff sign in</h1><p>Use staff@example.test / demo-password for this local fixture.</p>
<form method="post" action="/login">{{ csrf_field() }}
<label>Email<input type="email" name="email" required value="{{ old('email') }}"></label>
<label>Password<input type="password" name="password" required></label><button>Sign in</button></form>
@endsection
