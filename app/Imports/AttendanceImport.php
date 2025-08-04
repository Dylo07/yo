<?php

namespace App\Imports;

use App\Models\Staff;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceImport implements ToCollection
{
    private $month;

    public function __construct($month)
    {
        $this->month = $month;
        Log::info('AttendanceImport initialized', ['month' => $this->month]);
    }

    public function collection(Collection $rows)
    {
        Log::info('Processing Excel rows', ['total_rows' => $rows->count()]);

        // Skip the header row (first row)
        $dataRows = $rows->skip(1);
        
        foreach ($dataRows as $rowIndex => $row) {
            try {
                // Skip empty rows
                if ($row->filter()->isEmpty()) {
                    continue;
                }

                // Get staff code and name from first two columns
                $staffCode = $row[0] ?? null;
                $staffName = $row[1] ?? null;

                if (empty($staffCode) && empty($staffName)) {
                    Log::warning('Skipping row with no staff info', ['row' => $rowIndex + 2]);
                    continue;
                }

                // Find staff member
                $staff = null;
                if ($staffCode) {
                    $staff = Staff::where('staff_code', $staffCode)->first();
                }
                if (!$staff && $staffName) {
                    $staff = Staff::where('name', 'LIKE', '%' . trim($staffName) . '%')->first();
                }

                if (!$staff) {
                    Log::warning('Staff not found', [
                        'staff_code' => $staffCode,
                        'staff_name' => $staffName,
                        'row' => $rowIndex + 2
                    ]);
                    continue;
                }

                Log::info('Processing staff', [
                    'staff_id' => $staff->id,
                    'staff_code' => $staff->staff_code,
                    'name' => $staff->name
                ]);

                // Process each day column (starting from column index 2)
                for ($day = 1; $day <= 31; $day++) {
                    $columnIndex = $day + 1; // Column 2 = day 1, Column 3 = day 2, etc.
                    
                    if (!isset($row[$columnIndex])) {
                        continue;
                    }
                    
                    $cellValue = $row[$columnIndex];
                    
                    if (empty($cellValue) || strtolower(trim($cellValue)) === 'absent') {
                        continue;
                    }

                    // Create date for this day
                    try {
                        $date = Carbon::createFromFormat('Y-m-d', $this->month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT));
                    } catch (\Exception $e) {
                        // Skip invalid dates (like Feb 30th, etc.)
                        continue;
                    }

                    // ENHANCED: Parse times from cell with multiple formats
                    $timeString = trim($cellValue);
                    
                    // Handle different separators: space, comma, semicolon, newline, tab
                    $times = preg_split('/[\s,;\n\r\t]+/', $timeString);
                    $times = array_filter($times); // Remove empty elements
                    
                    // Validate times and clean them
                    $validTimes = [];
                    foreach ($times as $time) {
                        $time = trim($time);
                        
                        // Handle different time formats
                        if (preg_match('/^([0-1]?[0-9]|2[0-3]):([0-5][0-9])$/', $time)) {
                            // Format: HH:MM
                            $validTimes[] = $time;
                        } elseif (preg_match('/^([0-1]?[0-9]|2[0-3])\.([0-5][0-9])$/', $time)) {
                            // Format: HH.MM (convert to HH:MM)
                            $validTimes[] = str_replace('.', ':', $time);
                        } elseif (preg_match('/^([0-1]?[0-9]|2[0-3])([0-5][0-9])$/', $time) && strlen($time) >= 3) {
                            // Format: HHMM (add colon)
                            $hour = substr($time, 0, -2);
                            $minute = substr($time, -2);
                            $validTimes[] = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . $minute;
                        }
                    }

                    if (empty($validTimes)) {
                        Log::warning('No valid times found', [
                            'staff_id' => $staff->id,
                            'day' => $day,
                            'cell_value' => $cellValue,
                            'parsed_times' => $times
                        ]);
                        continue;
                    }

                    // Sort times chronologically
                    sort($validTimes);

                    // Create or update attendance record
                    $attendanceData = [
                        'staff_id' => $staff->id,
                        'date' => $date->format('Y-m-d'),
                        'raw_data' => implode(' ', $validTimes),
                        'check_in' => $validTimes[0] ?? null,
                        'check_out' => isset($validTimes[1]) ? $validTimes[1] : null,
                        'status' => $this->calculateStatus($validTimes[0] ?? null)
                    ];

                    $attendance = Attendance::updateOrCreate(
                        [
                            'staff_id' => $staff->id,
                            'date' => $date->format('Y-m-d')
                        ],
                        $attendanceData
                    );

                    Log::info('Attendance record created/updated', [
                        'attendance_id' => $attendance->id,
                        'staff_id' => $staff->id,
                        'date' => $date->format('Y-m-d'),
                        'original_cell' => $cellValue,
                        'parsed_times' => $times,
                        'valid_times' => $validTimes,
                        'raw_data' => $attendanceData['raw_data']
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Error processing row', [
                    'row' => $rowIndex + 2,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info('AttendanceImport completed');
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
}