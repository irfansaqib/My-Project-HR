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
     * ** NEW FUNCTION TO TEST EMAIL SETTINGS **
     */
    public function test(MailConfigurationService $mailConfigService)
    {
        $this->authorize('update', EmailConfiguration::class);
        $business = Auth::user()->business;

        // ** IMPORTANT: Change this to your personal email address for testing **
        $recipientEmail = 'irfansaqib01@gmail.com';
        
        try {
            // Load the business-specific mail settings
            $mailConfigService->setBusinessMailConfig($business->id);
            
            // Attempt to send a simple text email
            Mail::raw('This is a test email from your HR application. If you received this, your SMTP settings are correct!', function ($message) use ($recipientEmail) {
                $message->to($recipientEmail)
                        ->subject('Test Email from HR Application');
            });

            return "<h1>Success!</h1><p>Test email sent successfully! Please check your inbox at <strong>{$recipientEmail}</strong>. If it's not there, check your spam folder.</p>";

        } catch (\Exception $e) {
            // If it fails, display the exact error message
            return "<h1>Email Failed to Send</h1><p>The system returned the following error:</p><pre style='background-color:#f8d7da; color:#721c24; padding:15px; border-radius:5px; white-space: pre-wrap; word-wrap: break-word;'>" . $e->getMessage() . "</pre>";
        }
    }
}