<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class StudentCard extends Component
{
    public $student;

    public function __construct($student)
    {
        $this->student = $student;
    }

    /**
     * Get view 
     */
    public function render(): View|Closure|string
    {
        return view('components.student-card');
    }
}
