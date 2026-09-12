<?php

namespace App\Http\Controllers\Api\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SupportTicketApiController extends Controller
{
    /**
     * List the authenticated user's own support tickets.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $tickets = $user->supportTickets()->latest()->get()->map(function ($ticket) {
            $ticket->screenshot_url = $ticket->screenshot ? asset('storage/' . $ticket->screenshot) : null;
            return $ticket;
        });

        return response()->json([
            'status' => true,
            'message' => 'Tickets retrieved successfully.',
            'tickets' => $tickets,
            'data' => $tickets,
        ]);
    }

    /**
     * View a single support ticket, including any admin reply.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $ticket = $user->supportTickets()->find($id);

        if (!$ticket) {
            return response()->json(['status' => false, 'message' => 'Support ticket not found.'], 404);
        }

        $ticket->screenshot_url = $ticket->screenshot ? asset('storage/' . $ticket->screenshot) : null;

        return response()->json([
            'status' => true,
            'ticket' => $ticket,
            'data' => $ticket,
        ]);
    }

    /**
     * Create a new support ticket and notify the support team by email.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // Accept flexible keys from both mobile app and web (subject/title, message/description)
        $subject = $request->input('subject') ?? $request->input('title');
        $message = $request->input('message') ?? $request->input('description') ?? $request->input('body');

        $dataToValidate = [
            'subject' => $subject,
            'message' => $message,
        ];

        $validator = Validator::make($dataToValidate, [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle screenshot or attachment file upload
        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store('support-tickets', 'public');
        } elseif ($request->hasFile('image')) {
            $screenshotPath = $request->file('image')->store('support-tickets', 'public');
        } elseif ($request->hasFile('attachment')) {
            $screenshotPath = $request->file('attachment')->store('support-tickets', 'public');
        } elseif ($request->filled('screenshot') && is_string($request->input('screenshot'))) {
            $screenshotPath = $request->input('screenshot');
        }

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => $subject,
            'message' => $message,
            'screenshot' => $screenshotPath,
            'status' => 'open',
        ]);

        $ticket->screenshot_url = $ticket->screenshot ? asset('storage/' . $ticket->screenshot) : null;

        // Try sending notification email to active admins, guarded against any exception
        try {
            $adminEmails = Admin::where('status', 'active')->pluck('email');
            if ($adminEmails->isNotEmpty()) {
                Mail::send('shopowner.emails.support-ticket-created', [
                    'user' => $user,
                    'ticket' => $ticket,
                ], function ($mail) use ($adminEmails, $ticket) {
                    $mail->to($adminEmails->all())
                         ->subject('New Support Ticket #' . $ticket->id . ': ' . $ticket->subject);
                });
            }
        } catch (\Throwable $e) {
            \Log::error('Support ticket notification email failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Support ticket submitted successfully.',
            'ticket' => $ticket,
            'data' => $ticket,
        ], 201);
    }

    /**
     * Edit a ticket's subject/message, only while it hasn't been replied to yet.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $ticket = $user->supportTickets()->find($id);

        if (!$ticket) {
            return response()->json(['status' => false, 'message' => 'Support ticket not found.'], 404);
        }

        if ($ticket->status !== 'open') {
            return response()->json(['status' => false, 'message' => 'This ticket has already been picked up and can no longer be edited.'], 422);
        }

        $subject = $request->input('subject') ?? $request->input('title') ?? $ticket->subject;
        $message = $request->input('message') ?? $request->input('description') ?? $ticket->message;

        $validator = Validator::make([
            'subject' => $subject,
            'message' => $message,
        ], [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('screenshot')) {
            $ticket->screenshot = $request->file('screenshot')->store('support-tickets', 'public');
        } elseif ($request->hasFile('image')) {
            $ticket->screenshot = $request->file('image')->store('support-tickets', 'public');
        }

        $ticket->subject = $subject;
        $ticket->message = $message;
        $ticket->save();

        $ticket->screenshot_url = $ticket->screenshot ? asset('storage/' . $ticket->screenshot) : null;

        return response()->json([
            'status' => true,
            'message' => 'Support ticket updated.',
            'ticket' => $ticket,
            'data' => $ticket
        ]);
    }

    /**
     * Delete one of the authenticated user's own tickets.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $ticket = $user->supportTickets()->find($id);

        if (!$ticket) {
            return response()->json(['status' => false, 'message' => 'Support ticket not found.'], 404);
        }

        $ticket->delete();

        return response()->json([
            'status' => true,
            'message' => 'Support ticket deleted.'
        ]);
    }
}
