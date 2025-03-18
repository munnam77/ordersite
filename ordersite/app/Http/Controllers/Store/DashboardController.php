<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the store dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $store = Auth::guard('store')->user();
        $schedules = Schedule::all();
        $orders = Order::where('store_id', $store->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('store.dashboard', compact('store', 'schedules', 'orders'));
    }

    /**
     * Store a new order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeOrder(Request $request)
    {
        $store = Auth::guard('store')->user();
        
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'p_quantity' => 'required|numeric|min:0.5',
            'comment' => 'nullable|string|max:1000',
            'delivery_date' => 'nullable|date',
            'vehicle' => 'nullable|string|max:255',
        ], [
            'schedule_id.required' => 'スケジュールを選択してください',
            'schedule_id.exists' => '無効なスケジュールです',
            'p_quantity.required' => '数量を入力してください',
            'p_quantity.numeric' => '数量は数値で入力してください',
            'p_quantity.min' => '数量は0.5以上で入力してください',
            'comment.max' => 'コメントは1000文字以内で入力してください',
            'delivery_date.date' => '有効な日付を入力してください',
            'vehicle.max' => '車両名は255文字以内で入力してください',
        ]);
        
        $schedule = Schedule::findOrFail($validated['schedule_id']);
        
        // Check if the quantity exceeds the schedule's total limit
        $currentTotal = Order::where('schedule_id', $schedule->id)->sum('p_quantity');
        $newTotal = $currentTotal + $validated['p_quantity'];
        
        if ($newTotal > $schedule->p_total_number) {
            return back()->withErrors([
                'p_quantity' => "発注数量が上限を超えています。残り可能数量: " . 
                    ($schedule->p_total_number - $currentTotal)
            ])->withInput();
        }
        
        // Create the order
        $order = new Order([
            'store_id' => $store->id,
            'schedule_id' => $schedule->id,
            'schedule_name' => $schedule->schedule_name,
            'p_quantity' => $validated['p_quantity'],
            'comment' => $validated['comment'] ?? null,
            'delivery_date' => $validated['delivery_date'] ?? null,
            'vehicle' => $validated['vehicle'] ?? null,
        ]);
        
        $order->save();
        
        return redirect()->route('store.dashboard')
            ->with('success', '発注が完了しました。発注番号: ' . $order->id);
    }
} 