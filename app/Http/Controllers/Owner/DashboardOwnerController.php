<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransaksiQris;
use App\Models\TransaksiTunai;
use Illuminate\Support\Carbon;

class DashboardOwnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today = Carbon::now()->format('Y-m-d');
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');

        $todayQris = TransaksiQris::whereDate('timestamp', $today)->get();
        $todayTunai = TransaksiTunai::whereDate('timestamp', $today)->get();
        
        $yesterdayQris = TransaksiQris::whereDate('timestamp', $yesterday)->get();
        $yesterdayTunai = TransaksiTunai::whereDate('timestamp', $yesterday)->get();

        $todayModal = $todayQris->sum('total_cost_price') + $todayTunai->sum('total_cost_price');
        $todayPendapatan = $todayQris->sum('subtotal') + $todayTunai->sum('subtotal');
        $todayKeuntungan = $todayPendapatan - $todayModal;

        $yesterdayModal = $yesterdayQris->sum('total_cost_price') + $yesterdayTunai->sum('total_cost_price');
        $yesterdayPendapatan = $yesterdayQris->sum('subtotal') + $yesterdayTunai->sum('subtotal');
        $yesterdayKeuntungan = $yesterdayPendapatan - $yesterdayModal;

        $modalPercentage = $yesterdayModal != 0 ? (($todayModal - $yesterdayModal) / $yesterdayModal) * 100 : 0;
        $pendapatanPercentage = $yesterdayPendapatan != 0 ? (($todayPendapatan - $yesterdayPendapatan) / $yesterdayPendapatan) * 100 : 0;
        $keuntunganPercentage = $yesterdayKeuntungan != 0 ? (($todayKeuntungan - $yesterdayKeuntungan) / $yesterdayKeuntungan) * 100 : 0;

        $modalDiff = $todayModal - $yesterdayModal;
        $pendapatanDiff = $todayPendapatan - $yesterdayPendapatan;
        $keuntunganDiff = $todayKeuntungan - $yesterdayKeuntungan;

        $monthlyData = $this->getMonthlyData();

        return view('owner.dashboard', compact(
            'todayModal', 'todayPendapatan', 'todayKeuntungan',
            'modalPercentage', 'pendapatanPercentage', 'keuntunganPercentage',
            'modalDiff', 'pendapatanDiff', 'keuntunganDiff', 'monthlyData'
        ));
    }

    public function getDataByDateRange(Request $request)
    {
        // Tentukan rentang tanggal
        if ($request->has('preset')) {
            $dates = $this->getPresetDates($request->preset);
            $startDate = Carbon::parse($dates['start'])->startOfDay();
            $endDate = Carbon::parse($dates['end'])->endOfDay();
        } else {
            $startDate = Carbon::parse($request->input('start'))->startOfDay();
            $endDate = Carbon::parse($request->input('end'))->endOfDay();
        }

        // Data periode saat ini
        $currentQris = TransaksiQris::whereBetween('timestamp', [$startDate, $endDate])->get();
        $currentTunai = TransaksiTunai::whereBetween('timestamp', [$startDate, $endDate])->get();

        $currentModal = $currentQris->sum('total_cost_price') + $currentTunai->sum('total_cost_price');
        $currentPendapatan = $currentQris->sum('subtotal') + $currentTunai->sum('subtotal');
        $currentKeuntungan = $currentPendapatan - $currentModal;

        // Tentukan periode sebelumnya berdasarkan preset
        $previousStartDate = '';
        $previousEndDate = '';
        
        switch($request->preset) {
            case 'today':
                $previousStartDate = Carbon::yesterday()->startOfDay();
                $previousEndDate = Carbon::yesterday()->endOfDay();
                break;
            case 'yesterday':
                $previousStartDate = Carbon::now()->subDays(2)->startOfDay();
                $previousEndDate = Carbon::now()->subDays(2)->endOfDay();
                break;
            case 'last7days':
                $previousStartDate = Carbon::now()->subDays(14)->startOfDay();
                $previousEndDate = Carbon::now()->subDays(8)->endOfDay();
                break;
            case 'last30days':
                $previousStartDate = Carbon::now()->subDays(60)->startOfDay();
                $previousEndDate = Carbon::now()->subDays(31)->endOfDay();
                break;
            case 'thisMonth':
                $previousStartDate = Carbon::now()->subMonth()->startOfMonth();
                $previousEndDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'lastMonth':
                $previousStartDate = Carbon::now()->subMonths(2)->startOfMonth();
                $previousEndDate = Carbon::now()->subMonths(2)->endOfMonth();
                break;
            default:
                // Untuk custom date range
                $daysDiff = $startDate->diffInDays($endDate) + 1;
                $previousStartDate = $startDate->copy()->subDays($daysDiff);
                $previousEndDate = $startDate->copy()->subDay();
        }

        // Data periode sebelumnya
        $previousQris = TransaksiQris::whereBetween('timestamp', [$previousStartDate, $previousEndDate])->get();
        $previousTunai = TransaksiTunai::whereBetween('timestamp', [$previousStartDate, $previousEndDate])->get();

        $previousModal = $previousQris->sum('total_cost_price') + $previousTunai->sum('total_cost_price');
        $previousPendapatan = $previousQris->sum('subtotal') + $previousTunai->sum('subtotal');
        $previousKeuntungan = $previousPendapatan - $previousModal;

        // Hitung persentase perubahan
        // Menggunakan nilai absolut untuk perhitungan persentase
        $modalPercentage = $previousModal != 0 ? (($currentModal - $previousModal) / abs($previousModal)) * 100 : 0;
        $pendapatanPercentage = $previousPendapatan != 0 ? (($currentPendapatan - $previousPendapatan) / abs($previousPendapatan)) * 100 : 0;
        $keuntunganPercentage = $previousKeuntungan != 0 ? (($currentKeuntungan - $previousKeuntungan) / abs($previousKeuntungan)) * 100 : 0;

        // Hitung selisih nominal (tetap menggunakan current - previous)
        $modalDiff = $currentModal - $previousModal;
        $pendapatanDiff = $currentPendapatan - $previousPendapatan;
        $keuntunganDiff = $currentKeuntungan - $previousKeuntungan;

        // Ambil data bulanan untuk grafik
        $monthlyData = $this->getMonthlyData($startDate, $endDate, $currentModal, $currentPendapatan, $currentKeuntungan);

        return response()->json([
            'todayModal' => $currentModal,
            'todayPendapatan' => $currentPendapatan,
            'todayKeuntungan' => $currentKeuntungan,
            'modalPercentage' => round($modalPercentage, 2),
            'pendapatanPercentage' => round($pendapatanPercentage, 2),
            'keuntunganPercentage' => round($keuntunganPercentage, 2),
            'modalDiff' => $modalDiff,
            'pendapatanDiff' => $pendapatanDiff,
            'keuntunganDiff' => $keuntunganDiff,
            'monthlyData' => $monthlyData
        ]);
    }

    /**
     * Get monthly data for charts
     */
    private function getMonthlyData($startDate = null, $endDate = null, $rangeModal = null, $rangePendapatan = null, $rangeKeuntungan = null)
    {
        $monthlyData = [];

        // If no dates are provided, default to the current month
        if (!$startDate || !$endDate) {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        } else {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
        }

        // Jika bulan sama, ambil data harian
        if ($start->format('Y-m') == $end->format('Y-m')) {
            $currentDate = $start->copy();
            while ($currentDate <= $end) {
                $dayQris = TransaksiQris::whereDate('timestamp', $currentDate->format('Y-m-d'))->get();
                $dayTunai = TransaksiTunai::whereDate('timestamp', $currentDate->format('Y-m-d'))->get();

                $modal = $dayQris->sum('total_cost_price') + $dayTunai->sum('total_cost_price');
                $pendapatan = $dayQris->sum('subtotal') + $dayTunai->sum('subtotal');
                $keuntungan = $pendapatan - $modal;

                $monthlyData[$currentDate->format('d M')] = [
                    'modal' => $modal,
                    'pendapatan' => $pendapatan,
                    'keuntungan' => $keuntungan
                ];

                $currentDate->addDay();
            }
        } else {
            // Jika rentang bulan berbeda, ambil data per bulan
            $currentDate = $start->copy()->startOfMonth();
            while ($currentDate <= $end->copy()->endOfMonth()) {
                $monthQris = TransaksiQris::whereYear('timestamp', $currentDate->year)
                    ->whereMonth('timestamp', $currentDate->month)
                    ->get();

                $monthTunai = TransaksiTunai::whereYear('timestamp', $currentDate->year)
                    ->whereMonth('timestamp', $currentDate->month)
                    ->get();

                $modal = $monthQris->sum('total_cost_price') + $monthTunai->sum('total_cost_price');
                $pendapatan = $monthQris->sum('subtotal') + $monthTunai->sum('subtotal');
                $keuntungan = $pendapatan - $modal;

                $monthlyData[$currentDate->format('M Y')] = [
                    'modal' => $modal,
                    'pendapatan' => $pendapatan,
                    'keuntungan' => $keuntungan
                ];

                $currentDate->addMonth();
            }
        }

        // Jika rentang tanggal lebih dari satu hari, tambahkan total dari rentang yang dihitung sebelumnya
        if ($startDate != $endDate) {
            $monthlyData['Total'] = [
                'modal' => $rangeModal,
                'pendapatan' => $rangePendapatan,
                'keuntungan' => $rangeKeuntungan
            ];
        }

        return $monthlyData;
    }

    private function getPresetDates($preset)
    {
        $now = Carbon::now();
        
        switch ($preset) {
            case 'today':
                return [
                    'start' => $now->format('Y-m-d'),
                    'end' => $now->format('Y-m-d')
                ];
            case 'yesterday':
                return [
                    'start' => $now->copy()->subDay()->format('Y-m-d'),
                    'end' => $now->copy()->subDay()->format('Y-m-d')
                ];
            case 'last7days':
                return [
                    'start' => $now->copy()->subDays(6)->format('Y-m-d'),
                    'end' => $now->format('Y-m-d')
                ];
            case 'last30days':
                return [
                    'start' => $now->copy()->subDays(29)->format('Y-m-d'),
                    'end' => $now->format('Y-m-d')
                ];
            case 'thisMonth':
                return [
                    'start' => $now->copy()->startOfMonth()->format('Y-m-d'),
                    'end' => $now->copy()->endOfMonth()->format('Y-m-d')
                ];
            case 'lastMonth':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth()->format('Y-m-d'),
                    'end' => $now->copy()->subMonth()->endOfMonth()->format('Y-m-d')
                ];
            default:
                return [
                    'start' => $now->format('Y-m-d'),
                    'end' => $now->format('Y-m-d')
                ];
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
