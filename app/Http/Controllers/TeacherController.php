<?php

namespace App\Http\Controllers;

use App\Models\SchoolTerm;
use App\Models\Teacher;
use App\Models\TeacherLoan;
use App\Models\TeacherLoanPayment;
use App\Models\TeacherSubject;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));

        $activeTerm = SchoolTerm::with('schoolYear')
            ->where('is_active', true)
            ->first();

        $teachersQuery = Teacher::query()
            ->with([
                'teacherSubjects.subject',
                'teacherLoans.payments',
                'schedules' => function ($query) use ($activeTerm) {
                    $query->with(['subject', 'room', 'section', 'timeSlot']);

                    if ($activeTerm) {
                        $query->where('school_term_id', $activeTerm->id);
                    }
                },
            ])
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($search !== '') {
            $teachersQuery->where(function ($query) use ($search) {
                $query->where('employee_number', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('middle_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('contact_number', 'like', '%' . $search . '%');
            });
        }

        $teachers = $teachersQuery->get();

        $now = Carbon::now('Asia/Manila');
        $currentDay = strtolower($now->format('l'));
        $currentTime = $now->format('H:i:s');

        $teacherDirectory = $teachers->map(function (Teacher $teacher) use ($currentDay, $currentTime): array {
            $currentSchedule = $teacher->schedules
                ->filter(function ($schedule) use ($currentDay, $currentTime) {
                    if (!$schedule->timeSlot || !$schedule->room) {
                        return false;
                    }

                    if ($schedule->day_of_week !== $currentDay) {
                        return false;
                    }

                    return $schedule->timeSlot->start_time <= $currentTime
                        && $schedule->timeSlot->end_time > $currentTime;
                })
                ->sortBy(fn ($schedule) => $schedule->timeSlot?->start_time)
                ->first();

            return [
                'teacher' => $teacher,
                'assigned_subjects' => $teacher->teacherSubjects
                    ->map(fn (TeacherSubject $teacherSubject) => $teacherSubject->subject?->name)
                    ->filter()
                    ->values(),
                'current_schedule' => $currentSchedule,
                'current_room_name' => $currentSchedule?->room?->name,
                'current_room_meta' => $currentSchedule
                    ? ($currentSchedule->section?->name . ' • ' . ($currentSchedule->timeSlot?->time_range ?? ''))
                    : 'Available this time slot',
            ];
        });

        $loadMonitoring = $teachers->map(function (Teacher $teacher): array {
            $assignedHours = $this->calculateAssignedHours($teacher);
            $maxLoad = (float) $teacher->maximum_weekly_load;
            $remainingLoad = $maxLoad - $assignedHours;

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
                'remaining_hours' => $remainingLoad,
                'status' => $status,
            ];
        })->values();

        $teacherLoans = TeacherLoan::query()
            ->with(['teacher', 'payments'])
            ->orderByDesc('loan_date')
            ->orderByDesc('id')
            ->get();

        $paymentHistory = TeacherLoanPayment::query()
            ->with(['teacherLoan.teacher'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        $summary = [
            'total_teachers' => Teacher::count(),
            'assigned_teachers' => $teachers->filter(fn (Teacher $teacher) => $teacher->teacherSubjects->isNotEmpty())->count(),
            'overload_alerts' => $loadMonitoring->where('status', 'overload')->count(),
            'active_tea_loans' => $teacherLoans->where('status', 'active')->count(),
            'paid_loans' => $teacherLoans->where('status', 'paid')->count(),
            'teachers_with_balance' => $teacherLoans
                ->filter(fn (TeacherLoan $loan) => $loan->outstanding_balance > 0)
                ->pluck('teacher_id')
                ->unique()
                ->count(),
        ];

        $subjects = Subject::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('teachers.index', [
            'teachers' => $teachers,
            'teacherDirectory' => $teacherDirectory,
            'loadMonitoring' => $loadMonitoring,
            'teacherLoans' => $teacherLoans,
            'paymentHistory' => $paymentHistory,
            'subjects' => $subjects,
            'activeTerm' => $activeTerm,
            'summary' => $summary,
            'search' => $search,
        ]);
    }

    public function storeTeacher(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_number' => ['required', 'string', 'max:30', 'unique:teachers,employee_number'],
            'first_name' => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'sex' => ['nullable', Rule::in(['male', 'female'])],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100', 'unique:teachers,email'],
            'maximum_weekly_load' => ['required', 'numeric', 'min:0', 'max:99.99'],
            'is_active' => ['required', 'boolean'],
        ]);

        Teacher::create($validated);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Teacher record created successfully.');
    }

    public function updateTeacher(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'employee_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique('teachers', 'employee_number')->ignore($teacher->id),
            ],
            'first_name' => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'sex' => ['nullable', Rule::in(['male', 'female'])],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('teachers', 'email')->ignore($teacher->id),
            ],
            'maximum_weekly_load' => ['required', 'numeric', 'min:0', 'max:99.99'],
            'is_active' => ['required', 'boolean'],
        ]);

        $teacher->update($validated);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Teacher record updated successfully.');
    }

    public function destroyTeacher(Teacher $teacher): RedirectResponse
    {
        $teacher->delete();

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Teacher record deleted successfully.');
    }

    public function storeTeacherSubject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::unique('teacher_subjects', 'subject_id')
                    ->where(fn ($query) => $query->where('teacher_id', $request->teacher_id)),
            ],
        ]);

        TeacherSubject::create($validated);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Teaching assignment added successfully.');
    }

    public function destroyTeacherSubject(TeacherSubject $teacherSubject): RedirectResponse
    {
        $teacherSubject->delete();

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Teaching assignment removed successfully.');
    }

    public function storeLoan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'loan_type' => ['required', 'string', 'max:50'],
            'principal_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'loan_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'paid', 'cancelled'])],
        ]);

        TeacherLoan::create($validated);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'TEA loan record created successfully.');
    }

    public function updateLoan(Request $request, TeacherLoan $teacherLoan): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'loan_type' => ['required', 'string', 'max:50'],
            'principal_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'loan_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'paid', 'cancelled'])],
        ]);

        $teacherLoan->update($validated);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'TEA loan record updated successfully.');
    }

    public function destroyLoan(TeacherLoan $teacherLoan): RedirectResponse
    {
        $teacherLoan->delete();

        return redirect()
            ->route('teachers.index')
            ->with('success', 'TEA loan record deleted successfully.');
    }

    public function storeLoanPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_loan_id' => ['required', 'exists:teacher_loans,id'],
            'payment_date' => ['required', 'date'],
            'amount_paid' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'remarks' => ['nullable', 'string'],
        ]);

        TeacherLoanPayment::create($validated);

        $loan = TeacherLoan::with('payments')->findOrFail($validated['teacher_loan_id']);
        $this->syncLoanStatus($loan);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Loan payment recorded successfully.');
    }

    public function updateLoanPayment(Request $request, TeacherLoanPayment $teacherLoanPayment): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_loan_id' => ['required', 'exists:teacher_loans,id'],
            'payment_date' => ['required', 'date'],
            'amount_paid' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'remarks' => ['nullable', 'string'],
        ]);

        $teacherLoanPayment->update($validated);

        $loan = TeacherLoan::with('payments')->findOrFail($validated['teacher_loan_id']);
        $this->syncLoanStatus($loan);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Loan payment updated successfully.');
    }

    public function destroyLoanPayment(TeacherLoanPayment $teacherLoanPayment): RedirectResponse
    {
        $loanId = $teacherLoanPayment->teacher_loan_id;

        $teacherLoanPayment->delete();

        $loan = TeacherLoan::with('payments')->findOrFail($loanId);
        $this->syncLoanStatus($loan);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Loan payment deleted successfully.');
    }

    private function calculateAssignedHours(Teacher $teacher): float
    {
        return round(
            $teacher->schedules->sum(function ($schedule) {
                if (!$schedule->timeSlot || $schedule->timeSlot->is_break) {
                    return 0;
                }

                $start = Carbon::createFromFormat('H:i:s', $schedule->timeSlot->start_time);
                $end = Carbon::createFromFormat('H:i:s', $schedule->timeSlot->end_time);

                return $start->diffInMinutes($end) / 60;
            }),
            2
        );
    }

    private function syncLoanStatus(TeacherLoan $loan): void
    {
        if ($loan->status === 'cancelled') {
            return;
        }

        $status = $loan->outstanding_balance <= 0 ? 'paid' : 'active';

        if ($loan->status !== $status) {
            $loan->update(['status' => $status]);
        }
    }
}