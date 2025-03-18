@extends('layouts.app')

@section('title', $schedule->schedule_name . ' | 管理者ダッシュボード')

@section('sidebar')
<nav class="nav flex-column">
    <a class="nav-link" href="{{ route('admin.dashboard') }}">
        <i class="bi bi-speedometer2"></i> ダッシュボード
    </a>
    <a class="nav-link active" href="{{ route('admin.schedule', $schedule->id) }}">
        <i class="bi bi-calendar3"></i> {{ $schedule->schedule_name }}
    </a>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">{{ $schedule->schedule_name }}</h1>
            <p class="text-muted">スケジュールID: {{ $schedule->schedule_id }}</p>
        </div>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editScheduleModal">
                <i class="bi bi-pencil"></i> 編集
            </button>
            <a href="{{ route('admin.schedule.export', $schedule->id) }}" class="btn btn-success">
                <i class="bi bi-download"></i> CSV出力
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    スケジュール情報
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5 class="card-title">総数量上限</h5>
                        <p class="card-text display-6">{{ $schedule->p_total_number }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h5 class="card-title">発注済み数量</h5>
                        <p class="card-text display-6">{{ $totalOrdered }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h5 class="card-title">残り数量</h5>
                        <p class="card-text display-6 {{ ($schedule->p_total_number - $totalOrdered) < 10 ? 'text-danger' : '' }}">
                            {{ $schedule->p_total_number - $totalOrdered }}
                        </p>
                    </div>
                    
                    @php
                        $progressPercent = ($totalOrdered / $schedule->p_total_number) * 100;
                        $progressClass = 'bg-success';
                        if ($progressPercent >= 90) {
                            $progressClass = 'bg-danger';
                        } elseif ($progressPercent >= 70) {
                            $progressClass = 'bg-warning';
                        }
                    @endphp
                    
                    <div class="mb-3">
                        <h5 class="card-title">進捗率</h5>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar {{ $progressClass }}" role="progressbar" 
                                 style="width: {{ $progressPercent }}%"
                                 aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100">
                                {{ round($progressPercent) }}%
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-0">
                        <h5 class="card-title">作成日時</h5>
                        <p class="card-text">{{ $schedule->created_at->format('Y/m/d H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    発注一覧
                </div>
                <div class="card-body">
                    @if($orders->isEmpty())
                        <div class="alert alert-info mb-0">
                            このスケジュールに対する発注はまだありません。
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>発注ID</th>
                                        <th>店舗名</th>
                                        <th>都道府県</th>
                                        <th>数量</th>
                                        <th>配送希望日</th>
                                        <th>コメント</th>
                                        <th>発注日時</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>{{ $order->store->store_name }}</td>
                                            <td>{{ $order->store->prefectures }}</td>
                                            <td>{{ $order->p_quantity }}</td>
                                            <td>{{ $order->delivery_date ? $order->delivery_date->format('Y/m/d') : '-' }}</td>
                                            <td>
                                                @if($order->comment)
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                           data-bs-toggle="tooltip" data-bs-placement="top" 
                                                           title="{{ $order->comment }}">
                                                        <i class="bi bi-chat-dots"></i>
                                                    </button>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $order->created_at->format('Y/m/d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- スケジュール編集モーダル -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.schedule.update', $schedule->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editScheduleModalLabel">スケジュール編集</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="schedule_name" class="form-label">スケジュール名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="schedule_name" name="schedule_name" required value="{{ $schedule->schedule_name }}">
                    </div>
                    <div class="mb-3">
                        <label for="p_total_number" class="form-label">総数量上限 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="p_total_number" name="p_total_number" min="{{ $totalOrdered }}" required value="{{ $schedule->p_total_number }}">
                        <div class="form-text">現在の発注合計 ({{ $totalOrdered }}) 以上の値を設定してください</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary">更新する</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ツールチップの初期化
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endsection 