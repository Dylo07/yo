<?php
// Update your StaffSectionPassword.php middleware with this final corrected version:
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class StaffSectionPassword
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user has already entered the correct password
        if (Session::has('staff_section_authenticated') && Session::get('staff_section_authenticated') === true) {
            // If this is a POST request to staff-information (password submission), redirect to GET
            if ($request->isMethod('post') && $request->path() === 'staff-information' && $request->has('staff_password')) {
                return redirect()->route('staff.information');
            }
            return $next($request);
        }

        // If it's a password verification request, handle it
        if ($request->isMethod('post') && $request->has('staff_password')) {
            $enteredPassword = $request->input('staff_password');
            $correctPassword = config('app.staff_section_password', 'hotel2024');
            
            if ($enteredPassword === $correctPassword) {
                // Set session to remember authentication
                Session::put('staff_section_authenticated', true);
                Session::put('staff_section_auth_time', now());
                
                // Redirect to the staff information page with GET method
                return redirect()->route('staff.information');
            } else {
                return redirect()->back()->with('error', 'Incorrect password. Please try again.');
            }
        }

        // Show password form for any GET request
        return response()->view('attendance.manual.staff-password-form', [
            'intended_url' => $request->url()
        ]);
    }
}
