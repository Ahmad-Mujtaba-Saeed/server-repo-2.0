<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Timetable;

class CheckClassTimeOverLap implements Rule
{
    protected $classId;
    protected $startTime;
    protected $endTime;

    public function __construct($classId, $startTime, $endTime)
    {
        $this->classId = $classId;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function passes($attribute, $value)
    {
        return !Timetable::where('class_id', $this->classId)
            ->where(function ($query) {
                $query->whereBetween('start_time', [$this->startTime, $this->endTime])
                    ->orWhereBetween('end_time', [$this->startTime, $this->endTime])
                    ->orWhere(function ($query) {
                        $query->where('start_time', '<=', $this->startTime)
                            ->where('end_time', '>=', $this->endTime);
                    });
            })
            ->exists();
    }

    public function message()
    {
        return 'The given time range overlaps with an existing time range for the same class.';
    }
}
