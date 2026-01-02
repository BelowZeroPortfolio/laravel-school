<?php

namespace App\Events;

use App\Models\TeacherAttendance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when teacher attendance status changes from pending.
 * Broadcasts to teacher-monitoring.{school_year_id} channel.
 * (Requirement 13.3)
 */
class AttendanceFinalized implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The teacher attendance record that was finalized.
     */
    public TeacherAttendance $attendance;

    /**
     * The previous status before finalization.
     */
    public string $previousStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(TeacherAttendance $attendance, string $previousStatus = 'pending')
    {
        $this->attendance = $attendance;
        $this->previousStatus = $previousStatus;
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
            'attendance' => [
                'id' => $this->attendance->id,
                'teacher_id' => $this->attendance->teacher_id,
                'attendance_date' => $this->attendance->attendance_date->toDateString(),
                'time_in' => $this->attendance->time_in?->toDateTimeString(),
                'first_student_scan' => $this->attendance->first_student_scan?->toDateTimeString(),
                'attendance_status' => $this->attendance->attendance_status,
                'late_status' => $this->attendance->late_status,
                'school_year_id' => $this->attendance->school_year_id,
            ],
            'teacher' => $this->attendance->teacher ? [
                'id' => $this->attendance->teacher->id,
                'username' => $this->attendance->teacher->username,
                'full_name' => $this->attendance->teacher->full_name,
            ] : null,
            'previous_status' => $this->previousStatus,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'attendance.finalized';
    }
}
