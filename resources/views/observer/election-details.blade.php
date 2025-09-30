@extends('layouts.observer-app')

@section('content')
@livewire('observer.election-details', ['election' => $election])
@endsection