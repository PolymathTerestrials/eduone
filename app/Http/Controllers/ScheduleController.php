<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Schedule;
use App\Room;
use App\Group;
use \Carbon\Carbon as Carbon;

class ScheduleController extends Controller
{
    protected $teachers = [];

    protected $classes = [];

    protected $subjects = [];

    public function __construct()
    {
        $this->teachers = \App\User::ofRole(3)->pluck('display_name', 'id')->toArray();
        
        $this->subjects = \App\Subject::get(['id', 'name', 'sessions_count'])->getDictionary();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->date === 'today')
            $request->date = date('Y-m-d');

        $request->date = isset($request->date) ? $request->date : date('Y-m-d');

        $request_date = Carbon::createFromFormat('Y-m-d', $request->date);

        $today          = Carbon::today()->format('Y-m-d');
        $previous_day   = $request_date->copy()->subDay()->format('Y-m-d');
        $next_day       = $request_date->copy()->addDay()->format('Y-m-d');
        $viewing_day    = $request_date->copy()->format('Y-m-d');

        $weekday        = config('settings.weekdays')[$request_date->dayOfWeek];

        // Todo: Should cast $request->date to date type
        
        $slots = config('settings.slots');

        $rooms = Room::pluck('name', 'id')->toArray();

        $available_schedules = Schedule::whereDate('started_at', '=', $viewing_day)
                                        ->ofBranch(1)->get()->toArray();

        $schedules = [];
        foreach ($rooms as $room_id => $room_name) {
            foreach ($slots as $slot) {                    
                $schedules[$room_id][$slot['id']] = new \stdClass;
            }
        }

        foreach ($available_schedules as $schedule){
            if (isset($schedule['room_id']) && isset($schedule['slot_id'])) {
                $schedules[$schedule['room_id']][$schedule['slot_id']] = $schedule;
            }
        }

        $classes = Group::ofType('class')
                    ->whereDate('started_at', '<=', $viewing_day)
                    ->pluck('name', 'id')
                    ->toArray();

        $pass_to_view = [
            'teachers'  => $this->teachers,
            'slots'     => $slots,
            'rooms'     => $rooms,
            'schedules' => $schedules,
            'classes'   => $classes,
            'subjects'  => $this->subjects,
            'request'   => $request,
            'dates'     => compact('today', 'previous_day', 'next_day', 'viewing_day', 'weekday')
        ];
        
        return view('schedules/index', $pass_to_view);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = array_filter($request->all());

        $data['branch_id'] = 1;

        // Convert Slot to time
        $slot_id                = intval($data['slot_id']);
        $slot_time              = get_slot_time($slot_id);
        $date                   = $data['started_at'];

        if ( ! isset($data['id'])) {
            $data['started_at']    .= ' ' .$slot_time[0];
            $data['finished_at']    = $date . ' ' . $slot_time[1];

            $data['started_at']     = Carbon::parse($data['started_at'])->format('Y-m-d H:i:s');
            $data['finished_at']    = Carbon::parse($data['finished_at'])->format('Y-m-d H:i:s');
        }

        // Check if class is already learn in same slot, same time
        $conflict = Schedule::isClassConflict($data['class_id'], $date, $data['slot_id']);
        if ($conflict)
            return 'conflict';
      
        if ( ! isset($data['id']) )
            return Schedule::create($data);

        $schedule = Schedule::findOrFail($data['id']);

        unset($data['updated_at']);
        
        $schedule->update($data);

        return $schedule;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Schedule::findOrFail($id)->delete();
            return 'success';
        } catch (Exception $e) {
            return 'error';
        }
    }
}
