<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $enrollments = Enrollment::where('student_id', $user->id)->with('course')->get();
        $enrolledCourses = $enrollments->pluck('course');

        return view('dashboard', compact('enrolledCourses', 'enrollments'));
    }

    public function enroll(Course $course)
    {
        $user = Auth::user();

        // Check if already enrolled
        $existing = Enrollment::where('student_id', $user->id)->where('course_id', $course->id)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Already enrolled in this course.');
        }

        Enrollment::create([
            'student_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_on' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Successfully enrolled in the course!');
    }
}