@extends('layouts.observer-app')

@section('content')
<livewire:observer.election-positions :electionId="$electionId" />
@endsection