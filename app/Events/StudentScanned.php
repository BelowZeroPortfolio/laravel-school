<?php

namespace App\Events;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a student scans their QR code.
 * Broadcasts to attendance.{school_year_id} channel.
 * (Requirement 13.1)
 */
class StudentScanned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The student who scanned.
     */
    public Student $student;

    /**
     * The attendance record created.
     */
    public Attendance $attendance;

    /**
     * The attendance status.
     */
    public string $status;

    /**
     * Create a new event instance.
     */
    public function __construct(Student $student, Attendance $attendance, string $status)
    {
        $this->student = $student;
        $this->attendance = $attendance;
        $this->status = $status;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('attendance.' . $this->attendance->school_year_id),
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
            'student' => [
                'id' => $this->student->id,
                'student_id' => $this->student->student_id,
                'lrn' => $this->student->lrn,
                'first_name' => $this->student->first_name,
                'last_name' => $this->student->last_name,
                'full_name' => $this->student->full_name,
            ],
            'attendance' => [
                'id' => $this->attendance->id,
                'attendance_date' => $this->attendance->attendance_date->toDateString(),
                'check_in_time' => $this->attendance->check_in_time->toDateTimeString(),
                'status' => $this->attendance->status,
                'school_year_id' => $this->attendance->school_year_id,
            ],
            'status' => $this->status,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'student.scanned';
    }
}
