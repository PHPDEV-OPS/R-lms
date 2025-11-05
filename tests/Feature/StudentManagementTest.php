<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_view_student_list()
    {
        Student::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.students.index'));

        $response->assertStatus(200)
            ->assertViewHas('students');
    }

    public function test_admin_can_view_student_creation_form()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.students.create'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_student()
    {
        $this->startSession();
        
        $studentData = [
            'student_id' => 'STU001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'date_of_birth' => '2000-01-01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('admin.students.store'), $studentData + ['_token' => session()->token()]);

        $response->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success', 'Student created successfully.');

        $this->assertDatabaseHas('students', [
            'student_id' => 'STU001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);
    }

    public function test_admin_cannot_create_student_with_duplicate_email()
    {
        $this->startSession();
        
        Student::factory()->create(['email' => 'john.doe@example.com']);

        $studentData = [
            'student_id' => 'STU001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'date_of_birth' => '2000-01-01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('admin.students.store'), $studentData + ['_token' => session()->token()]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['email']);

        $this->assertDatabaseCount('students', 1);
    }

    public function test_admin_cannot_create_student_with_duplicate_student_id()
    {
        $this->startSession();
        
        Student::factory()->create(['student_id' => 'STU001']);

        $studentData = [
            'student_id' => 'STU001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'date_of_birth' => '2000-01-01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('admin.students.store'), $studentData + ['_token' => session()->token()]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['student_id']);

        $this->assertDatabaseCount('students', 1);
    }

    public function test_admin_can_view_student_details()
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.students.show', $student));

        $response->assertStatus(200)
            ->assertViewHas('student', $student);
    }

    public function test_admin_can_view_student_edit_form()
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.students.edit', $student));

        $response->assertStatus(200)
            ->assertViewHas('student', $student);
    }

    public function test_admin_can_update_student()
    {
        $this->startSession();
        
        $student = Student::factory()->create([
            'student_id' => 'STU001',
        ]);

        $updateData = [
            'student_id' => 'STU002',
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated.email@example.com',
            'date_of_birth' => '2000-01-01',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->put(route('admin.students.update', $student), $updateData + ['_token' => session()->token()]);

        $response->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success', 'Student updated successfully.');

        $student->refresh();
        $this->assertEquals('STU002', $student->student_id);
        $this->assertEquals('Updated', $student->first_name);
        $this->assertEquals('Name', $student->last_name);
        $this->assertEquals('updated.email@example.com', $student->email);
    }

    public function test_admin_can_update_student_with_same_email()
    {
        $this->startSession();
        
        $student = Student::factory()->create();

        $updateData = [
            'student_id' => $student->student_id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => $student->email, // Same email
            'date_of_birth' => $student->date_of_birth->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->admin)
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->put(route('admin.students.update', $student), $updateData + ['_token' => session()->token()]);

        $response->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success', 'Student updated successfully.');

        $student->refresh();
        $this->assertEquals('Updated', $student->first_name);
        $this->assertEquals('Name', $student->last_name);
    }

    public function test_admin_cannot_update_student_with_existing_email()
    {
        $this->startSession();
        
        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();

        $updateData = [
            'student_id' => $student2->student_id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => $student1->email, // Existing email
            'date_of_birth' => $student2->date_of_birth->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->admin)
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->put(route('admin.students.update', $student2), $updateData + ['_token' => session()->token()]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['email']);

        $student2->refresh();
        $this->assertNotEquals('Updated', $student2->first_name);
    }

    public function test_admin_can_delete_student()
    {
        $this->startSession();
        
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin)
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->delete(route('admin.students.destroy', $student), ['_token' => session()->token()]);

        $response->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success', 'Student deleted successfully.');

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_student_creation_requires_valid_data()
    {
        $this->startSession();
        
        $response = $this->actingAs($this->admin)
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('admin.students.store'), ['_token' => session()->token()]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['student_id', 'first_name', 'last_name', 'email', 'date_of_birth', 'password']);
    }

    public function test_student_password_must_be_confirmed()
    {
        $this->startSession();
        
        $studentData = [
            'student_id' => 'STU001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'date_of_birth' => '2000-01-01',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        $response = $this->actingAs($this->admin)
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('admin.students.store'), $studentData + ['_token' => session()->token()]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['password']);
    }

    public function test_non_admin_cannot_access_student_management()
    {
        $regularUser = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($regularUser)
            ->get(route('admin.students.index'));

        $response->assertStatus(403); // Forbidden
    }

    public function test_student_show_includes_enrollment_data()
    {
        $student = Student::factory()->create();
        $course = \App\Models\Course::factory()->create();

        // Create enrollment
        \App\Models\Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.students.show', $student));

        $response->assertStatus(200)
            ->assertViewHas('student', function ($viewStudent) {
                return $viewStudent->enrollments->count() === 1;
            });
    }
}