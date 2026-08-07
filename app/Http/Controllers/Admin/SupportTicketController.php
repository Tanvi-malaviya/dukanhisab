<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Mail;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $tickets = $query->latest()->paginate(10)->withQueryString();

        return view('admin.support.index', compact('tickets'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate([
            'admin_reply' => 'required|string|max:2000',
        ]);

        $ticket->update([
            'admin_reply' => $request->input('admin_reply'),
            'status' => 'inProgress',
            'replied_at' => now(),
        ]);

        $ticket->load('user');
        if ($ticket->user) {
            try {
                Mail::send('shopowner.emails.support-ticket-replied', [
                    'user' => $ticket->user,
                    'ticket' => $ticket,
                ], function ($message) use ($ticket) {
                    $message->to($ticket->user->email)
                            ->subject('Reply to Your Support Ticket #' . $ticket->id);
                });
            } catch (\Exception $e) {
                \Log::error('Support ticket reply email failed: ' . $e->getMessage());
            }
        }

        AuditLog::log("Replied to support ticket #{$ticket->id} (Subject: {$ticket->subject})");

        return back()->with('success', 'Reply submitted and ticket status updated to In Progress.');
    }

    public function updateStatus($id, $status)
    {
        $ticket = SupportTicket::findOrFail($id);

        if (!in_array($status, ['open', 'pending', 'inProgress', 'resolved', 'closed'])) {
            return back()->with('error', 'Invalid status.');
        }

        $ticket->update(['status' => $status]);

        AuditLog::log("Updated status of support ticket #{$ticket->id} to {$status}");

        return back()->with('success', "Ticket status updated to {$status}.");
    }

    public function destroy($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->delete();

        AuditLog::log("Deleted support ticket #{$id}");

        return back()->with('success', 'Ticket deleted successfully.');
    }
}
