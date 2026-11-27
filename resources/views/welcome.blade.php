@extends('layout')
@section('content')<h1>Tasks for Today</h1><p>Keep track of what needs doing today.</p><a class='button' href="{{ route('tasks.index') }}">Browse tasks</a>@endsection