<?php

use Illuminate\Support\Facades\Route;

Route::get('/livewire-test', function () {
    return view('livewire-test');
})->name('livewire.test');