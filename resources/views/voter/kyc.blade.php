@extends('layouts.app')

@section('title', 'KYC')
@section('page-title', 'KYC')




@section('content')
<div class="max-w-4xl mx-auto">
    @livewire('voter.kyc-upload')
</div>
@endsection