<?php

namespace App\Imports;

use App\Models\Staff;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceImport implements ToCollection, WithHeadingRow
{
    private $month;

    public function __construct($month)
    {
        $this->month = $month;
    }

    public function collection(Collection $rows)
    {
        Log::info('Starting attendance import', [
            'month' => $this->month,
            'total_rows' => $rows->count()
        ]);

        foreach ($rows as $row) {
            try {
                // Get staff code and name from the row
                $staffCode = $row[0] ?? $row['staff_code'] ?? null;
                $staffName = $row[1] ?? $row['name'] ?? null;

                if (empty($staffCode) || empty($staffName)) {
                    Log::warning('Skipping row with missing staff info', [
                        'staff_code' => $staffCode,
                        'name' => $staffName
                    ]);
                    continue;
                }

                // Find the staff member
                $staff = Staff::where('staff_code', $staffCode)
                             ->orWhere('name', 'LIKE', '%' . trim($staffName) . '%')
                             ->first();

                if (!$staff) {
                    Log::warning('Staff not found', [
                        'staff_code' => $staffCode,
                        'name' => $staffName
                    ]);
                    continue;
                }

                Log::info('Processing staff member', [
                    'staff_id' => $staff->id,
                    'staff_code' => $staff->staff_code,
                    'name' => $staff->name
                ]);

                // Process each day column (starting from index 2, which should be day 01)
                $dayColumnIndex = 2; // Start from column C (index 2)
                
                for ($day = 1; $day <= 31; $day++) {
                    $cellValue = $row[$dayColumnIndex] ?? null;
                    
                    if (!empty($cellValue) && strtolower(trim($cellValue)) !== 'absent') {
                        $this->processAttendanceForDay($staff, $day, $cellValue);
                    }
                    
                    $dayColumnIndex++;
                }

            } catch (\Exception $e) {
                Log::error('Error processing row', [
                    'error' => $e->getMessage(),
                    'row_data' => $row->toArray()
                ]);
            }
        }
    }

    private function processAttendanceForDay($staff, $day, $cellValue)
    {
        try {
            // Create the date for this day
            $date = Carbon::createFromFormat('Y-m-d', $this->month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT));
            
            // Parse multiple times from the cell value
            // Handle different formats: "07:31 17:38", "07:31", "07:31 17:38 20:24", etc.
            $timeString = trim($cellValue);
            
            // Split by spaces and filter out empty values
            $times = array_filter(explode(' ', $timeString));
            
            // Validate and clean the times
            $validTimes = [];
            foreach ($times as $time) {
                $time = trim($time);
                // Check if time is in HH:MM format
                if (preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
                    $validTimes[] = $time;
                }
            }
            
            if (empty($validTimes)) {
                Log::warning('No valid times found', [
                    'staff_id' => $staff->id,
                    'day' => $day,
                    'cell_value' => $cellValue
                ]);
                return;
            }

            // Create or update attendance record
            $attendance = Attendance::updateOrCreate(
                [
                    'staff_id' => $staff->id,
                    'date' => $date->format('Y-m-d')
                ],
                [
                    'raw_data' => implode(' ', $validTimes),
                    'check_in' => $validTimes[0] ?? null,
                    'check_out' => isset($validTimes[1]) ? $validTimes[1] : null,
                    'status' => $this->calculateStatus($validTimes[0] ?? null)
                ]
            );

            Log::info('Attendance saved', [
                'staff_id' => $staff->id,
                'date' => $date->format('Y-m-d'),
                'times' => $validTimes,
                'attendance_id' => $attendance->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing attendance for day', [
                'staff_id' => $staff->id,
                'day' => $day,
                'cell_value' => $cellValue,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function calculateStatus($checkInTime)
    {
        if (!$checkInTime) {
            return 'absent';
        }
        
        try {
            $startTime = Carbon::createFromTimeString('08:30:00');
            $checkIn = Carbon::createFromTimeString($checkInTime);
            return $checkIn->gt($startTime) ? 'late' : 'present';
        } catch (\Exception $e) {
            Log::error('Error calculating status', [
                'check_in_time' => $checkInTime,
                'error' => $e->getMessage()
            ]);
            return 'present';
        }
    }

    public function headingRow(): int
    {
        return 1; // The heading row is the first row
    }
}