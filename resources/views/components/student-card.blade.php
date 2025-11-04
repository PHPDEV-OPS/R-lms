<div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
    <div class="flex items-center space-x-4">
        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
            <span class="text-white font-semibold text-lg">
                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
            </span>
        </div>
        <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900">
                {{ $student->full_name }}
            </h3>
            <p class="text-sm text-gray-500">
                {{ $student->email }}
            </p>
        </div>
    </div>
</div>