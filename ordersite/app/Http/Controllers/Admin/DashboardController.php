<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $schedules = Schedule::withCount('orders')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get total ordered quantities per schedule
        foreach ($schedules as $schedule) {
            $schedule->ordered_quantity = Order::where('schedule_id', $schedule->id)
                ->sum('p_quantity');
        }
        
        return view('admin.dashboard', compact('admin', 'schedules'));
    }
    
    /**
     * Display the schedule detail view.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function showSchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $orders = Order::where('schedule_id', $schedule->id)
            ->with('store')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $totalOrdered = $orders->sum('p_quantity');
        $stores = Store::all();
        
        return view('admin.schedule', compact('schedule', 'orders', 'totalOrdered', 'stores'));
    }
    
    /**
     * Store a new schedule.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'schedule_name' => 'required|string|max:255|unique:schedules',
            'p_total_number' => 'required|numeric|min:1',
        ], [
            'schedule_name.required' => 'スケジュール名を入力してください',
            'schedule_name.unique' => 'このスケジュール名は既に存在します',
            'p_total_number.required' => '総数量上限を入力してください',
            'p_total_number.numeric' => '総数量上限は数値で入力してください',
            'p_total_number.min' => '総数量上限は1以上で入力してください',
        ]);
        
        // Find the next schedule_id
        $maxScheduleId = Schedule::max('schedule_id') ?? 0;
        
        // Create the schedule
        $schedule = new Schedule([
            'schedule_id' => $maxScheduleId + 1,
            'schedule_name' => $validated['schedule_name'],
            'p_total_number' => $validated['p_total_number'],
        ]);
        
        $schedule->save();
        
        return redirect()->route('admin.dashboard')
            ->with('success', 'スケジュールを追加しました: ' . $schedule->schedule_name);
    }
    
    /**
     * Update an existing schedule.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSchedule(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        
        $validated = $request->validate([
            'schedule_name' => 'required|string|max:255|unique:schedules,schedule_name,' . $schedule->id,
            'p_total_number' => 'required|numeric|min:1',
        ], [
            'schedule_name.required' => 'スケジュール名を入力してください',
            'schedule_name.unique' => 'このスケジュール名は既に存在します',
            'p_total_number.required' => '総数量上限を入力してください',
            'p_total_number.numeric' => '総数量上限は数値で入力してください',
            'p_total_number.min' => '総数量上限は1以上で入力してください',
        ]);
        
        // Check if new total is less than current ordered quantity
        $currentTotal = Order::where('schedule_id', $schedule->id)->sum('p_quantity');
        if ($validated['p_total_number'] < $currentTotal) {
            return back()->withErrors([
                'p_total_number' => '総数量上限は現在の発注合計 (' . $currentTotal . ') 以上である必要があります'
            ])->withInput();
        }
        
        $schedule->update([
            'schedule_name' => $validated['schedule_name'],
            'p_total_number' => $validated['p_total_number'],
        ]);
        
        return redirect()->route('admin.schedule', $schedule->id)
            ->with('success', 'スケジュールを更新しました: ' . $schedule->schedule_name);
    }
    
    /**
     * Export orders for a schedule as CSV.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportSchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $orders = Order::where('schedule_id', $schedule->id)
            ->with('store')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $filename = 'schedule_' . $schedule->schedule_id . '_' . $schedule->schedule_name . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        
        $callback = function() use ($orders, $schedule) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper Japanese character encoding in Excel
            fputs($file, "\xEF\xBB\xBF");
            
            // Add header row
            fputcsv($file, [
                '発注ID',
                '店舗ID',
                '店舗名',
                '都道府県',
                'スケジュール',
                '数量',
                'コメント',
                '配送日',
                '車両',
                '発注日時',
            ]);
            
            // Add data rows
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->store->store_id,
                    $order->store->store_name,
                    $order->store->prefectures,
                    $order->schedule_name,
                    $order->p_quantity,
                    $order->comment,
                    $order->delivery_date ? $order->delivery_date->format('Y/m/d') : '',
                    $order->vehicle,
                    $order->created_at->format('Y/m/d H:i'),
                ]);
            }
            
            fclose($file);
        };
        
        return new StreamedResponse($callback, 200, $headers);
    }
} 