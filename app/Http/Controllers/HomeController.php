<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredCourses = Course::latest()->take(6)->get();
        $totalCourses = Course::count();
        $totalStudents = Student::count();
        $totalEnrollments = Enrollment::count();

        return view('home', compact('featuredCourses', 'totalCourses', 'totalStudents', 'totalEnrollments'));
    }
}