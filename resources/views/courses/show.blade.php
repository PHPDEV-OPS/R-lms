@extends('layouts.app')

@section('page-title', $course->title)

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">{{ $course->title }}</h1>
            <p class="mt-2 text-lg text-gray-600">{{ $course->description }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Course Details</h3>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Duration</dt>
                        <dd class="text-sm text-gray-900">{{ $course->duration ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Instructor</dt>
                        <dd class="text-sm text-gray-900">{{ $course->instructor ?? 'TBD' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Level</dt>
                        <dd class="text-sm text-gray-900">{{ $course->level ?? 'Beginner' }}</dd>
                    </div>
                </dl>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Enrollment</h3>
                @auth
                    @php
                        $enrolled = \App\Models\Enrollment::where('student_id', auth()->id())->where('course_id', $course->id)->exists();
                    @endphp
                    @if($enrolled)
                        <p class="text-green-600 font-medium">You are enrolled in this course</p>
                        <a href="{{ route('dashboard') }}" class="mt-2 inline-block bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">Go to Dashboard</a>
                    @else
                        <form method="POST" action="{{ route('enroll', $course) }}">
                            @csrf
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">Enroll Now</button>
                        </form>
                    @endif
                @else
                    <p class="text-gray-600 mb-2">Login to enroll in this course</p>
                    <a href="{{ route('login') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">Login</a>
                @endauth
            </div>
        </div>

        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Course Content</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-600">{{ $course->content ?? 'Course content will be available upon enrollment.' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection