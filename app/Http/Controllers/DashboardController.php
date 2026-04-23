<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use App\Models\Schedule;
use App\Models\SchoolTerm;
use App\Models\SchoolYear;
use App\Models\Section;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedGradeParam = $request->query('grade_level', '11');

        if (!in_array($selectedGradeParam, ['11', '12'], true)) {
            $selectedGradeParam = '11';
        }

        $activeSchoolYear = SchoolYear::query()
            ->where('is_active', true)
            ->first();

        $activeSchoolTerm = SchoolTerm::query()
            ->where('is_active', true)
            ->first();

        $selectedGradeLevel = GradeLevel::query()
            ->where('is_active', true)
            ->where(function ($query) use ($selectedGradeParam) {
                $query->where('code', $selectedGradeParam)
                    ->orWhere('name', 'Grade ' . $selectedGradeParam)
                    ->orWhere('sort_order', (int) $selectedGradeParam);
            })
            ->first();

        if (!$selectedGradeLevel) {
            $selectedGradeLevel = GradeLevel::query()
                ->where('is_active', true)
                ->where('name', 'like', '%' . $selectedGradeParam . '%')
                ->first();
        }

        $selectedGradeLabel = $selectedGradeLevel?->name ?? ('Grade ' . $selectedGradeParam);

        $baseSectionsQuery = Section::query()
            ->where('is_active', true)
            ->when(
                $selectedGradeLevel,
                fn ($query) => $query->where('grade_level_id', $selectedGradeLevel->id)
            );

        $sectionsQuery = clone $baseSectionsQuery;

        if ($activeSchoolYear) {
            $yearFilteredCount = (clone $baseSectionsQuery)
                ->where('school_year_id', $activeSchoolYear->id)
                ->count();

            if ($yearFilteredCount > 0) {
                $sectionsQuery->where('school_year_id', $activeSchoolYear->id);
            }
        }

        $sectionIds = $sectionsQuery->pluck('id');
        $totalSections = $sectionIds->count();

        $baseSchedulesQuery = Schedule::query()
            ->when(
                $sectionIds->isNotEmpty(),
                fn ($query) => $query->whereIn('section_id', $sectionIds),
                fn ($query) => $query->whereRaw('1 = 0')
            );

        $schedulesQuery = clone $baseSchedulesQuery;

        if ($activeSchoolTerm) {
            $termFilteredCount = (clone $baseSchedulesQuery)
                ->where('school_term_id', $activeSchoolTerm->id)
                ->count();

            if ($termFilteredCount > 0) {
                $schedulesQuery->where('school_term_id', $activeSchoolTerm->id);
            }
        }

        $totalSchedules = (clone $schedulesQuery)->count();

        $teacherIds = (clone $schedulesQuery)->pluck('teacher_id')->filter()->unique();
        $subjectIds = (clone $schedulesQuery)->pluck('subject_id')->filter()->unique();
        $roomIds = (clone $schedulesQuery)->pluck('room_id')->filter()->unique();
        $scheduledSectionIds = (clone $schedulesQuery)->pluck('section_id')->filter()->unique();

        $totalTeachers = $teacherIds->count();
        $totalSubjects = $subjectIds->count();
        $totalRooms = $roomIds->count();
        $incompleteSchedules = $sectionIds->diff($scheduledSectionIds)->count();

        $configuredPathwayCount = (clone $sectionsQuery)
            ->whereNotNull('pathway_id')
            ->distinct()
            ->count('pathway_id');

        $configuredShiftCount = (clone $sectionsQuery)
            ->whereNotNull('shift_type_id')
            ->distinct()
            ->count('shift_type_id');

        return view('dashboard.index', [
            'selectedGradeParam' => $selectedGradeParam,
            'selectedGradeLabel' => $selectedGradeLabel,
            'activeSchoolYear' => $activeSchoolYear,
            'activeSchoolTerm' => $activeSchoolTerm,
            'totalSections' => $totalSections,
            'totalTeachers' => $totalTeachers,
            'totalSubjects' => $totalSubjects,
            'totalRooms' => $totalRooms,
            'totalSchedules' => $totalSchedules,
            'incompleteSchedules' => $incompleteSchedules,
            'configuredPathwayCount' => $configuredPathwayCount,
            'configuredShiftCount' => $configuredShiftCount,
        ]);
    }
}