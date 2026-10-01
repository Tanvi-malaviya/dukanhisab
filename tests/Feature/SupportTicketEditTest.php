<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Web and app edit a ticket the same way: a multipart POST spoofed as PUT (PHP doesn't parse
 * multipart PUT bodies, which used to silently drop the text and the new screenshot).
 */
class SupportTicketEditTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Mail::fake();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    private function ticket(array $o = []): SupportTicket
    {
        return SupportTicket::create($o + ['user_id' => $this->user->id, 'subject' => 'Old', 'message' => 'Old body', 'status' => 'open']);
    }

    public function test_multipart_edit_with_method_spoofing_saves_text_and_screenshot(): void
    {
        $t = $this->ticket();

        $this->post("/api/v1/shopowner/support-tickets/{$t->id}", [
            '_method' => 'PUT',
            'subject' => 'New subject',
            'message' => 'New body',
            'screenshot' => UploadedFile::fake()->image('shot.png'),
        ], ['Accept' => 'application/json'])->assertStatus(200);

        $t->refresh();
        $this->assertSame('New subject', $t->subject);
        $this->assertSame('New body', $t->message);
        $this->assertNotNull($t->screenshot);
        Storage::disk('public')->assertExists($t->screenshot);
    }

    public function test_the_mobile_alias_route_accepts_the_same_edit(): void
    {
        $t = $this->ticket();
        $this->post("/api/v1/support-tickets/{$t->id}", ['_method' => 'PUT', 'subject' => 'S2', 'message' => 'M2'], ['Accept' => 'application/json'])
            ->assertStatus(200);
        $this->assertSame('S2', $t->fresh()->subject);
    }

    public function test_a_ticket_support_has_picked_up_cannot_be_edited(): void
    {
        $t = $this->ticket(['status' => 'inProgress']);
        $this->putJson("/api/v1/shopowner/support-tickets/{$t->id}", ['subject' => 'x', 'message' => 'y'])->assertStatus(422);
        $this->assertSame('Old', $t->fresh()->subject);
    }

    public function test_reply_adds_a_message_to_the_thread(): void
    {
        $t = $this->ticket();
        $r = $this->postJson("/api/v1/shopowner/support-tickets/{$t->id}/reply", ['message' => 'Any update?'])->assertStatus(200);
        $this->assertSame('Any update?', $r->json('ticket.messages.0.message'));
        $this->assertSame('user', $r->json('ticket.messages.0.sender_type'));
    }
}
