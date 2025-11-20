<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EmailConfiguration;
use App\Services\MailConfigurationService;
use Illuminate\Support\Facades\Mail;

class EmailConfigurationController extends Controller
{
    public function edit()
    {
        $this->authorize('update', EmailConfiguration::class);
        $config = Auth::user()->business->emailConfiguration;
        return view('email-configuration.edit', compact('config'));
    }

    public function update(Request $request)
    {
        $this->authorize('update', EmailConfiguration::class);
        $validated = $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'required|string',
            'password' => 'required|string',
            'encryption' => 'required|in:tls,ssl,starttls',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);
        
        $business = Auth::user()->business;
        $business->emailConfiguration()->updateOrCreate(
            ['business_id' => $business->id],
            $validated
        );

        return redirect()->back()->with('success', 'Email configuration saved successfully.');
    }

    /**
     * ** UPDATED FUNCTION TO TEST EMAIL SETTINGS **
     */
    public function test(MailConfigurationService $mailConfigService)
    {
        $this->authorize('update', EmailConfiguration::class);
        $business = Auth::user()->business;

        // ** 1. FIX: Get the logged-in user's email dynamically **
        $recipientEmail = Auth::user()->email;
        
        try {
            // Load the business-specific mail settings
            $mailConfigService->setBusinessMailConfig($business->id);
            
            // Attempt to send a simple text email
            Mail::raw('This is a test email from your HR application. If you received this, your SMTP settings are correct!', function ($message) use ($recipientEmail) {
                $message->to($recipientEmail)
                        ->subject('Test Email from HR Application');
            });

            // ** 2. FIX: Return a proper redirect with a success message **
            return redirect()->back()->with('success', "Test email sent successfully! Please check your inbox at {$recipientEmail}.");

        } catch (\Exception $e) {
            // ** 3. FIX: Return a proper redirect with the error message **
            return redirect()->back()->with('error', "Email Failed to Send: " . $e->getMessage());
        }
    }
}