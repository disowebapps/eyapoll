@extends('layouts.app')

@section('title', 'Apply as Candidate')
@section('page-title', 'Apply as Candidate')

@section('content')
@livewire('candidate.application-form', ['election' => $election])
@endsection