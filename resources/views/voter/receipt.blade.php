@extends('layouts.app')

@section('title', 'Vote Vefication')
@section('page-title', 'Vote Vefication')




@section('content')
    @livewire('voter.receipt-verification', ['election' => $election])
@endsection