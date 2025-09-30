@extends('layouts.admin')

@section('content')
@livewire('admin.users.show', ['userId' => $userId, 'userType' => $userType, 'user' => $user])
@endsection