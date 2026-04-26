<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalendarRequest;
use App\Http\Resources\CalendarResource;
use App\Models\Calendar;

class CalendarController extends Controller
{
    public function index()
    {
        return CalendarResource::collection(Calendar::all());
    }

    public function store(CalendarRequest $request)
    {
        return new CalendarResource(Calendar::create($request->validated()));
    }

    public function show(Calendar $calendar)
    {
        return new CalendarResource($calendar);
    }

    public function update(CalendarRequest $request, Calendar $calendar)
    {
        $calendar->update($request->validated());

        return new CalendarResource($calendar);
    }

    public function destroy(Calendar $calendar)
    {
        $calendar->delete();

        return response()->json();
    }
}
