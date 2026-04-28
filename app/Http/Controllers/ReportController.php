<?php

namespace App\Http\Controllers;

use App\Models\Zaka;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportZakaExport;
use App\Exports\ReportJumuiyaExport;
use App\Exports\ReportKandaExport;
use App\Exports\ReportMwanajumuiyaExport;

class ReportController extends Controller
{
    public function mwanajumuiya(Request $request)
    {
        $mwanajumuiyaId = $request->input('mwanajumuiya_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $members = \App\Models\Mwanajumuiya::with('jumuiya')
            ->orderBy('jina_la_mwanajumuiya')
            ->get();

        $zakas = collect();
        $total = 0;

        if ($mwanajumuiyaId) {
            $query = Zaka::with('mwanajumuiya.jumuiya')
                ->where('mwanajumuiya_id', $mwanajumuiyaId);

            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
                $query->whereBetween('paid_at', [$start, $end]);
            }

            $zakas = $query->orderBy('paid_at', 'desc')->get();
            $total = $zakas->sum('kiasi');
        }

        return view('reports.mwanajumuiya', compact(
            'members',
            'zakas',
            'total',
            'mwanajumuiyaId',
            'startDate',
            'endDate'
        ));
    }
    public function zaka(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $jumuiyaId = $request->input('jumuiya_id');

        $query = Zaka::with('mwanajumuiya.jumuiya');

        if ($jumuiyaId) {
            $query->whereHas('mwanajumuiya', function ($q) use ($jumuiyaId) {
                $q->where('jumuiya_id', $jumuiyaId);
            });
        }

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('paid_at', [$start, $end]);
        } else {
            $query->whereYear('paid_at', $year);
            if ($month) {
                $query->whereMonth('paid_at', $month);
            }
        }

        $zakas = $query->orderBy('paid_at', 'desc')->get();
        $total = $zakas->sum('kiasi');
        $jumuiyas = \App\Models\Jumuiya::orderBy('jina_la_jumuiya')->get();

        return view('reports.zaka', compact('zakas', 'total', 'year', 'month', 'startDate', 'endDate', 'jumuiyas', 'jumuiyaId'));
    }

    public function jumuiya(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('jumuiyas')
            ->join('mwanajumuiyas', 'jumuiyas.id', '=', 'mwanajumuiyas.jumuiya_id')
            ->join('zakas', 'mwanajumuiyas.id', '=', 'zakas.mwanajumuiya_id')
            ->select('jumuiyas.jina_la_jumuiya', DB::raw('SUM(zakas.kiasi) as total'))
            ->groupBy('jumuiyas.id', 'jumuiyas.jina_la_jumuiya')
            ->orderByDesc('total');

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('zakas.paid_at', [$start, $end]);
        } else {
            $query->whereYear('zakas.paid_at', $year);
            if ($month) {
                $query->whereMonth('zakas.paid_at', $month);
            }
        }

        $data = $query->get();
        $total = $data->sum('total');

        return view('reports.jumuiya', compact('data', 'total', 'year', 'month', 'startDate', 'endDate'));
    }

    public function kanda(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('kandas')
            ->join('jumuiyas', 'kandas.id', '=', 'jumuiyas.kanda_id')
            ->join('mwanajumuiyas', 'jumuiyas.id', '=', 'mwanajumuiyas.jumuiya_id')
            ->join('zakas', 'mwanajumuiyas.id', '=', 'zakas.mwanajumuiya_id')
            ->select('kandas.jina_la_kanda', DB::raw('SUM(zakas.kiasi) as total'))
            ->groupBy('kandas.id', 'kandas.jina_la_kanda')
            ->orderByDesc('total');

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('zakas.paid_at', [$start, $end]);
        } else {
            $query->whereYear('zakas.paid_at', $year);
            if ($month) {
                $query->whereMonth('zakas.paid_at', $month);
            }
        }

        $data = $query->get();
        $total = $data->sum('total');

        return view('reports.kanda', compact('data', 'total', 'year', 'month', 'startDate', 'endDate'));
    }

    public function zakaExport(Request $request)
    {
        return Excel::download(new ReportZakaExport(
            $request->input('year'),
            $request->input('month'),
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('jumuiya_id')
        ), 'zaka_report.xlsx');
    }

    public function jumuiyaExport(Request $request)
    {
        return Excel::download(new ReportJumuiyaExport(
            $request->input('year'),
            $request->input('month'),
            $request->input('start_date'),
            $request->input('end_date')
        ), 'jumuiya_report.xlsx');
    }

    public function kandaExport(Request $request)
    {
        return Excel::download(new ReportKandaExport(
            $request->input('year'),
            $request->input('month'),
            $request->input('start_date'),
            $request->input('end_date')
        ), 'kanda_report.xlsx');
    }

    public function mwanajumuiyaExport(Request $request)
    {
        return Excel::download(new ReportMwanajumuiyaExport(
            $request->input('mwanajumuiya_id'),
            $request->input('start_date'),
            $request->input('end_date')
        ), 'mwanajumuiya_report.xlsx');
    }
}
