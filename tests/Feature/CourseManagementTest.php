<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_view_course_list()
    {
        Course::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.courses.index'));

        $response->assertStatus(200)
            ->assertViewHas('courses');
    }

    public function test_admin_can_view_course_creation_form()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.courses.create'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_course()
    {
        $this->startSession();
        
        $courseData = [
            'course_code' => 'CS101',
            'course_name' => 'Introduction to Computer Science',
            'description' => 'A comprehensive introduction to computer science fundamentals.',
            'credits' => 3,
        ];

        $response = $this->actingAs($this->admin)
            ->from(route('admin.courses.create'))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('admin.courses.store'), $courseData + ['_token' => session()->token()]);

        $response->assertRedirect(route('admin.courses.index'))
            ->assertSessionHas('success', 'Course created successfully.');

        $this->assertDatabaseHas('courses', $courseData);
    }

    public function test_admin_cannot_create_course_with_duplicate_code()
    {
        $this->startSession();
        
        Course::factory()->create(['course_code' => 'CS101']);

        $courseData = [
            'course_code' => 'CS101',
            'course_name' => 'Different Course Name',
            'description' => 'Different description.',
            'credits' => 3,
        ];

        $response = $this->actingAs($this->admin)
            ->from(route('admin.courses.create'))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('admin.courses.store'), $courseData + ['_token' => session()->token()]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['course_code']);

        $this->assertDatabaseCount('courses', 1);
    }

    public function test_admin_can_view_course_details()
    {
        $course = Course::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.courses.show', $course));

        $response->assertStatus(200)
            ->assertViewHas('course', $course);
    }

    public function test_admin_can_view_course_edit_form()
    {
        $course = Course::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.courses.edit', $course));

        $response->assertStatus(200)
            ->assertViewHas('course', $course);
    }

    public function test_admin_can_update_course()
    {
        $this->startSession();
        
        $course = Course::factory()->create([
            'course_code' => 'CS101',
            'course_name' => 'Old Name',
        ]);

        $updateData = [
            'course_code' => 'CS102',
            'course_name' => 'Updated Course Name',
            'description' => 'Updated description.',
            'credits' => 4,
        ];

        $response = $this->actingAs($this->admin)
            ->from(route('admin.courses.edit', $course))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->put(route('admin.courses.update', $course), $updateData + ['_token' => session()->token()]);

        $response->assertRedirect(route('admin.courses.index'))
            ->assertSessionHas('success', 'Course updated successfully.');

        $course->refresh();
        $this->assertEquals('CS102', $course->course_code);
        $this->assertEquals('Updated Course Name', $course->course_name);
    }

    public function test_admin_cannot_update_course_with_existing_code()
    {
        $this->startSession();
        
        $course1 = Course::factory()->create(['course_code' => 'CS101']);
        $course2 = Course::factory()->create(['course_code' => 'CS102']);

        $updateData = [
            'course_code' => 'CS101', // Existing code
            'course_name' => 'Updated Name',
            'description' => 'Updated description.',
            'credits' => 3,
            '_token' => csrf_token(),
        ];

        $response = $this->actingAs($this->admin)
            ->from(route('admin.courses.edit', $course2))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->put(route('admin.courses.update', $course2), $updateData + ['_token' => session()->token()]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['course_code']);

        $course2->refresh();
        $this->assertEquals('CS102', $course2->course_code); // Should remain unchanged
    }

    public function test_admin_can_delete_course()
    {
        $this->startSession();
        
        $course = Course::factory()->create();

        $response = $this->actingAs($this->admin)
            ->from(route('admin.courses.index'))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->delete(route('admin.courses.destroy', $course), ['_token' => session()->token()]);

        $response->assertRedirect(route('admin.courses.index'))
            ->assertSessionHas('success', 'Course deleted successfully.');

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_course_creation_requires_valid_data()
    {
        $this->startSession();
        
        $response = $this->actingAs($this->admin)
            ->from(route('admin.courses.create'))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('admin.courses.store'), ['_token' => session()->token()]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['course_code', 'course_name', 'credits']);
    }

    public function test_non_admin_cannot_access_course_management()
    {
        $regularUser = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($regularUser)
            ->get(route('admin.courses.index'));

        $response->assertStatus(403); // Forbidden
    }
}