@extends('layouts.admin')
@section('content')<h1 class="h3 mb-3">Edit Menu Item</h1><div class="card"><div class="card-body"><form method="POST" action="{{ route('menu-items.update', $menuItem) }}">@method('PUT')@include('menu_items._form')</form></div></div>@endsection
