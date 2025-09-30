@extends('layouts.admin')

@section('title', 'Election Management')

@section('content')
    <livewire:admin.election-management :election-id="$electionId" />
@endsection