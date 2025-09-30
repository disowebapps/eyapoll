@extends('layouts.observer-app')

@section('content')
@livewire('observer.election-results', ['electionId' => $electionId])
@endsection