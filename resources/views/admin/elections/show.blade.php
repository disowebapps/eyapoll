@extends('layouts.admin')

@section('content')
@livewire('admin.elections.show', ['electionId' => $electionId])
@endsection