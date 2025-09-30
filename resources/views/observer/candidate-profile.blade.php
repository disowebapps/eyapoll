@extends('layouts.observer-app')

@section('content')
@livewire('observer.candidate-profile', ['candidateId' => $candidateId])
@endsection