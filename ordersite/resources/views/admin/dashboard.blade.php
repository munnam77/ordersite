@extends('layouts.app')

@section('title', '管理者ダッシュボード | 発注サイト')

@section('sidebar')
<nav class="nav flex-column">
    <a class="nav-link active" href="{{ route('admin.dashboard') }}">
        <i class="bi bi-speedometer2"></i> ダッシュボード
    </a>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">スケジュール管理</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
            <i class="bi bi-plus-circle"></i> 新規スケジュール
        </button>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    スケジュール一覧
                </div>
                <div class="card-body">
                    @if($schedules->isEmpty())
                        <div class="alert alert-info mb-0">
                            スケジュールはありません。右上の「新規スケジュール」ボタンから追加してください。
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                            <td>{{ $schedule->schedule_id }}</td>
                                            <td>
                                                <a href="{{ route('admin.schedule', $schedule->id) }}">
                                                    {{ $schedule->schedule_name }}
                                                </a>
                                            </td>
                                            <td>{{ $schedule->p_total_number }}</td>
                                            <td>{{ $schedule->ordered_quantity }}</td>
                                            <td>{{ $schedule->p_total_number - $schedule->ordered_quantity }}</td>
                                            <td style="width: 150px;">
                                                <div class="progress">
                                                    <div class="progress-bar {{ $progressClass }}" role="progressbar" 
                                                         style="width: {{ $progressPercent }}%"
                                                         aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100">
                                                        {{ round($progressPercent) }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $schedule->orders_count }}</td>
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
        </div>
    </div>
</div>

<!-- 新規スケジュール追加モーダル -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.schedule.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addScheduleModalLabel">新規スケジュール追加</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="schedule_name" class="form-label">スケジュール名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="schedule_name" name="schedule_name" required>
                        <div class="form-text">例: 4/7週, 4/14週</div>
                    </div>
                    <div class="mb-3">
                        <label for="p_total_number" class="form-label">総数量上限 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="p_total_number" name="p_total_number" min="1" required>
                        <div class="form-text">全店舗合計の発注可能上限数量</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary">追加する</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 