<?php

namespace App\Events;

use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a teacher logs in.
 * Broadcasts to teacher-monitoring.{school_year_id} channel.
 * (Requirement 13.2)
 */
class TeacherLoggedIn implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The teacher who logged in.
     */
    public User $teacher;

    /**
     * The teacher attendance record created/updated.
     */
    public TeacherAttendance $attendance;

    /**
     * Create a new event instance.
     */
    public function __construct(User $teacher, TeacherAttendance $attendance)
    {
        $this->teacher = $teacher;
        $this->attendance = $attendance;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('teacher-monitoring.' . $this->attendance->school_year_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'teacher' => [
                'id' => $this->teacher->id,
                'username' => $this->teacher->username,
                'full_name' => $this->teacher->full_name,
                'role' => $this->teacher->role,
            ],
            'attendance' => [
                'id' => $this->attendance->id,
                'attendance_date' => $this->attendance->attendance_date->toDateString(),
                'time_in' => $this->attendance->time_in?->toDateTimeString(),
                'attendance_status' => $this->attendance->attendance_status,
                'school_year_id' => $this->attendance->school_year_id,
            ],
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'teacher.logged_in';
    }
}
