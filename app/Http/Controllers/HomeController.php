<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function getFinancialYear(Request $request)
    {
        $request->validate([
            'country' => 'required',
            'year' => 'required'
        ]);

        $country = $request->input('country');
        $yearInput = $request->input('year');

        if ($country === 'uk') {
            [$startYear, $endYear] = explode('-', $yearInput);
            $start = Carbon::create($startYear, 4, 6);
            $end = Carbon::create("20$endYear", 4, 5);
            $countryCode = 'GB';
        } else {
            $start = Carbon::create($yearInput, 1, 1);
            $end = Carbon::create($yearInput, 12, 31);
            $countryCode = 'IE';
        }

        $originalStart = $start->copy();
        $originalEnd = $end->copy();

        $skippedStartNote = '';
        if ($start->isSaturday()) {
            $start->addDays(2);
            $skippedStartNote = $originalStart->format('jS M') .' and '. $originalStart->copy()->addDay()->format('jS M')  . ' are Saturday and Sunday';
        } elseif ($start->isSunday()) {
            $start->addDay();
            $skippedStartNote = $originalStart->format('jS M') . ' is a Sunday';
        }

        $skippedEndNote = '';
        if ($end->isSaturday()) {
            $end->subDay();
            $skippedEndNote = $originalEnd->format('jS M') . ' is a Sunday';
        } elseif ($end->isSunday()) {
            $end->subDays(2);
            $skippedEndNote = $originalEnd->copy()->subDay()->format('jS M') .' and '. $originalEnd->format('jS M')  . ' are Saturday and Sunday';
        }

        $holidays = $this->getPublicHolidays($start, $end, $countryCode);

        $filtered = collect($holidays)->filter(function ($holiday) use ($start, $end) {
            $date = Carbon::parse($holiday['date']);
            return $date->between($start, $end) && !$date->isWeekend();
        })->values();

        return response()->json([
            'financial_year_start' => $start->format('jS F Y') . ($skippedStartNote ? " ({$skippedStartNote})" : ''),
            'financial_year_end' => $end->format('jS F Y') . ($skippedEndNote ? " ({$skippedEndNote})" : ''),
            'holidays' => $filtered,
        ]);
    }

    private function getPublicHolidays(Carbon $start, Carbon $end, $countryCode)
    {
        $holidays = [];

        foreach (range($start->year, $end->year) as $year) {
            $response = Http::get("https://date.nager.at/api/v3/PublicHolidays/{$year}/{$countryCode}");

            if ($response->successful()) {
                $holidays = array_merge($holidays, $response->json());
            }
        }

        return $holidays;
    }
}

