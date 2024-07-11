<?php

namespace App\Rules;

use App\Models\timetable;
use Illuminate\Contracts\Validation\Rule;

class CheckClassTimeOverLap implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $ClassId;
    protected $startTime;
    protected $endTime;
    protected $day;

    public function __construct($ClassId, $startTime , $day)
    {
        $this->ClassId = $ClassId;
        $this->startTime = $startTime;
        $this->day = $day;
    }
    public function passes($attribute, $value)
    {
        $this->endTime = $value;
        \DB::enableQueryLog();
        return !timetable::where('ClassID', $this->ClassId)
        ->where('Day', $this->day) // Assuming $this->day is a string like 'Monday'
        ->where(function ($query) {
            $query->whereBetween('StartingTime', [$this->startTime, $this->endTime])
                  ->orWhereBetween('EndingTime', [$this->startTime, $this->endTime])
                  ->orWhere(function ($query) {
                      $query->where('StartingTime', '<=', $this->startTime)
                            ->where('EndingTime', '>=', $this->endTime);
                  });
        })
        ->exists();
        dd(\DB::getQueryLog());
    }

    public function message()
    {
        return 'This time is overlapping according to this Class';
    }
}
