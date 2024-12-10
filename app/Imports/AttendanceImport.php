<?php

namespace App\Imports;

use App\Models\Staff;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\ToArray;
use Carbon\Carbon;

class AttendanceImport implements ToArray
{
   private $targetMonth;

   public function __construct($month = null) 
   {
       $this->targetMonth = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();
   }

   public function array(array $rows)
   {
       // Skip the header row
       $dataRows = array_slice($rows, 1);
       
       foreach ($dataRows as $row) {
           if (empty($row[0])) continue;
           
           $staffCode = $row[0];
           $staff = Staff::where('staff_code', $staffCode)->first();
           
           if (!$staff) continue;
           
           // Process columns 2 through 31 (days of month)
           for ($column = 2; $column <= 31; $column++) {
               if (!isset($row[$column]) || empty($row[$column])) continue;
               
               $dayOfMonth = $column - 1;
               $date = $this->targetMonth->copy()
                   ->startOfMonth()
                   ->addDays($dayOfMonth - 1)
                   ->format('Y-m-d');
               
               // Format raw data
               $rawData = $row[$column];
               // Split times that are on new lines or spaces
               $times = array_filter(preg_split('/[\s\n]+/', trim($rawData)));
               
               // Store multiple times as separate values
               $formattedData = implode(' ', $times);
               
               try {
                   Attendance::updateOrCreate(
                       [
                           'staff_id' => $staff->id,
                           'date' => $date
                       ],
                       [
                           'raw_data' => $formattedData
                       ]
                   );
               } catch (\Exception $e) {
                   \Log::error("Error importing attendance for staff {$staffCode} on {$date}: " . $e->getMessage());
                   continue;
               }
           }
       }
   }
}