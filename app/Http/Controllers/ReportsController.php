<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\SchoolTerm;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $activeSchoolYear = SchoolYear::query()
            ->where('is_active', true)
            ->first();

        $schoolYears = SchoolYear::query()
            ->where('is_active', true)
            ->orderByDesc('name')
            ->get();

        $selectedSchoolYearId = (int) ($request->input('school_year_id') ?: $activeSchoolYear?->id);

        $terms = SchoolTerm::query()
            ->when($selectedSchoolYearId, function ($query) use ($selectedSchoolYearId) {
                $query->where('school_year_id', $selectedSchoolYearId);
            })
            ->orderBy('term_order')
            ->get();

        $activeTerm = $terms->firstWhere('is_active', true);

        $selectedTermId = (int) ($request->input('term_id') ?: $activeTerm?->id);

        $gradeLevels = GradeLevel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $selectedGradeLevelId = $request->filled('grade_level_id')
            ? (int) $request->input('grade_level_id')
            : null;

        $sections = Section::query()
            ->with(['gradeLevel', 'pathway', 'shiftType'])
            ->where('is_active', true)
            ->when($selectedSchoolYearId, function ($query) use ($selectedSchoolYearId) {
                $query->where('school_year_id', $selectedSchoolYearId);
            })
            ->when($selectedGradeLevelId, function ($query) use ($selectedGradeLevelId) {
                $query->where('grade_level_id', $selectedGradeLevelId);
            })
            ->orderBy('name')
            ->get();

        $selectedSectionId = $request->filled('section_id')
            ? (int) $request->input('section_id')
            : null;

        $teachers = Teacher::query()
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $selectedTeacherId = $request->filled('teacher_id')
            ? (int) $request->input('teacher_id')
            : null;

        $rooms = Room::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedRoomId = $request->filled('room_id')
            ? (int) $request->input('room_id')
            : null;

        $reportType = $request->input('report_type', 'section_schedule');

        $reportData = match ($reportType) {
            'teacher_load' => $this->buildTeacherLoadReport($selectedTermId),
            'teacher_schedule' => $this->buildTeacherScheduleReport($selectedTermId, $selectedTeacherId),
            'room_utilization' => $this->buildRoomUtilizationReport($selectedTermId, $selectedRoomId),
            'subject_hours_summary' => $this->buildSubjectHoursSummaryReport($selectedTermId, $selectedSectionId),
            default => $this->buildSectionScheduleReport($selectedTermId, $selectedSectionId),
        };

        return view('reports.index', [
            'pageTitle' => 'Reports',
            'pageSubtitle' => 'Preview, print, and export scheduling reports',
            'reportType' => $reportType,
            'reportData' => $reportData,

            'schoolYears' => $schoolYears,
            'terms' => $terms,
            'gradeLevels' => $gradeLevels,
            'sections' => $sections,
            'teachers' => $teachers,
            'rooms' => $rooms,

            'selectedSchoolYearId' => $selectedSchoolYearId,
            'selectedTermId' => $selectedTermId,
            'selectedGradeLevelId' => $selectedGradeLevelId,
            'selectedSectionId' => $selectedSectionId,
            'selectedTeacherId' => $selectedTeacherId,
            'selectedRoomId' => $selectedRoomId,
        ]);
    }

    public function print(Request $request)
    {
        return $this->index($request);
    }

    public function exportPdf(Request $request)
    {
        return response()->json([
            'message' => 'PDF export route is ready. Connect this in the next step using dompdf or your preferred PDF package.',
        ]);
    }

    public function exportExcel(Request $request)
    {
        return response()->json([
            'message' => 'Excel export route is ready. Connect this in the next step using Laravel Excel or your preferred export package.',
        ]);
    }

    private function buildSectionScheduleReport(?int $termId, ?int $sectionId): array
    {
        $section = Section::query()
            ->with(['gradeLevel', 'pathway', 'shiftType', 'room'])
            ->find($sectionId);

        $timeSlots = collect();

        $schedules = collect();

        $grid = [];

        if ($section && $termId) {
            $timeSlots = TimeSlot::query()
                ->where('shift_type_id', $section->shift_type_id)
                ->where('is_active', true)
                ->orderBy('slot_order')
                ->get();

            $schedules = Schedule::query()
                ->with(['subject', 'teacher', 'room', 'timeSlot'])
                ->where('school_term_id', $termId)
                ->where('section_id', $section->id)
                ->get();

            $grid = $this->buildWeeklyGrid($timeSlots, $schedules, 'section');
        }

        return [
            'title' => 'Section Schedule Report',
            'section' => $section,
            'timeSlots' => $timeSlots,
            'schedules' => $schedules,
            'grid' => $grid,
        ];
    }

    private function buildTeacherLoadReport(?int $termId): array
    {
        $teachers = Teacher::query()
            ->where('is_active', true)
            ->with([
                'schedules' => function ($query) use ($termId) {
                    $query->when($termId, function ($subQuery) use ($termId) {
                        $subQuery->where('school_term_id', $termId);
                    })->with(['subject', 'section']);
                },
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function (Teacher $teacher) {
                $assignedHours = (float) $teacher->schedules->sum(function ($schedule) {
                    return (float) ($schedule->subject?->weekly_hours ?? 0);
                });

                $maxLoad = (float) $teacher->maximum_weekly_load;
                $remainingHours = $maxLoad - $assignedHours;

                $status = 'normal';

                if ($assignedHours > $maxLoad) {
                    $status = 'overload';
                } elseif ($assignedHours < $maxLoad && $assignedHours > 0) {
                    $status = 'underload';
                } elseif ($assignedHours == 0.0) {
                    $status = 'unassigned';
                }

                return [
                    'teacher' => $teacher,
                    'assigned_hours' => $assignedHours,
                    'max_load' => $maxLoad,
                    'remaining_hours' => $remainingHours,
                    'status' => $status,
                    'total_schedule_entries' => $teacher->schedules->count(),
                ];
            });

        return [
            'title' => 'Teacher Load Report',
            'rows' => $teachers,
        ];
    }

    private function buildTeacherScheduleReport(?int $termId, ?int $teacherId): array
    {
        $teacher = Teacher::query()->find($teacherId);

        $schedules = collect();
        $timeSlots = collect();
        $grid = [];

        if ($teacher && $termId) {
            $schedules = Schedule::query()
                ->with(['subject', 'section.gradeLevel', 'room', 'timeSlot'])
                ->where('school_term_id', $termId)
                ->where('teacher_id', $teacher->id)
                ->get();

            $timeSlots = TimeSlot::query()
                ->where('is_active', true)
                ->orderBy('shift_type_id')
                ->orderBy('slot_order')
                ->get();

            $grid = $this->buildWeeklyGrid($timeSlots, $schedules, 'teacher');
        }

        return [
            'title' => 'Teacher Schedule Report',
            'teacher' => $teacher,
            'timeSlots' => $timeSlots,
            'schedules' => $schedules,
            'grid' => $grid,
        ];
    }

    private function buildRoomUtilizationReport(?int $termId, ?int $roomId): array
    {
        $room = Room::query()->find($roomId);

        $schedules = collect();
        $timeSlots = collect();
        $grid = [];

        if ($room && $termId) {
            $schedules = Schedule::query()
                ->with(['subject', 'teacher', 'section.gradeLevel', 'timeSlot'])
                ->where('school_term_id', $termId)
                ->where('room_id', $room->id)
                ->get();

            $timeSlots = TimeSlot::query()
                ->where('is_active', true)
                ->orderBy('shift_type_id')
                ->orderBy('slot_order')
                ->get();

            $grid = $this->buildWeeklyGrid($timeSlots, $schedules, 'room');
        }

        $utilizationRows = $schedules
            ->groupBy('day_of_week')
            ->map(function (Collection $items, string $day) {
                return [
                    'day' => ucfirst($day),
                    'entries' => $items->count(),
                ];
            })
            ->values();

        return [
            'title' => 'Room Utilization Report',
            'room' => $room,
            'timeSlots' => $timeSlots,
            'schedules' => $schedules,
            'grid' => $grid,
            'utilizationRows' => $utilizationRows,
        ];
    }

    private function buildSubjectHoursSummaryReport(?int $termId, ?int $sectionId): array
    {
        $section = Section::query()
            ->with(['gradeLevel', 'pathway', 'shiftType'])
            ->find($sectionId);

        $rows = collect();

        if ($section && $termId) {
            $rows = Schedule::query()
                ->with(['subject'])
                ->where('school_term_id', $termId)
                ->where('section_id', $section->id)
                ->get()
                ->groupBy('subject_id')
                ->map(function (Collection $items) {
                    $subject = $items->first()?->subject;

                    $scheduledHours = $items->count();

                    return [
                        'subject' => $subject,
                        'scheduled_hours' => $scheduledHours,
                        'expected_weekly_hours' => (float) ($subject?->weekly_hours ?? 0),
                        'difference' => $scheduledHours - (float) ($subject?->weekly_hours ?? 0),
                    ];
                })
                ->sortBy(fn ($row) => $row['subject']?->name)
                ->values();
        }

        return [
            'title' => 'Subject Hours Summary',
            'section' => $section,
            'rows' => $rows,
        ];
    }

    private function buildWeeklyGrid(Collection $timeSlots, Collection $schedules, string $context): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        $grid = [];

        foreach ($timeSlots as $timeSlot) {
            $row = [
                'time_slot' => $timeSlot,
                'days' => [],
            ];

            foreach ($days as $day) {
                $entry = $schedules->first(function ($schedule) use ($timeSlot, $day) {
                    return (int) $schedule->time_slot_id === (int) $timeSlot->id
                        && $schedule->day_of_week === $day;
                });

                $row['days'][$day] = $entry ? $this->formatGridEntry($entry, $context) : null;
            }

            $grid[] = $row;
        }

        return $grid;
    }

    private function formatGridEntry(Schedule $schedule, string $context): array
    {
        return match ($context) {
            'teacher' => [
                'subject' => $schedule->subject?->name,
                'section' => $schedule->section?->name,
                'room' => $schedule->room?->name,
            ],
            'room' => [
                'subject' => $schedule->subject?->name,
                'teacher' => $schedule->teacher?->full_name,
                'section' => $schedule->section?->name,
            ],
            default => [
                'subject' => $schedule->subject?->name,
                'teacher' => $schedule->teacher?->full_name,
                'room' => $schedule->room?->name,
            ],
        };
    }
}