<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_code',
        'course_name',
        'description',
        'credits',
        'price',
    ];

    /**
     * Relationship: A course can have many students.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollments')
            ->withPivot('enrolled_on', 'status')
            ->withTimestamps();
    }

    /**
     * Relationship: A course has many enrollments.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Helper accessor for course title
    public function getTitleAttribute()
    {
        return $this->course_name;
    }

    // Currency formatter for Kenyan Shillings
    public function getFormattedPriceAttribute()
    {
        return 'Ksh ' . number_format($this->price, 0);
    }
}
