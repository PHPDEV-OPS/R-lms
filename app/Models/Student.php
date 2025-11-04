<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'date_of_birth',
        'password',
        'role',
        'student_id',
        'bio',
        'notification_preferences',
        'privacy_settings',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'password' => 'hashed',
        'notification_preferences' => 'array',
        'privacy_settings' => 'array',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Accessor for full_name that concatenates first and last names.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Relationship: A student can enroll in many courses.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->withPivot('enrolled_on', 'status')
            ->withTimestamps();
    }

    /**
     * Relationship: A student has many enrollments.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
