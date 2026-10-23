<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Stockroom · Upgrade demonstration</title><link rel="stylesheet" href="{{ asset('service.css') }}"></head>
<body><header><strong>STOCKROOM</strong><span>Legacy upgrade demonstration</span></header>
<main><nav><a href="/items">Inventory</a><a href="/categories">Categories</a>
@if(Auth::check())<form method="post" action="/logout">{{ csrf_field() }}<button>Sign out</button></form>@endif</nav>
@if($errors->any())<aside role="alert"><strong>Please check your input.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></aside>@endif
@yield('content')
<footer>Local, synthetic data only · Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }}</footer></main></body></html>
