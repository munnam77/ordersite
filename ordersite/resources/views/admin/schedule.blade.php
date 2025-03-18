@extends('layouts.app')

@section('title', $schedule->schedule_name . ' | 管理者ダッシュボード')

@section('sidebar')
<nav class="nav flex-column">
    <a class="nav-link" href="{{ route('admin.dashboard') }}">
        <i class="bi bi-speedometer2 me-2"></i> ダッシュボード
    </a>
    <a class="nav-link active" href="{{ route('admin.schedule', $schedule->id) }}">
        <i class="bi bi-calendar3 me-2"></i> {{ $schedule->schedule_name }}
    </a>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $schedule->schedule_name }}</h1>
            <p class="text-muted mb-0">スケジュールID: <span class="badge bg-light text-dark">{{ $schedule->schedule_id }}</span></p>
        </div>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editScheduleModal">
                <i class="bi bi-pencil me-1"></i> 編集
            </button>
            <a href="{{ route('admin.schedule.export', $schedule->id) }}" class="btn btn-success">
                <i class="bi bi-download me-1"></i> CSV出力
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-info-circle me-2"></i>
                    <span class="fw-bold">スケジュール情報</span>
                </div>
                <div class="card-body">
                    @php
                        $progressPercent = ($totalOrdered / $schedule->p_total_number) * 100;
                        $progressClass = 'bg-success';
                        $textColorClass = 'text-success';
                        if ($progressPercent >= 90) {
                            $progressClass = 'bg-danger';
                            $textColorClass = 'text-danger';
                        } elseif ($progressPercent >= 70) {
                            $progressClass = 'bg-warning';
                            $textColorClass = 'text-warning';
                        }
                    @endphp
                    
                    <div class="row mb-4">
                        <div class="col-6">
                            <div class="small text-muted mb-1">総数量上限</div>
                            <div class="fs-3 fw-bold">{{ number_format($schedule->p_total_number) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted mb-1">発注済み数量</div>
                            <div class="fs-3 fw-bold">{{ number_format($totalOrdered) }}</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="small text-muted">残り数量</div>
                            <div class="small {{ $textColorClass }} fw-bold">{{ number_format($schedule->p_total_number - $totalOrdered) }}</div>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar {{ $progressClass }}" role="progressbar" 
                                 style="width: {{ $progressPercent }}%"
                                 aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end align-items-center mt-1">
                            <div class="small {{ $textColorClass }} fw-bold">{{ round($progressPercent) }}% 達成</div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between p-3 bg-light rounded mb-4">
                        <div class="text-center">
                            <div class="small text-muted mb-1">店舗数</div>
                            <div class="fs-5 fw-bold">{{ $orders->pluck('store_id')->unique()->count() }}</div>
                        </div>
                        <div class="text-center">
                            <div class="small text-muted mb-1">発注件数</div>
                            <div class="fs-5 fw-bold">{{ $orders->count() }}</div>
                        </div>
                        <div class="text-center">
                            <div class="small text-muted mb-1">平均数量</div>
                            <div class="fs-5 fw-bold">{{ $orders->count() > 0 ? number_format($totalOrdered / $orders->count(), 1) : 0 }}</div>
                        </div>
                    </div>
                    
                    <div class="small text-muted mb-1">作成日時</div>
                    <div class="fw-bold">{{ $schedule->created_at->format('Y年m月d日 H:i') }}</div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-graph-up me-2"></i>
                    <span class="fw-bold">都道府県別集計</span>
                </div>
                <div class="card-body">
                    @php
                        $prefectureSummary = $orders->groupBy(function($order) {
                            return $order->store->prefectures;
                        })->map(function($items, $prefecture) {
                            return [
                                'count' => $items->count(),
                                'total' => $items->sum('p_quantity')
                            ];
                        })->sortByDesc(function($data) {
                            return $data['total'];
                        });
                    @endphp
                    
                    @if($prefectureSummary->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>発注データがありません
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>都道府県</th>
                                        <th class="text-center">発注数量</th>
                                        <th class="text-center">店舗数</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prefectureSummary as $prefecture => $data)
                                        <tr>
                                            <td>{{ $prefecture }}</td>
                                            <td class="text-center fw-bold">{{ number_format($data['total']) }}</td>
                                            <td class="text-center">{{ $data['count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-list-check me-2"></i>
                        <span class="fw-bold">発注一覧</span>
                    </div>
                    <span class="badge bg-primary rounded-pill">{{ $orders->count() }}件</span>
                </div>
                <div class="card-body">
                    @if($orders->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>このスケジュールに対する発注はまだありません。
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>発注ID</th>
                                        <th>店舗名</th>
                                        <th>都道府県</th>
                                        <th class="text-center">数量</th>
                                        <th>配送希望日</th>
                                        <th>車両</th>
                                        <th>コメント</th>
                                        <th>発注日時</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td class="fw-bold">{{ $order->store->store_name }}</td>
                                            <td>{{ $order->store->prefectures }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-primary rounded-pill">{{ $order->p_quantity }}</span>
                                            </td>
                                            <td>{{ $order->delivery_date ? $order->delivery_date->format('Y/m/d') : '-' }}</td>
                                            <td>{{ $order->vehicle ?: '-' }}</td>
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.schedule.update', $schedule->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editScheduleModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>スケジュール編集
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="schedule_name" class="form-label">スケジュール名 <span class="required-asterisk">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                            <input type="text" class="form-control" id="schedule_name" name="schedule_name" required value="{{ $schedule->schedule_name }}">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="p_total_number" class="form-label">総数量上限 <span class="required-asterisk">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-123"></i></span>
                            <input type="number" class="form-control" id="p_total_number" name="p_total_number" min="{{ $totalOrdered }}" required value="{{ $schedule->p_total_number }}">
                            <span class="input-group-text">個</span>
                        </div>
                        <div class="form-text">現在の発注合計 ({{ $totalOrdered }}) 以上の値を設定してください</div>
                        
                        @if($totalOrdered > 0)
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <small>既に発注が存在するため、総数量上限を発注合計より少なく設定することはできません。</small>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>キャンセル
                    </button>
                    <button type="submit" class="btn btn-accent">
                        <i class="bi bi-check-circle me-1"></i>更新する
                    </button>
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