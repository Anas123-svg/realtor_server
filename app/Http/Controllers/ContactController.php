<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Admin;  // Make sure Admin model exists
use App\Mail\ContactMail;
use App\Mail\ContactUs;
use App\Mail\PropertyEvalAdmin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Import Log facade

class ContactController extends Controller
{
    public function sendAgentEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:1000',
            'adminId' => 'required|exists:admins,id', 
        ]);
    
        if ($validator->fails()) {
            Log::error('Email validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $admin = Admin::find($request->adminId);
    
        if (!$admin) {
            Log::error('Admin not found', ['adminId' => $request->adminId]);
            return response()->json(['error' => 'Admin not found.'], 404);
        }
    
        $emailData = [
            'userName' => $request->name,
            'userEmail' => $request->email,
            'userPhone' => $request->phone,
            'userMessage' => $request->message,
        ];
    
        try {
            // Send email to admin
            Mail::to($admin->email)->send(new ContactMail($emailData));
            Log::info('Email sent successfully to admin', ['adminEmail' => $admin->email, 'emailData' => $emailData]);
    
            Mail::raw("Hello {$request->name},\n\nThank you for reaching out to us! We have received your message, and our team will contact you shortly.\n\nBest regards,\nThe Realtor Team", function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Message Received Confirmation');
            });
    
            Log::info('Confirmation email sent successfully to user', ['userEmail' => $request->email]);
    
            return response()->json(['message' => 'Email sent successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending email', [
                'adminEmail' => $admin->email,
                'userEmail' => $request->email,
                'emailData' => $emailData,
                'exception' => $e->getMessage()
            ]);
            return response()->json(['error' => 'An error occurred while sending the email.'], 500);
        }
    }



    public function sendContactEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'reason'=> 'nullable|string|max:255',
        ]);
    
        if ($validator->fails()) {
            Log::error('Email validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $admins = Admin::all();
    
        if ($admins->isEmpty()) {
            Log::error('No admins found to send email');
            return response()->json(['error' => 'No admins found.'], 404);
        }
    
        $emailData = [
            'userName' => $request->name,
            'userEmail' => $request->email,
            'userPhone' => $request->phone,
            'userCountry' => $request->country,
            'userCity' => $request->city,
            'userMessage' => $request->message,
            'reason' => $request->reason,
        ];
    
        try {
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new ContactUs($emailData));
                Log::info('Email sent successfully to admin', ['adminEmail' => $admin->email, 'emailData' => $emailData]);
            }
    
            Mail::raw(
                "Hello {$request->name},\n\nThank you for reaching out to us! We have received your message, and our team will contact you shortly.\n\nBest regards,\nThe Realtor Team",
                function ($message) use ($request) {
                    $message->to($request->email)
                            ->subject('Message Received Confirmation');
                }
            );
    
            Log::info('Confirmation email sent successfully to user', ['userEmail' => $request->email]);
    
            return response()->json(['message' => 'Emails sent successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending email', [
                'emailData' => $emailData,
                'exception' => $e->getMessage()
            ]);
            return response()->json(['error' => 'An error occurred while sending the email.'], 500);
        }
    }

    public function propertyEvalEmail(Request $request)
{
    // Validate the input data
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:20',
        'country' => 'required|string|max:255',
        'dealType' => 'required|string|max:255',
        'propertyType' => 'required|string|max:255',
        'propertyTitle' => 'required|string|max:255',
        'propertySize' => 'required|string|max:255',
        'location' => 'required|string|max:255',
        'bedrooms'=> 'nullable|integer|max:255',
        'bathrooms'=> 'nullable|integer|max:255',
        'images' => 'nullable|array', 
        'images.*' => 'nullable|string|max:255',

    ]);

    if ($validator->fails()) {
        Log::error('Email validation failed', ['errors' => $validator->errors()]);
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $admins = Admin::all();

    if ($admins->isEmpty()) {
        Log::error('No admins found to send email');
        return response()->json(['error' => 'No admins found.'], 404);
    }

    // Prepare the data for the email
    $emailData = [
        'userName' => $request->name,
        'userEmail' => $request->email,
        'userPhone' => $request->phone,
        'userCountry' => $request->country,
        'location' => $request->location,
        'dealType' => $request->dealType,
        'propertyType' => $request->propertyType,
        'propertyTitle' => $request->propertyTitle,
        'propertySize' => $request->propertySize,
        'bedrooms' => $request->bedrooms,
        'bathrooms' => $request->bathrooms,
    ];
    if ($request->has('images')) {
        $emailData['images'] = $request->images; // Directly assign the array of image URLs
    }

    try {
        // Send email to admins
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new PropertyEvalAdmin($emailData));
            Log::info('Email sent successfully to admin', ['adminEmail' => $admin->email, 'emailData' => $emailData]);
        }

        // Send confirmation email to the user
        Mail::raw(
            "Hello {$request->name},\n\nThank you for reaching out to us! We have received your message, and our team will contact you shortly.\n\nBest regards,\nThe Realtor Team",
            function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Message Received Confirmation');
            }
        );        
        Log::info('Confirmation email sent successfully to user', ['userEmail' => $request->email]);

        return response()->json(['message' => 'Emails sent successfully.'], 200);
    } catch (\Exception $e) {
        Log::error('Exception occurred while sending email', [
            'emailData' => $emailData,
            'exception' => $e->getMessage()
        ]);
        return response()->json(['error' => 'An error occurred while sending the email.'], 500);
    }
}

}
