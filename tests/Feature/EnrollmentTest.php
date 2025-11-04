<?php

namespace Tests\Feature;

use App\Events\EnrollmentCreated;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_enroll_in_course()
    {
        Event::fake();

        $student = Student::factory()->create();
        $course = Course::factory()->create();

        // Create token for student
        $token = $student->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        Event::assertDispatched(EnrollmentCreated::class);
    }

    public function test_student_cannot_enroll_twice_in_same_course()
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();

        // Create first enrollment
        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $token = $student->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Already enrolled']);
    }
}
