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
        $tickets = $request->user()->supportTickets()->latest()->get();

        return response()->json(['tickets' => $tickets]);
    }

    /**
     * View a single support ticket, including any admin reply.
     */
    public function show(Request $request, $id)
    {
        $ticket = $request->user()->supportTickets()->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Support ticket not found.'], 404);
        }

        return response()->json(['ticket' => $ticket]);
    }

    /**
     * Create a new support ticket and notify the support team by email.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
            'status' => 'open',
        ]);

        $adminEmails = Admin::where('status', 'active')->pluck('email');

        if ($adminEmails->isNotEmpty()) {
            try {
                Mail::send('shopowner.emails.support-ticket-created', [
                    'user' => $user,
                    'ticket' => $ticket,
                ], function ($message) use ($adminEmails, $ticket) {
                    $message->to($adminEmails->all())
                            ->subject('New Support Ticket #' . $ticket->id . ': ' . $ticket->subject);
                });
            } catch (\Exception $e) {
                \Log::error('Support ticket notification email failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Support ticket submitted successfully.',
            'ticket' => $ticket,
        ], 201);
    }

    /**
     * Edit a ticket's subject/message, only while it hasn't been replied to yet.
     */
    public function update(Request $request, $id)
    {
        $ticket = $request->user()->supportTickets()->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Support ticket not found.'], 404);
        }

        if ($ticket->status !== 'open') {
            return response()->json(['message' => 'This ticket has already been picked up and can no longer be edited.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket->update($request->only(['subject', 'message']));

        return response()->json(['message' => 'Support ticket updated.', 'ticket' => $ticket]);
    }

    /**
     * Delete one of the authenticated user's own tickets.
     */
    public function destroy(Request $request, $id)
    {
        $ticket = $request->user()->supportTickets()->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Support ticket not found.'], 404);
        }

        $ticket->delete();

        return response()->json(['message' => 'Support ticket deleted.']);
    }
}
