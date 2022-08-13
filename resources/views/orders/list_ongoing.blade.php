@extends('layouts.base')

@section('title', 'Заказы в пути')
@section('header', 'Заказы в пути')

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
                <td>
                @if($order->last_status == 'Ongoing')
                <form action="{{ route('orders.deliverOrder') }}" method="POST"
                      class="form-inline">
                    @csrf
                    <input type="hidden" name="id" value="{{$order->id}}">
                    <input type="submit" class="btn btn-danger" value="Закрыть">
                </form>
                @else
                    @if($order->last_status == 'Delivered' && isset($drivers[$order->id]))
                            Клиент доставлен
                    @endif
                @endif
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
