<?php

namespace App\Rules;

use App\Models\timetable;
use Illuminate\Contracts\Validation\Rule;

class CheckTimeOverLap implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $teacherId;
    protected $startTime;
    protected $endTime;
    protected $day;

    public function __construct($teacherId, $startTime , $day)
    {
        $this->teacherId = $teacherId;
        $this->startTime = $startTime;
        $this->day = $day;
    }
    public function passes($attribute, $value)
    {
        $this->endTime = $value;

        return !timetable::where('TeacherID', $this->teacherId)
            ->where('Day', $this->day)
            ->where(function ($query) {
                $query->whereBetween('StartingTime', [$this->startTime, $this->endTime])
                      ->orWhereBetween('EndingTime', [$this->startTime, $this->endTime])
                      ->orWhere(function ($query) {
                          $query->where('StartingTime', '<=', $this->startTime)
                                ->where('EndingTime', '>=', $this->endTime);
                    });
            })
            ->exists();
    }

    public function message()
    {
        return 'The given time range overlaps with an existing time range for the same teacher.';
    }
}
