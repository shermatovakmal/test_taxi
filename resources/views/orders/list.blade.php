@extends('layouts.base')

@section('title', 'Главная')
@section('header', 'Заказы в ожидании')

@section('main')
    <div class="alert alert-info">Доступных водителей: {{$availableDriversCount}}</div>
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
                @if(isset($result) && $result['order_id'] == $order->id)
                    <div class="alert alert-{{$result['return_status']}}" role="alert">
                        {{$result['return_text']}}
                    </div>
                @endif
                @if($order->last_status == 'Received')
                <form action="{{ route('orders.assignDrv') }}" method="POST"
                          class="form-inline">
                        @csrf
                        <input type="hidden" name="id" value="{{$order->id}}">
                        <input type="submit" class="btn btn-danger"
                               value="Назначить">
                    </form>
                @else
                    @if($order->last_status == 'Assigned' && isset($drivers[$order->id]))
                            {{$drivers[$order->id]['name_last']}} {{$drivers[$order->id]['name_first']}} - {{$drivers[$order->id]['phone']}}
                    @endif
                @endif
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
