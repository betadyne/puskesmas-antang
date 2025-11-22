<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Registration;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Generate daily queue report
     */
    public function dailyReport(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date|before_or_equal:today',
            'poli_id' => 'nullable|exists:polis,id',
        ]);

        $user = $request->user();
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $poliId = $request->poli_id;

        // If petugas, only show their poli
        if (!$user->hasRole('admin') && $user->poli_id) {
            $poliId = $user->poli_id;
        }

        $query = Queue::with(['registration.patient', 'poli', 'petugas'])
            ->whereHas('registration', function ($query) use ($date) {
                $query->where('tanggal_daftar', $date->format('Y-m-d'));
            });

        if ($poliId) {
            $query->where('poli_id', $poliId);
        }

        $queues = $query->orderBy('poli_id')
            ->orderBy('created_at')
            ->get();

        // Calculate statistics
        $stats = [
            'total_queues' => $queues->count(),
            'total_waiting' => $queues->where('status', 'menunggu')->count(),
            'total_called' => $queues->where('status', 'dipanggil')->count(),
            'total_being_served' => $queues->where('status', 'sedang dilayani')->count(),
            'total_finished' => $queues->where('status', 'selesai')->count(),
            'total_skipped' => $queues->where('status', 'dilewati')->count(),
            'date' => $date->format('d F Y'),
            'poli' => $poliId ? \App\Models\Poli::find($poliId) : 'Semua Poli',
        ];

        // Calculate wait times
        $finishedQueues = $queues->filter(function ($queue) {
            return $queue->status === 'selesai' && $queue->wait_time;
        });

        if ($finishedQueues->isNotEmpty()) {
            $stats['avg_wait_time'] = round($finishedQueues->avg('wait_time'), 2);
            $stats['min_wait_time'] = $finishedQueues->min('wait_time');
            $stats['max_wait_time'] = $finishedQueues->max('wait_time');
        } else {
            $stats['avg_wait_time'] = 0;
            $stats['min_wait_time'] = 0;
            $stats['max_wait_time'] = 0;
        }

        // Calculate service times
        $servedQueues = $queues->filter(function ($queue) {
            return $queue->status === 'selesai' && $queue->service_time;
        });

        if ($servedQueues->isNotEmpty()) {
            $stats['avg_service_time'] = round($servedQueues->avg('service_time'), 2);
            $stats['min_service_time'] = $servedQueues->min('service_time');
            $stats['max_service_time'] = $servedQueues->max('service_time');
        } else {
            $stats['avg_service_time'] = 0;
            $stats['min_service_time'] = 0;
            $stats['max_service_time'] = 0;
        }

        // Group by poli for detailed breakdown
        $byPoli = $queues->groupBy('poli_id')->map(function ($poliQueues, $poliId) {
            $poli = \App\Models\Poli::find($poliId);
            return [
                'poli' => $poli,
                'statistics' => [
                    'total' => $poliQueues->count(),
                    'finished' => $poliQueues->where('status', 'selesai')->count(),
                    'skipped' => $poliQueues->where('status', 'dilewati')->count(),
                    'avg_wait_time' => round($poliQueues->whereNotNull('wait_time')->avg('wait_time') ?? 0, 2),
                ]
            ];
        });

        return response()->json([
            'message' => 'Daily report generated successfully',
            'data' => [
                'statistics' => $stats,
                'by_poli' => $byPoli,
                'queues' => $queues->map(function ($queue) {
                    return [
                        'nomor_antrean' => $queue->nomor_antrean,
                        'pasien' => $queue->registration?->patient?->nama,
                        'poli' => $queue->poli?->nama_poli,
                        'status' => $queue->status,
                        'wait_time' => $queue->wait_time,
                        'service_time' => $queue->service_time,
                        'created_at' => $queue->created_at->format('H:i'),
                        'called_at' => $queue->called_at?->format('H:i'),
                        'served_at' => $queue->served_at?->format('H:i'),
                        'finished_at' => $queue->finished_at?->format('H:i'),
                    ];
                }),
            ]
        ]);
    }

    /**
     * Generate statistical report (weekly/monthly)
     */
    public function statistics(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date|before_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date|before_or_equal:today',
            'period' => 'nullable|in:week,month,custom',
            'poli_id' => 'nullable|exists:polis,id',
        ]);

        $user = $request->user();
        $period = $request->period ?? 'week';
        $poliId = $request->poli_id;

        // If petugas, only show their poli
        if (!$user->hasRole('admin') && $user->poli_id) {
            $poliId = $user->poli_id;
        }

        // Determine date range
        if ($period === 'week') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif ($period === 'month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } else {
            $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
            $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
        }

        // Get registrations in date range
        $query = Queue::with(['registration', 'poli'])
            ->whereHas('registration', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_daftar', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            });

        if ($poliId) {
            $query->where('poli_id', $poliId);
        }

        $queues = $query->get();

        // Daily breakdown
        $periodData = [];
        $periodRange = CarbonPeriod::create($startDate, $endDate);
        
        foreach ($periodRange as $date) {
            $dayQueues = $queues->filter(function ($queue) use ($date) {
                return $queue->registration->tanggal_daftar === $date->format('Y-m-d');
            });

            $periodData[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('l'),
                'total' => $dayQueues->count(),
                'finished' => $dayQueues->where('status', 'selesai')->count(),
                'skipped' => $dayQueues->where('status', 'dilewati')->count(),
                'avg_wait_time' => round($dayQueues->whereNotNull('wait_time')->avg('wait_time') ?? 0, 2),
            ];
        }

        // Overall statistics
        $overallStats = [
            'period' => [
                'start' => $startDate->format('d F Y'),
                'end' => $endDate->format('d F Y'),
                'days' => $periodRange->count(),
            ],
            'total_registrations' => $queues->count(),
            'total_finished' => $queues->where('status', 'selesai')->count(),
            'total_skipped' => $queues->where('status', 'dilewati')->count(),
            'completion_rate' => $queues->count() > 0 ? 
                round(($queues->where('status', 'selesai')->count() / $queues->count()) * 100, 2) : 0,
            'avg_wait_time' => round($queues->whereNotNull('wait_time')->avg('wait_time') ?? 0, 2),
            'peak_day' => $this->findPeakDay($periodData),
        ];

        // By poli statistics
        $byPoliStats = $queues->groupBy('poli_id')->map(function ($poliQueues, $poliId) {
            $poli = \App\Models\Poli::find($poliId);
            return [
                'poli' => $poli,
                'total' => $poliQueues->count(),
                'finished' => $poliQueues->where('status', 'selesai')->count(),
                'completion_rate' => $poliQueues->count() > 0 ? 
                    round(($poliQueues->where('status', 'selesai')->count() / $poliQueues->count()) * 100, 2) : 0,
                'avg_wait_time' => round($poliQueues->whereNotNull('wait_time')->avg('wait_time') ?? 0, 2),
            ];
        });

        return response()->json([
            'message' => 'Statistics generated successfully',
            'data' => [
                'overall' => $overallStats,
                'daily_breakdown' => $periodData,
                'by_poli' => $byPoliStats,
            ]
        ]);
    }

    /**
     * Find peak day from period data
     */
    private function findPeakDay($periodData)
    {
        if (empty($periodData)) {
            return null;
        }

        $peakDay = collect($periodData)->sortByDesc('total')->first();
        return [
            'date' => $peakDay['date'],
            'day' => $peakDay['day'],
            'total' => $peakDay['total'],
        ];
    }

    /**
     * Generate waiting time analysis report
     */
    public function waitingTimeAnalysis(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date|before_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date|before_or_equal:today',
            'poli_id' => 'nullable|exists:polis,id',
        ]);

        $user = $request->user();
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
        $poliId = $request->poli_id;

        // If petugas, only show their poli
        if (!$user->hasRole('admin') && $user->poli_id) {
            $poliId = $user->poli_id;
        }

        $query = Queue::with(['poli'])
            ->whereHas('registration', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_daftar', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            })
            ->whereNotNull('wait_time');

        if ($poliId) {
            $query->where('poli_id', $poliId);
        }

        $queues = $query->get();

        // Wait time categories
        $categories = [
            '0-15' => $queues->filter(function ($queue) { return $queue->wait_time <= 15; })->count(),
            '16-30' => $queues->filter(function ($queue) { return $queue->wait_time > 15 && $queue->wait_time <= 30; })->count(),
            '31-45' => $queues->filter(function ($queue) { return $queue->wait_time > 30 && $queue->wait_time <= 45; })->count(),
            '46-60' => $queues->filter(function ($queue) { return $queue->wait_time > 45 && $queue->wait_time <= 60; })->count(),
            '60+' => $queues->filter(function ($queue) { return $queue->wait_time > 60; })->count(),
        ];

        $analysis = [
            'total_analyzed' => $queues->count(),
            'avg_wait_time' => round($queues->avg('wait_time') ?? 0, 2),
            'median_wait_time' => $this->calculateMedian($queues->pluck('wait_time')->toArray()),
            'min_wait_time' => $queues->min('wait_time'),
            'max_wait_time' => $queues->max('wait_time'),
            'categories' => $categories,
            'over_60_percentage' => $queues->count() > 0 ? 
                round(($categories['60+'] / $queues->count()) * 100, 2) : 0,
        ];

        return response()->json([
            'message' => 'Waiting time analysis completed',
            'data' => $analysis
        ]);
    }

    /**
     * Calculate median value from array
     */
    private function calculateMedian($array)
    {
        if (empty($array)) {
            return 0;
        }

        sort($array);
        $count = count($array);
        $middle = floor($count / 2);

        if ($count % 2 == 0) {
            return ($array[$middle - 1] + $array[$middle]) / 2;
        }

        return $array[$middle];
    }
}
