@extends('layouts.app')

@section('title', '発注入力 | 発注サイト')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="h3 mb-4">発注入力</h1>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ $store->store_name }} ({{ $store->prefectures }})</span>
                    <span class="badge bg-secondary">店舗ID: {{ $store->store_id }}</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('store.order.store') }}" id="orderForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="schedule_id" class="form-label">スケジュール <span class="text-danger">*</span></label>
                            <select class="form-select @error('schedule_id') is-invalid @enderror" name="schedule_id" id="schedule_id" required>
                                <option value="">スケジュールを選択してください</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}" data-total="{{ $schedule->p_total_number }}" data-ordered="{{ $schedule->total_ordered_quantity }}" data-remaining="{{ $schedule->remaining_quantity }}">
                                        {{ $schedule->schedule_name }} (残り: {{ $schedule->remaining_quantity }}/{{ $schedule->p_total_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('schedule_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" id="scheduleHelp">発注可能な残り数量が表示されます</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="p_quantity" class="form-label">数量 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('p_quantity') is-invalid @enderror" name="p_quantity" id="p_quantity" min="0.5" step="0.5" required value="{{ old('p_quantity') }}">
                                <span class="input-group-text">個</span>
                            </div>
                            @error('p_quantity')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="delivery_date" class="form-label">配送希望日 (任意)</label>
                            <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" name="delivery_date" id="delivery_date" value="{{ old('delivery_date') }}">
                            @error('delivery_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="vehicle" class="form-label">車両 (任意)</label>
                            <input type="text" class="form-control @error('vehicle') is-invalid @enderror" name="vehicle" id="vehicle" value="{{ old('vehicle') }}" placeholder="例: 2tトラック">
                            @error('vehicle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="comment" class="form-label">コメント (任意)</label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" name="comment" id="comment" rows="3">{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary" id="confirmOrder">発注する</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">最近の発注履歴</div>
                <div class="card-body">
                    @if($orders->isEmpty())
                        <div class="alert alert-info mb-0">
                            発注履歴はありません。
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>スケジュール</th>
                                        <th>数量</th>
                                        <th>配送希望日</th>
                                        <th>発注日時</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>{{ $order->schedule_name }}</td>
                                            <td>{{ $order->p_quantity }} 個</td>
                                            <td>{{ $order->delivery_date ? $order->delivery_date->format('Y/m/d') : '-' }}</td>
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

<!-- 確認モーダル -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">発注内容の確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>以下の内容で発注します。よろしいですか？</p>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">スケジュール</th>
                        <td id="confirmSchedule"></td>
                    </tr>
                    <tr>
                        <th>数量</th>
                        <td id="confirmQuantity"></td>
                    </tr>
                    <tr>
                        <th>配送希望日</th>
                        <td id="confirmDeliveryDate"></td>
                    </tr>
                    <tr>
                        <th>車両</th>
                        <td id="confirmVehicle"></td>
                    </tr>
                    <tr>
                        <th>コメント</th>
                        <td id="confirmComment"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="submitOrder">発注を確定する</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const orderForm = document.getElementById('orderForm');
        const confirmBtn = document.getElementById('confirmOrder');
        const submitBtn = document.getElementById('submitOrder');
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        
        // スケジュール選択時に上限をチェック
        const scheduleSelect = document.getElementById('schedule_id');
        const quantityInput = document.getElementById('p_quantity');
        
        scheduleSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                const remaining = parseFloat(option.dataset.remaining);
                if (remaining <= 0) {
                    alert('このスケジュールは発注上限に達しています。');
                    quantityInput.max = 0;
                } else {
                    quantityInput.max = remaining;
                }
            }
        });
        
        // 確認ボタン押下時の処理
        confirmBtn.addEventListener('click', function() {
            // 基本的なバリデーション
            if (!orderForm.checkValidity()) {
                orderForm.reportValidity();
                return;
            }
            
            // スケジュール選択チェック
            const scheduleId = scheduleSelect.value;
            if (!scheduleId) {
                alert('スケジュールを選択してください。');
                scheduleSelect.focus();
                return;
            }
            
            // 数量チェック
            const quantity = parseFloat(quantityInput.value);
            if (isNaN(quantity) || quantity < 0.5) {
                alert('数量は0.5以上で入力してください。');
                quantityInput.focus();
                return;
            }
            
            // スケジュールの残り数量チェック
            const option = scheduleSelect.options[scheduleSelect.selectedIndex];
            const remaining = parseFloat(option.dataset.remaining);
            if (quantity > remaining) {
                alert(`発注数量が上限を超えています。\n残り可能数量: ${remaining}`);
                quantityInput.focus();
                return;
            }
            
            // モーダルに値をセット
            document.getElementById('confirmSchedule').textContent = option.textContent.split(' (')[0];
            document.getElementById('confirmQuantity').textContent = `${quantity} 個`;
            
            const deliveryDateInput = document.getElementById('delivery_date');
            let deliveryDateText = '-';
            if (deliveryDateInput.value) {
                const date = new Date(deliveryDateInput.value);
                deliveryDateText = `${date.getFullYear()}/${(date.getMonth()+1).toString().padStart(2, '0')}/${date.getDate().toString().padStart(2, '0')}`;
            }
            document.getElementById('confirmDeliveryDate').textContent = deliveryDateText;
            
            const vehicle = document.getElementById('vehicle').value || '-';
            document.getElementById('confirmVehicle').textContent = vehicle;
            
            const comment = document.getElementById('comment').value || '-';
            document.getElementById('confirmComment').textContent = comment;
            
            // モーダル表示
            modal.show();
        });
        
        // 発注確定ボタン押下時の処理
        submitBtn.addEventListener('click', function() {
            orderForm.submit();
        });
    });
</script>
@endsection 