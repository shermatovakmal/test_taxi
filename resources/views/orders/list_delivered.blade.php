@extends('layouts.base')

@section('title', 'Завершенные заказы')
@section('header', 'Завершенные заказы')

@section('main')
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Время получения заказа</th>
            <th>Начальная точка</th>
            <th>Сумма</th>
            <th>Телефон</th>
            <th>Текущий статус</th>
            <th>Дата обновления статуса</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>

        @foreach ($orders as $order)
            <tr>
                <td>{{$order->created_at}}</td>
                <td>{{$order->start_location}}</td>
                <td>{{$order->amount}}</td>
                <td>{{$order->phone}}</td>
                <td>{{$order->last_status}}</td>
                <td>{{$order->status_updated_at}}</td>
                <td>Клиент доставлен</td>

            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
