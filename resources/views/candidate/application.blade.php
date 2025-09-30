@extends('layouts.candidate')

@section('content')
@livewire('candidate.application-status', ['candidate' => $candidateId])
@endsection