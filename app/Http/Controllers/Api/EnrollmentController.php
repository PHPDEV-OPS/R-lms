<?php

namespace App\Http\Controllers\Api;

use App\Events\EnrollmentCreated;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        // Check if already enrolled before starting transaction
        $exists = Enrollment::where('student_id', $data['student_id'])
            ->where('course_id', $data['course_id'])
            ->exists();
        
        if ($exists) {
            return response()->json(['message' => 'Already enrolled'], 422);
        }

        DB::beginTransaction();
        
        try {
            $enrollment = Enrollment::create([
                'student_id' => $data['student_id'],
                'course_id' => $data['course_id'],
                'enrolled_on' => now(),
                'status' => 'active',
            ]);

            DB::commit();
            
            EnrollmentCreated::dispatch($enrollment);
            
            return response()->json($enrollment, 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Enrollment failed'], 500);
        }
    }
}
