@extends('layouts.admin')

@section('content')
<livewire:admin.eligible-voters :electionId="$electionId" />
@endsection