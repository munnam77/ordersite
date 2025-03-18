@extends('layouts.app')

@section('title', '管理者ダッシュボード | 発注サイト')

@section('sidebar')
<nav class="nav flex-column">
    <a class="nav-link active" href="{{ route('admin.dashboard') }}">
        <i class="bi bi-speedometer2 me-2"></i> ダッシュボード
    </a>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">スケジュール管理</h1>
        <div>
            <button type="button" class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                <i class="bi bi-plus-circle me-1"></i> 新規スケジュール
            </button>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-calendar-check me-2"></i>スケジュール一覧</span>
                    <span class="badge bg-primary">{{ $schedules->count() }}件</span>
                </div>
                <div class="card-body">
                    @if($schedules->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>スケジュールはありません。右上の「新規スケジュール」ボタンから追加してください。
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>スケジュール名</th>
                                        <th>総数量上限</th>
                                        <th>発注済み数量</th>
                                        <th>残り数量</th>
                                        <th>進捗率</th>
                                        <th>発注件数</th>
                                        <th>作成日</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $schedule)
                                        @php
                                            $progressPercent = ($schedule->ordered_quantity / $schedule->p_total_number) * 100;
                                            $progressClass = 'bg-success';
                                            if ($progressPercent >= 90) {
                                                $progressClass = 'bg-danger';
                                            } elseif ($progressPercent >= 70) {
                                                $progressClass = 'bg-warning';
                                            }
                                        @endphp
                                        <tr>
                                            <td><span class="badge bg-light text-dark">{{ $schedule->schedule_id }}</span></td>
                                            <td>
                                                <a href="{{ route('admin.schedule', $schedule->id) }}" class="text-decoration-none text-primary fw-bold">
                                                    {{ $schedule->schedule_name }}
                                                </a>
                                            </td>
                                            <td class="text-center">{{ number_format($schedule->p_total_number) }}</td>
                                            <td class="text-center">{{ number_format($schedule->ordered_quantity) }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $schedule->p_total_number - $schedule->ordered_quantity > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                                                    {{ number_format($schedule->p_total_number - $schedule->ordered_quantity) }}
                                                </span>
                                            </td>
                                            <td style="width: 150px;">
                                                <div class="progress" style="height: 8px;" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ round($progressPercent) }}%">
                                                    <div class="progress-bar {{ $progressClass }}" role="progressbar" 
                                                         style="width: {{ $progressPercent }}%"
                                                         aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <div class="text-center mt-1 small">
                                                    <span class="text-{{ $progressPercent >= 90 ? 'danger' : ($progressPercent >= 70 ? 'warning' : 'success') }}">
                                                        {{ round($progressPercent) }}%
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary rounded-pill">
                                                    {{ $schedule->orders_count }}
                                                </span>
                                            </td>
                                            <td>{{ $schedule->created_at->format('Y/m/d') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.schedule', $schedule->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> 詳細
                                                    </a>
                                                    <a href="{{ route('admin.schedule.export', $schedule->id) }}" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-download"></i> CSV
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card mt-4 shadow-sm">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>操作ガイド
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <h5 class="fs-6"><i class="bi bi-plus-circle me-2"></i>スケジュールの追加</h5>
                                <p class="text-muted small">右上の「新規スケジュール」ボタンをクリックして、スケジュール名と総数量上限を入力してください。</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <h5 class="fs-6"><i class="bi bi-eye me-2"></i>詳細表示</h5>
                                <p class="text-muted small">スケジュール名または「詳細」ボタンをクリックすると、そのスケジュールの発注一覧が表示されます。</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <h5 class="fs-6"><i class="bi bi-download me-2"></i>CSVエクスポート</h5>
                                <p class="text-muted small">「CSV」ボタンをクリックすると、そのスケジュールの発注データをCSVファイルとしてダウンロードできます。</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 新規スケジュール追加モーダル -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.schedule.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addScheduleModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>新規スケジュール追加
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="schedule_name" class="form-label">スケジュール名 <span class="required-asterisk">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                            <input type="text" class="form-control" id="schedule_name" name="schedule_name" required>
                        </div>
                        <div class="form-text">例: 4/7週, 4/14週, 5月第1週など</div>
                    </div>
                    <div class="mb-4">
                        <label for="p_total_number" class="form-label">総数量上限 <span class="required-asterisk">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-123"></i></span>
                            <input type="number" class="form-control" id="p_total_number" name="p_total_number" min="1" required>
                            <span class="input-group-text">個</span>
                        </div>
                        <div class="form-text">全店舗合計の発注可能上限数量</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>キャンセル
                    </button>
                    <button type="submit" class="btn btn-accent">
                        <i class="bi bi-check-circle me-1"></i>追加する
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
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection 