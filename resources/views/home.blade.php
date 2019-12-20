@extends('layouts.app')
<link href="{{ asset('custom/custum.css') }}" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Muli&display=swap" rel="stylesheet">
@section('content')
	<div class="container-fluid p-0">
		<div id="frame">
			@php $create = auth()->user()->hasRole('superadmin') ? 1 : 0 
			@endphp
			<chat-component :user="{{ auth()->user() }}" 
			:allusers="{{ $allusers }}" :allcontacts="{{ $allcontacts }}" 
			:rooms="{{ $rooms }}" 
			:group_permission="{{ 1 }}">
			</chat-component>
		</div>
	</div>
@endsection
