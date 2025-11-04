<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'enrolled_on',
        'status',
    ];

    protected $casts = [
        'enrolled_on' => 'datetime',
    ];

    /**
     * Relationship: An enrollment belongs to a student.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship: An enrollment belongs to a course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
