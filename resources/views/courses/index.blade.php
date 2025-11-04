@extends('layouts.app')

@section('page-title', 'Courses')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Available Courses</h1>
    <p class="mt-1 text-sm text-gray-600">Browse and enroll in our courses</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($courses as $course)
    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $course->title }}</h3>
            <p class="text-sm text-gray-600 mb-4">{{ Str::limit($course->description, 150) }}</p>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Duration: {{ $course->duration ?? 'N/A' }}</span>
                <a href="{{ route('courses.show', $course) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">View Details</a>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-8">
    {{ $courses->links() }}
</div>
@endsection