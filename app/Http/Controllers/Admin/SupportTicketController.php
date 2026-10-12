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
        $query = SupportTicket::with(['user', 'messages.senderAdmin', 'messages.senderUser']);

        $selectedStatus = $request->input('status', 'open');

        if ($selectedStatus === 'all') {
            // Show all tickets
        } elseif ($selectedStatus === 'closed') {
            $query->whereIn('status', ['closed', 'resolved']);
        } else {
            // Default to 'open'
            $selectedStatus = 'open';
            $query->whereIn('status', ['open', 'pending', 'inProgress']);
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

        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::whereIn('status', ['open', 'pending', 'inProgress'])->count(),
            'closed' => SupportTicket::whereIn('status', ['closed', 'resolved'])->count(),
        ];

        return view('admin.support.index', compact('tickets', 'stats', 'selectedStatus'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate([
            'admin_reply' => 'required|string|max:5000',
            'status' => 'nullable|string|in:open,closed',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf,doc,docx,xls,xlsx,zip|max:10240',
        ]);

        $admin = auth('admin')->user();
        $adminReplyText = $request->input('admin_reply');

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('support-tickets', 'public');
        }

        // Create message in conversation thread
        $ticket->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $admin?->id,
            'message' => $adminReplyText,
            'attachment' => $attachmentPath,
        ]);

        $status = $request->input('status', ($ticket->status === 'closed' ? 'closed' : 'open'));

        $ticket->update([
            'admin_reply' => $adminReplyText,
            'status' => $status,
            'replied_at' => now(),
        ]);

        $ticket->load('user');
        if ($ticket->user && !empty($ticket->user->email)) {
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

        return back()->with('success', 'Reply submitted successfully.');
    }

    public function updateStatus($id, $status)
    {
        $ticket = SupportTicket::findOrFail($id);

        if (!in_array($status, ['open', 'closed'])) {
            return back()->with('error', 'Invalid status. Only Open and Closed are supported.');
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
