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
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchedulingController extends Controller
{
    public function index(Request $request): View
    {
        $activeSchoolYear = SchoolYear::query()
            ->where('is_active', true)
            ->first();

        $activeTerm = SchoolTerm::with('schoolYear')
            ->where('is_active', true)
            ->first();

        $gradeLevels = GradeLevel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $selectedGradeLevelId = $request->filled('grade_level_id')
            ? (int) $request->get('grade_level_id')
            : (int) ($gradeLevels->first()?->id);

        $sections = Section::query()
            ->with(['gradeLevel', 'pathway', 'shiftType', 'room'])
            ->where('is_active', true)
            ->when($activeSchoolYear, function ($query) use ($activeSchoolYear) {
                $query->where('school_year_id', $activeSchoolYear->id);
            })
            ->when($selectedGradeLevelId, function ($query) use ($selectedGradeLevelId) {
                $query->where('grade_level_id', $selectedGradeLevelId);
            })
            ->orderBy('name')
            ->get();

        $selectedSectionId = $request->filled('section_id')
            ? (int) $request->get('section_id')
            : (int) ($sections->first()?->id);

        $selectedSection = $sections->firstWhere('id', $selectedSectionId);

        if (!$selectedSection && $selectedSectionId) {
            $selectedSection = Section::query()
                ->with(['gradeLevel', 'pathway', 'shiftType', 'room'])
                ->where('is_active', true)
                ->when($activeSchoolYear, function ($query) use ($activeSchoolYear) {
                    $query->where('school_year_id', $activeSchoolYear->id);
                })
                ->find($selectedSectionId);
        }

        $timeSlots = collect();

        if ($selectedSection?->shift_type_id) {
            $timeSlots = TimeSlot::query()
                ->where('shift_type_id', $selectedSection->shift_type_id)
                ->where('is_active', true)
                ->orderBy('slot_order')
                ->get();
        }

        $subjects = collect();

        if ($selectedSection && $activeTerm) {
            $mappedSubjects = Subject::query()
                ->where('is_active', true)
                ->whereHas('pathwaySubjects', function ($query) use ($selectedSection, $activeTerm) {
                    $query->where('pathway_id', $selectedSection->pathway_id)
                        ->where('is_active', true)
                        ->where(function ($subQuery) use ($selectedSection) {
                            $subQuery->whereNull('grade_level_id')
                                ->orWhere('grade_level_id', $selectedSection->grade_level_id);
                        })
                        ->where(function ($subQuery) use ($activeTerm) {
                            $subQuery->whereNull('school_term_id')
                                ->orWhere('school_term_id', $activeTerm->id);
                        });
                })
                ->orderBy('name')
                ->get();

            $subjects = $mappedSubjects->isNotEmpty()
                ? $mappedSubjects
                : Subject::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
        }
        $teachers = Teacher::query()
            ->with([
                'teacherSubjects.subject',
                'schedules' => function ($query) use ($activeTerm) {
                    $query->with(['subject', 'room', 'section', 'timeSlot']);

                    if ($activeTerm) {
                        $query->where('school_term_id', $activeTerm->id);
                    }
                },
            ])
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $rooms = Room::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $scheduleEntries = collect();

        if ($selectedSection && $activeTerm) {
            $scheduleEntries = Schedule::query()
                ->with(['subject', 'teacher', 'room', 'timeSlot', 'section'])
                ->where('school_term_id', $activeTerm->id)
                ->where('section_id', $selectedSection->id)
                ->get();
        }

        $daysOfWeek = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
        ];

        $scheduleMap = [];

        foreach ($scheduleEntries as $entry) {
            $scheduleMap[$entry->time_slot_id][$entry->day_of_week] = $entry;
        }

        $subjectHours = $subjects->map(function ($subject) use ($scheduleEntries) {
            $assignedHours = $scheduleEntries
                ->where('subject_id', $subject->id)
                ->sum(function ($schedule) {
                    if (!$schedule->timeSlot || $schedule->timeSlot->is_break) {
                        return 0;
                    }

                    return $this->timeSlotHours($schedule->timeSlot);
                });

            return [
                'subject' => $subject,
                'required_hours' => (float) $subject->weekly_hours,
                'assigned_hours' => round((float) $assignedHours, 2),
                'remaining_hours' => round((float) $subject->weekly_hours - (float) $assignedHours, 2),
            ];
        });

        $sectionTeachers = $teachers->map(function ($teacher) {
            $assignedHours = $teacher->schedules->sum(function ($schedule) {
                if (!$schedule->timeSlot || $schedule->timeSlot->is_break) {
                    return 0;
                }

                return $this->timeSlotHours($schedule->timeSlot);
            });

            return [
                'teacher' => $teacher,
                'assigned_hours' => round((float) $assignedHours, 2),
                'remaining_hours' => round((float) $teacher->maximum_weekly_load - (float) $assignedHours, 2),
            ];
        });

        return view('scheduling.index', [
            'activeSchoolYear' => $activeSchoolYear,
            'activeTerm' => $activeTerm,
            'gradeLevels' => $gradeLevels,
            'sections' => $sections,
            'selectedGradeLevelId' => $selectedGradeLevelId,
            'selectedSectionId' => $selectedSectionId,
            'selectedSection' => $selectedSection,
            'timeSlots' => $timeSlots,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'rooms' => $rooms,
            'scheduleEntries' => $scheduleEntries,
            'scheduleMap' => $scheduleMap,
            'daysOfWeek' => $daysOfWeek,
            'subjectHours' => $subjectHours,
            'sectionTeachers' => $sectionTeachers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSchedule($request);

        $this->ensureSectionBelongsToActivePeriod($validated['section_id'], $validated['school_term_id']);
        $this->ensureTimeSlotMatchesSectionShift($validated['section_id'], $validated['time_slot_id']);
        $this->ensureSubjectAllowedForSection($validated['section_id'], $validated['school_term_id'], $validated['subject_id']);
        $this->ensureTeacherAssignedToSubject($validated['teacher_id'], $validated['subject_id']);
        $this->ensureTeacherHasNoConflict(
            $validated['school_term_id'],
            $validated['teacher_id'],
            $validated['day_of_week'],
            $validated['time_slot_id']
        );
        $this->ensureSectionHasNoDuplicateSlot(
            $validated['school_term_id'],
            $validated['section_id'],
            $validated['day_of_week'],
            $validated['time_slot_id']
        );

        Schedule::create($validated);

        $section = Section::find($validated['section_id']);

        return redirect()
            ->route('scheduling.index', [
                'grade_level_id' => $section?->grade_level_id,
                'section_id' => $validated['section_id'],
            ])
            ->with('success', 'Schedule entry created successfully.');
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $this->validateSchedule($request);

        $this->ensureSectionBelongsToActivePeriod($validated['section_id'], $validated['school_term_id']);
        $this->ensureTimeSlotMatchesSectionShift($validated['section_id'], $validated['time_slot_id']);
        $this->ensureSubjectAllowedForSection($validated['section_id'], $validated['school_term_id'], $validated['subject_id']);
        $this->ensureTeacherAssignedToSubject($validated['teacher_id'], $validated['subject_id']);
        $this->ensureTeacherHasNoConflict(
            $validated['school_term_id'],
            $validated['teacher_id'],
            $validated['day_of_week'],
            $validated['time_slot_id'],
            $schedule->id
        );
        $this->ensureSectionHasNoDuplicateSlot(
            $validated['school_term_id'],
            $validated['section_id'],
            $validated['day_of_week'],
            $validated['time_slot_id'],
            $schedule->id
        );

        $schedule->update($validated);

        $section = Section::find($validated['section_id']);

        return redirect()
            ->route('scheduling.index', [
                'grade_level_id' => $section?->grade_level_id,
                'section_id' => $validated['section_id'],
            ])
            ->with('success', 'Schedule entry updated successfully.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $sectionId = $schedule->section_id;
        $gradeLevelId = $schedule->section?->grade_level_id;

        $schedule->delete();

        return redirect()
            ->route('scheduling.index', [
                'grade_level_id' => $gradeLevelId,
                'section_id' => $sectionId,
            ])
            ->with('success', 'Schedule entry deleted successfully.');
    }

    private function validateSchedule(Request $request): array
    {
        return $request->validate([
            'school_term_id' => ['required', 'exists:school_terms,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'time_slot_id' => ['required', 'exists:time_slots,id'],
            'day_of_week' => ['required', Rule::in([
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
            ])],
        ]);
    }

    private function ensureSectionBelongsToActivePeriod(int $sectionId, int $schoolTermId): void
    {
        $section = Section::findOrFail($sectionId);
        $schoolTerm = SchoolTerm::findOrFail($schoolTermId);

        if ((int) $section->school_year_id !== (int) $schoolTerm->school_year_id) {
            abort(422, 'The selected section does not belong to the selected academic period.');
        }
    }

    private function ensureTimeSlotMatchesSectionShift(int $sectionId, int $timeSlotId): void
    {
        $section = Section::findOrFail($sectionId);
        $timeSlot = TimeSlot::findOrFail($timeSlotId);

        if ((int) $section->shift_type_id !== (int) $timeSlot->shift_type_id) {
            abort(422, 'The selected time slot does not belong to the section shift.');
        }
    }

    private function ensureSubjectAllowedForSection(int $sectionId, int $schoolTermId, int $subjectId): void
    {
        $section = Section::findOrFail($sectionId);

        $hasMappedSubjectsForSection = Subject::query()
            ->where('is_active', true)
            ->whereHas('pathwaySubjects', function ($query) use ($section, $schoolTermId) {
                $query->where('pathway_id', $section->pathway_id)
                    ->where('is_active', true)
                    ->where(function ($subQuery) use ($section) {
                        $subQuery->whereNull('grade_level_id')
                            ->orWhere('grade_level_id', $section->grade_level_id);
                    })
                    ->where(function ($subQuery) use ($schoolTermId) {
                        $subQuery->whereNull('school_term_id')
                            ->orWhere('school_term_id', $schoolTermId);
                    });
            })
            ->exists();

        if ($hasMappedSubjectsForSection) {
            $allowed = Subject::query()
                ->whereKey($subjectId)
                ->where('is_active', true)
                ->whereHas('pathwaySubjects', function ($query) use ($section, $schoolTermId) {
                    $query->where('pathway_id', $section->pathway_id)
                        ->where('is_active', true)
                        ->where(function ($subQuery) use ($section) {
                            $subQuery->whereNull('grade_level_id')
                                ->orWhere('grade_level_id', $section->grade_level_id);
                        })
                        ->where(function ($subQuery) use ($schoolTermId) {
                            $subQuery->whereNull('school_term_id')
                                ->orWhere('school_term_id', $schoolTermId);
                        });
                })
                ->exists();
        } else {
            $allowed = Subject::query()
                ->whereKey($subjectId)
                ->where('is_active', true)
                ->exists();
        }

        if (!$allowed) {
            abort(422, 'The selected subject is not valid for this section and term.');
        }
    }

    private function ensureTeacherAssignedToSubject(int $teacherId, int $subjectId): void
    {
        $assigned = Teacher::query()
            ->whereKey($teacherId)
            ->whereHas('teacherSubjects', function ($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })
            ->exists();

        if (!$assigned) {
            abort(422, 'The selected teacher is not assigned to this subject.');
        }
    }

    private function ensureTeacherHasNoConflict(
        int $schoolTermId,
        int $teacherId,
        string $dayOfWeek,
        int $timeSlotId,
        ?int $ignoreScheduleId = null
    ): void {
        $query = Schedule::query()
            ->where('school_term_id', $schoolTermId)
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where('time_slot_id', $timeSlotId);

        if ($ignoreScheduleId) {
            $query->where('id', '!=', $ignoreScheduleId);
        }

        if ($query->exists()) {
            abort(422, 'The selected teacher already has a class in this slot.');
        }
    }

    private function ensureSectionHasNoDuplicateSlot(
        int $schoolTermId,
        int $sectionId,
        string $dayOfWeek,
        int $timeSlotId,
        ?int $ignoreScheduleId = null
    ): void {
        $query = Schedule::query()
            ->where('school_term_id', $schoolTermId)
            ->where('section_id', $sectionId)
            ->where('day_of_week', $dayOfWeek)
            ->where('time_slot_id', $timeSlotId);

        if ($ignoreScheduleId) {
            $query->where('id', '!=', $ignoreScheduleId);
        }

        if ($query->exists()) {
            abort(422, 'The section already has a schedule in this slot.');
        }
    }

    private function timeSlotHours(TimeSlot $timeSlot): float
    {
        $start = Carbon::createFromFormat('H:i:s', $timeSlot->start_time);
        $end = Carbon::createFromFormat('H:i:s', $timeSlot->end_time);

        return round($start->diffInMinutes($end) / 60, 2);
    }
}