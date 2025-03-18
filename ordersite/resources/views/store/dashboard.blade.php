@extends('layouts.app')

@section('title', '発注入力 | 発注サイト')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0">発注入力</h1>
                <div class="store-badge">
                    <span class="badge bg-primary rounded-pill">
                        <i class="bi bi-shop me-1"></i>{{ $store->store_name }}（{{ $store->prefectures }}）
                    </span>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-cart-plus me-2"></i>新規発注</span>
                    <span class="badge bg-light text-dark">店舗ID: {{ $store->store_id }}</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('store.order.store') }}" id="orderForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="schedule_id" class="form-label">スケジュール <span class="required-asterisk">*</span></label>
                            <select class="form-select @error('schedule_id') is-invalid @enderror" name="schedule_id" id="schedule_id" required>
                                <option value="">スケジュールを選択してください</option>
                                @foreach($schedules as $schedule)
                                    @php
                                        $progressPercent = ($schedule->total_ordered_quantity / $schedule->p_total_number) * 100;
                                        $badgeClass = 'bg-success';
                                        if ($progressPercent >= 90) {
                                            $badgeClass = 'bg-danger';
                                        } elseif ($progressPercent >= 70) {
                                            $badgeClass = 'bg-warning';
                                        }
                                    @endphp
                                    <option value="{{ $schedule->id }}" data-total="{{ $schedule->p_total_number }}" data-ordered="{{ $schedule->total_ordered_quantity }}" data-remaining="{{ $schedule->remaining_quantity }}" {{ old('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->schedule_name }} (残り: {{ $schedule->remaining_quantity }}/{{ $schedule->p_total_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('schedule_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="scheduleInfo" class="mt-2 d-none">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>発注状況: <span id="orderedText"></span>/<span id="totalText"></span></span>
                                    <span>残り: <span id="remainingText"></span></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div id="scheduleProgress" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="p_quantity" class="form-label">数量 <span class="required-asterisk">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('p_quantity') is-invalid @enderror" name="p_quantity" id="p_quantity" min="0.5" step="0.5" required value="{{ old('p_quantity') }}">
                                <span class="input-group-text">個</span>
                            </div>
                            @error('p_quantity')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text">0.5単位で入力可能です</div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="delivery_date" class="form-label">配送希望日</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" name="delivery_date" id="delivery_date" value="{{ old('delivery_date') }}">
                                </div>
                                @error('delivery_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="vehicle" class="form-label">車両</label>
                                <select class="form-select @error('vehicle') is-invalid @enderror" name="vehicle" id="vehicle">
                                    <option value="">選択してください</option>
                                    <option value="2t車" {{ old('vehicle') == '2t車' ? 'selected' : '' }}>2t車</option>
                                    <option value="4t車" {{ old('vehicle') == '4t車' ? 'selected' : '' }}>4t車</option>
                                    <option value="軽トラック" {{ old('vehicle') == '軽トラック' ? 'selected' : '' }}>軽トラック</option>
                                    <option value="その他" {{ old('vehicle') == 'その他' ? 'selected' : '' }}>その他</option>
                                </select>
                                @error('vehicle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="comment" class="form-label">コメント</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-chat-square-text"></i></span>
                                <textarea class="form-control @error('comment') is-invalid @enderror" name="comment" id="comment" rows="3" placeholder="特記事項がある場合はこちらに入力してください">{{ old('comment') }}</textarea>
                            </div>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid">
                            <button type="button" class="btn btn-accent btn-lg" id="confirmOrder">
                                <i class="bi bi-check-circle me-2"></i>発注する
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history me-2"></i>発注履歴</span>
                    <span class="badge bg-light text-dark">最近の注文</span>
                </div>
                <div class="card-body">
                    @if($orders->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>発注履歴はありません。
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
                                            <td><span class="badge bg-primary rounded-pill">{{ $order->p_quantity }}個</span></td>
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel"><i class="bi bi-exclamation-circle me-2"></i>発注内容の確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="lead text-center mb-4">以下の内容で発注します。よろしいですか？</p>
                <table class="table table-bordered">
                    <tr>
                        <th class="table-light" style="width: 30%">スケジュール</th>
                        <td id="confirmSchedule"></td>
                    </tr>
                    <tr>
                        <th class="table-light">数量</th>
                        <td id="confirmQuantity"></td>
                    </tr>
                    <tr>
                        <th class="table-light">配送希望日</th>
                        <td id="confirmDeliveryDate"></td>
                    </tr>
                    <tr>
                        <th class="table-light">車両</th>
                        <td id="confirmVehicle"></td>
                    </tr>
                    <tr>
                        <th class="table-light">コメント</th>
                        <td id="confirmComment" class="text-break"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>キャンセル
                </button>
                <button type="button" class="btn btn-accent" id="submitOrder">
                    <i class="bi bi-check-circle me-2"></i>発注を確定する
                </button>
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
        const scheduleInfo = document.getElementById('scheduleInfo');
        const scheduleProgress = document.getElementById('scheduleProgress');
        const orderedText = document.getElementById('orderedText');
        const totalText = document.getElementById('totalText');
        const remainingText = document.getElementById('remainingText');
        
        scheduleSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                const total = parseFloat(option.dataset.total);
                const ordered = parseFloat(option.dataset.ordered);
                const remaining = parseFloat(option.dataset.remaining);
                const percent = (ordered / total) * 100;
                
                // プログレスバー更新
                scheduleInfo.classList.remove('d-none');
                scheduleProgress.style.width = percent + '%';
                
                // プログレスバーの色を設定
                scheduleProgress.className = 'progress-bar';
                if (percent >= 90) {
                    scheduleProgress.classList.add('bg-danger');
                } else if (percent >= 70) {
                    scheduleProgress.classList.add('bg-warning');
                } else {
                    scheduleProgress.classList.add('bg-success');
                }
                
                // テキスト更新
                orderedText.textContent = ordered;
                totalText.textContent = total;
                remainingText.textContent = remaining;
                
                if (remaining <= 0) {
                    scheduleInfo.insertAdjacentHTML('afterend', 
                        '<div class="alert alert-danger mt-2">このスケジュールは発注上限に達しています。</div>');
                    quantityInput.max = 0;
                    quantityInput.disabled = true;
                } else {
                    const alertEl = scheduleInfo.nextElementSibling;
                    if (alertEl && alertEl.classList.contains('alert-danger')) {
                        alertEl.remove();
                    }
                    quantityInput.max = remaining;
                    quantityInput.disabled = false;
                }
            } else {
                scheduleInfo.classList.add('d-none');
                quantityInput.disabled = false;
            }
        });
        
        // 初期表示時にスケジュールが選択されていれば情報を表示
        if (scheduleSelect.value) {
            scheduleSelect.dispatchEvent(new Event('change'));
        }
        
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
            
            const vehicleSelect = document.getElementById('vehicle');
            const vehicle = vehicleSelect.options[vehicleSelect.selectedIndex]?.text || '-';
            document.getElementById('confirmVehicle').textContent = vehicle;
            
            const comment = document.getElementById('comment').value || '-';
            document.getElementById('confirmComment').textContent = comment;
            
            // モーダル表示
            modal.show();
        });
        
        // 発注確定ボタン押下時の処理
        submitBtn.addEventListener('click', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>処理中...';
            orderForm.submit();
        });
    });
</script>
@endsection 