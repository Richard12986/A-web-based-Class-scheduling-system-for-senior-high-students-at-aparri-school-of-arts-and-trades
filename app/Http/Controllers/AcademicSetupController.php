<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use App\Models\Pathway;
use App\Models\Room;
use App\Models\SchoolTerm;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\ShiftType;
use App\Models\Subject;
use App\Models\TimeSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicSetupController extends Controller
{
    public function index(): View
    {
        $activeSchoolYear = SchoolYear::query()
            ->where('is_active', true)
            ->first();

        $activeSchoolTerm = SchoolTerm::query()
            ->with('schoolYear')
            ->where('is_active', true)
            ->first();

        $gradeLevels = GradeLevel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $pathways = Pathway::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $shiftTypes = ShiftType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $sections = Section::query()
            ->with(['schoolYear', 'gradeLevel', 'pathway', 'shiftType', 'room'])
            ->when($activeSchoolYear, function ($query) use ($activeSchoolYear) {
                $query->where('school_year_id', $activeSchoolYear->id);
            })
            ->orderBy('name')
            ->get();

        $rooms = Room::query()
            ->orderBy('name')
            ->get();

        $subjects = Subject::query()
            ->orderBy('name')
            ->get();

        $timeSlots = TimeSlot::query()
            ->with('shiftType')
            ->orderBy('shift_type_id')
            ->orderBy('slot_order')
            ->get()
            ->groupBy('shift_type_id');

        return view('academic-setup.index', [
            'activeSchoolYear' => $activeSchoolYear,
            'activeSchoolTerm' => $activeSchoolTerm,
            'gradeLevels' => $gradeLevels,
            'pathways' => $pathways,
            'shiftTypes' => $shiftTypes,
            'sections' => $sections,
            'rooms' => $rooms,
            'subjects' => $subjects,
            'timeSlots' => $timeSlots,
        ]);
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $activeSchoolYear = SchoolYear::query()
            ->where('is_active', true)
            ->firstOrFail();

        $validated = $request->validate([
            'grade_level_id' => ['required', 'exists:grade_levels,id'],
            'pathway_id' => ['required', 'exists:pathways,id'],
            'shift_type_id' => ['required', 'exists:shift_types,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'name' => ['required', 'string', 'max:100'],
            'student_count' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $existingSection = Section::query()
            ->where('school_year_id', $activeSchoolYear->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($existingSection) {
            return back()
                ->withInput()
                ->withErrors([
                    'section_name' => 'A section with this name already exists in the active school year.',
                ]);
        }

        Section::query()->create([
            'school_year_id' => $activeSchoolYear->id,
            'grade_level_id' => $validated['grade_level_id'],
            'pathway_id' => $validated['pathway_id'],
            'shift_type_id' => $validated['shift_type_id'],
            'room_id' => $validated['room_id'] ?? null,
            'name' => $validated['name'],
            'student_count' => $validated['student_count'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()
            ->route('academic-setup.index')
            ->with('success', 'Section added successfully.');
    }

    public function updateSection(Request $request, Section $section): RedirectResponse
    {
        $validated = $request->validate([
            'grade_level_id' => ['required', 'exists:grade_levels,id'],
            'pathway_id' => ['required', 'exists:pathways,id'],
            'shift_type_id' => ['required', 'exists:shift_types,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'name' => ['required', 'string', 'max:100'],
            'student_count' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $existingSection = Section::query()
            ->where('school_year_id', $section->school_year_id)
            ->where('name', $validated['name'])
            ->where('id', '!=', $section->id)
            ->exists();

        if ($existingSection) {
            return back()
                ->withInput()
                ->withErrors([
                    'section_name' => 'A section with this name already exists in the active school year.',
                ]);
        }

        $section->update([
            'grade_level_id' => $validated['grade_level_id'],
            'pathway_id' => $validated['pathway_id'],
            'shift_type_id' => $validated['shift_type_id'],
            'room_id' => $validated['room_id'] ?? null,
            'name' => $validated['name'],
            'student_count' => $validated['student_count'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('academic-setup.index')
            ->with('success', 'Section updated successfully.');
    }

    public function destroySection(Section $section): RedirectResponse
    {
        $hasSchedules = $section->schedules()->exists();

        if ($hasSchedules) {
            return redirect()
                ->route('academic-setup.index')
                ->with('error', 'Section cannot be deleted because it already has schedule entries.');
        }

        $section->delete();

        return redirect()
            ->route('academic-setup.index')
            ->with('success', 'Section deleted successfully.');
    }

    public function storeRoom(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:rooms,name'],
            'room_type' => ['required', 'in:general,laboratory,workshop'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Room::query()->create([
            'name' => $validated['name'],
            'room_type' => $validated['room_type'],
            'capacity' => $validated['capacity'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()
            ->route('academic-setup.index')
            ->with('success', 'Room added successfully.');
    }

    public function updateRoom(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:rooms,name,' . $room->id],
            'room_type' => ['required', 'in:general,laboratory,workshop'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $room->update([
            'name' => $validated['name'],
            'room_type' => $validated['room_type'],
            'capacity' => $validated['capacity'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('academic-setup.index')
            ->with('success', 'Room updated successfully.');
    }

    public function destroyRoom(Room $room): RedirectResponse
    {
        $isUsedBySections = $room->sections()->exists();
        $isUsedBySchedules = $room->schedules()->exists();

        if ($isUsedBySections || $isUsedBySchedules) {
            return redirect()
                ->route('academic-setup.index')
                ->with('error', 'Room cannot be deleted because it is already used by a section or schedule.');
        }

        $room->delete();

        return redirect()
            ->route('academic-setup.index')
            ->with('success', 'Room deleted successfully.');
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:150', 'unique:subjects,name'],
            'subject_type' => ['required', 'in:core,elective,hgp'],
            'weekly_hours' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'total_hours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'offering_type' => ['required', 'in:semester,year'],
            'requires_special_room' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Subject::query()->create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'subject_type' => $validated['subject_type'],
            'weekly_hours' => $validated['weekly_hours'],
            'total_hours' => $validated['total_hours'] ?? null,
            'offering_type' => $validated['offering_type'],
            'requires_special_room' => (bool) ($validated['requires_special_room'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()
            ->route('academic-setup.index')
            ->with('success', 'Subject added successfully.');
    }

    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:subjects,code,' . $subject->id],
            'name' => ['required', 'string', 'max:150', 'unique:subjects,name,' . $subject->id],
            'subject_type' => ['required', 'in:core,elective,hgp'],
            'weekly_hours' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'total_hours' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'offering_type' => ['required', 'in:semester,year'],
            'requires_special_room' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $subject->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'subject_type' => $validated['subject_type'],
            'weekly_hours' => $validated['weekly_hours'],
            'total_hours' => $validated['total_hours'] ?? null,
            'offering_type' => $validated['offering_type'],
            'requires_special_room' => (bool) ($validated['requires_special_room'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('academic-setup.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroySubject(Subject $subject): RedirectResponse
    {
        $isUsedInSchedules = $subject->schedules()->exists();
        $isUsedInTeacherAssignments = $subject->teacherSubjects()->exists();
        $isUsedInPathwayMappings = $subject->pathwaySubjects()->exists();

        if ($isUsedInSchedules || $isUsedInTeacherAssignments || $isUsedInPathwayMappings) {
            return redirect()
                ->route('academic-setup.index')
                ->with('error', 'Subject cannot be deleted because it is already used in other academic records.');
        }

        $subject->delete();

        return redirect()
            ->route('academic-setup.index')
            ->with('success', 'Subject deleted successfully.');
    }

    public function storeTimeSlot(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shift_type_id' => ['required', 'exists:shift_types,id'],
            'label' => ['nullable', 'string', 'max:50'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_order' => ['required', 'integer', 'min:1', 'max:999'],
            'is_break' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $duplicateOrder = TimeSlot::query()
            ->where('shift_type_id', $validated['shift_type_id'])
            ->where('slot_order', $validated['slot_order'])
            ->exists();

        if ($duplicateOrder) {
            return back()
                ->withInput()
                ->withErrors([
                    'time_slot_order' => 'Slot order already exists for the selected shift.',
                ]);
        }

        $duplicateRange = TimeSlot::query()
            ->where('shift_type_id', $validated['shift_type_id'])
            ->where('start_time', $validated['start_time'])
            ->where('end_time', $validated['end_time'])
            ->exists();

        if ($duplicateRange) {
            return back()
                ->withInput()
                ->withErrors([
                    'time_slot_range' => 'This time range already exists for the selected shift.',
                ]);
        }

        TimeSlot::query()->create([
            'shift_type_id' => $validated['shift_type_id'],
            'label' => $validated['label'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'slot_order' => $validated['slot_order'],
            'is_break' => (bool) ($validated['is_break'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()
            ->route('academic-setup.index')
            ->with('success', 'Time slot added successfully.');
    }

    public function updateTimeSlot(Request $request, TimeSlot $timeSlot): RedirectResponse
    {
        $validated = $request->validate([
            'shift_type_id' => ['required', 'exists:shift_types,id'],
            'label' => ['nullable', 'string', 'max:50'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_order' => ['required', 'integer', 'min:1', 'max:999'],
            'is_break' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $duplicateOrder = TimeSlot::query()
            ->where('shift_type_id', $validated['shift_type_id'])
            ->where('slot_order', $validated['slot_order'])
            ->where('id', '!=', $timeSlot->id)
            ->exists();

        if ($duplicateOrder) {
            return back()
                ->withInput()
                ->withErrors([
                    'time_slot_order' => 'Slot order already exists for the selected shift.',
                ]);
        }

        $duplicateRange = TimeSlot::query()
            ->where('shift_type_id', $validated['shift_type_id'])
            ->where('start_time', $validated['start_time'])
            ->where('end_time', $validated['end_time'])
            ->where('id', '!=', $timeSlot->id)
            ->exists();

        if ($duplicateRange) {
            return back()
                ->withInput()
                ->withErrors([
                    'time_slot_range' => 'This time range already exists for the selected shift.',
                ]);
        }

        $timeSlot->update([
            'shift_type_id' => $validated['shift_type_id'],
            'label' => $validated['label'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'slot_order' => $validated['slot_order'],
            'is_break' => (bool) ($validated['is_break'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('academic-setup.index')
            ->with('success', 'Time slot updated successfully.');
    }

    public function destroyTimeSlot(TimeSlot $timeSlot): RedirectResponse
    {
        $hasSchedules = $timeSlot->schedules()->exists();

        if ($hasSchedules) {
            return redirect()
                ->route('academic-setup.index')
                ->with('error', 'Time slot cannot be deleted because it is already used in schedules.');
        }

        $timeSlot->delete();

        return redirect()
            ->route('academic-setup.index')
            ->with('success', 'Time slot deleted successfully.');
    }
}