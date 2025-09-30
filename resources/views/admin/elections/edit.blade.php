@extends('layouts.admin')

@section('content')
@livewire('admin.elections.edit', ['electionId' => $electionId])
@endsection