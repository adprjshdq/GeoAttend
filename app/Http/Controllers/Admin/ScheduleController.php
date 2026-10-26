<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Schedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Schedule::with(['course', 'lecturer.user']);

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }

        if ($request->filled('day')) {
            $query->where('day_of_week', $request->input('day'));
        }

        $schedules = $query->latest('id')->paginate(10)->withQueryString();
        $courses = Course::all();

        return view('admin.schedules.index', compact('schedules', 'courses'));
    }

    public function create(): View
    {
        $courses = Course::all();
        $lecturers = Lecturer::with('user')->get();

        return view('admin.schedules.create', compact('courses', 'lecturers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'lecturer_id' => ['required', 'exists:lecturers,id'],
            'day_of_week' => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['required', 'string', 'max:100'],
            'location_latitude' => ['required', 'numeric', 'between:-90,90'],
            'location_longitude' => ['required', 'numeric', 'between:-180,180'],
            'geofence_radius' => ['required', 'integer', 'min:10', 'max:1000'],
        ]);

        $schedule = Schedule::create($validated);

        AuditLog::log(
            action: 'SCHEDULE_CREATED',
            entityType: 'Schedule',
            entityId: $schedule->id,
            oldValues: null,
            newValues: ['room' => $schedule->room, 'day' => $schedule->day_of_week],
            reason: 'Administrator membuat jadwal perkuliahan baru'
        );

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal perkuliahan berhasil ditambahkan.');
    }

    public function edit(Schedule $schedule): View
    {
        $courses = Course::all();
        $lecturers = Lecturer::with('user')->get();

        return view('admin.schedules.edit', compact('schedule', 'courses', 'lecturers'));
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'lecturer_id' => ['required', 'exists:lecturers,id'],
            'day_of_week' => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'room' => ['required', 'string', 'max:100'],
            'location_latitude' => ['required', 'numeric', 'between:-90,90'],
            'location_longitude' => ['required', 'numeric', 'between:-180,180'],
            'geofence_radius' => ['required', 'integer', 'min:10', 'max:1000'],
        ]);

        $schedule->update($validated);

        AuditLog::log(
            action: 'SCHEDULE_UPDATED',
            entityType: 'Schedule',
            entityId: $schedule->id,
            oldValues: null,
            newValues: ['room' => $schedule->room, 'day' => $schedule->day_of_week],
            reason: 'Administrator memperbarui jadwal perkuliahan'
        );

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal perkuliahan berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $id = $schedule->id;
        $room = $schedule->room;

        $schedule->delete();

        AuditLog::log(
            action: 'SCHEDULE_DELETED',
            entityType: 'Schedule',
            entityId: $id,
            oldValues: ['room' => $room],
            newValues: null,
            reason: 'Administrator menghapus jadwal perkuliahan'
        );

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal perkuliahan telah berhasil dihapus.');
    }
}
