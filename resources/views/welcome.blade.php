@extends('layouts.base')

@section('title', 'Тестовые задания')
@section('header', 'Тестовые задания')

@section('main')
    <div class="alert alert-info">Задача 1. Автоназначение. <a href="{{ route('orders.list') }}">Перейти</a></div>
    <div class="alert alert-info">Задача 2. API-для регистрации и авторизации пользователя. <a href="">Перейти</a></div>
    <div class="alert alert-info">Задача 3. Биллинг. <a href="{{ route('orders.ongoing') }}">Перейти</a></div>
@endsection
