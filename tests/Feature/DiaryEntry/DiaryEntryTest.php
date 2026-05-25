<?php

declare(strict_types=1);

namespace Tests\Feature\DiaryEntry;

use App\Jobs\SendDiaryEntryCreatedNotification;
use App\Mail\DiaryEntryCreatedMail;
use App\Models\DiaryEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DiaryEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_diary_entries(): void
    {
        $this->getJson('/api/diary-entries')->assertUnauthorized();
    }

    public function test_user_can_list_their_diary_entries_with_pagination(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        DiaryEntry::factory()->count(3)->for($user)->create();
        DiaryEntry::factory()->count(2)->for($otherUser)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/diary-entries?per_page=2');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['id', 'title', 'content', 'mood', 'entry_date', 'tags'],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_creating_published_entry_queues_notification_email(): void
    {
        Mail::fake();
        Queue::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/diary-entries', [
            'title' => 'Morning walk',
            'content' => 'A calm start to the day.',
            'entry_date' => '2026-05-24',
            'is_draft' => false,
        ])->assertCreated();

        Queue::assertPushed(SendDiaryEntryCreatedNotification::class, function ($job) use ($user): bool {
            return $job->user->is($user) && $job->entry->title === 'Morning walk';
        });
    }

    public function test_creating_draft_does_not_queue_notification_email(): void
    {
        Queue::fake();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/diary-entries', [
            'title' => 'Draft note',
            'content' => 'Work in progress.',
            'entry_date' => '2026-05-24',
            'is_draft' => true,
        ])->assertCreated();

        Queue::assertNothingPushed();
    }

    public function test_notification_job_sends_mail(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $entry = DiaryEntry::factory()->for($user)->create(['title' => 'Evening reflection']);

        $job = new SendDiaryEntryCreatedNotification($entry, $user);
        $job->handle();

        Mail::assertSent(DiaryEntryCreatedMail::class, function (DiaryEntryCreatedMail $mail) use ($user, $entry): bool {
            return $mail->hasTo($user->email) && $mail->entry->is($entry);
        });
    }

    public function test_user_can_create_diary_entry(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/diary-entries', [
            'title' => 'Morning walk',
            'content' => 'A calm start to the day.',
            'mood' => 'calm',
            'entry_date' => '2026-05-24',
            'tags' => ['health', 'routine'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.entry.title', 'Morning walk')
            ->assertJsonPath('data.entry.mood', 'calm');

        $this->assertDatabaseHas('diary_entries', [
            'user_id' => $user->id,
            'title' => 'Morning walk',
        ]);
    }

    public function test_create_validates_required_fields(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/diary-entries', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content', 'entry_date']);
    }

    public function test_user_can_view_single_entry(): void
    {
        $user = User::factory()->create();
        $entry = DiaryEntry::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/diary-entries/'.$entry->id)
            ->assertOk()
            ->assertJsonPath('data.entry.id', $entry->id);
    }

    public function test_user_cannot_view_another_users_entry(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $entry = DiaryEntry::factory()->for($owner)->create();

        Sanctum::actingAs($intruder);

        $this->getJson('/api/diary-entries/'.$entry->id)->assertNotFound();
    }

    public function test_user_can_update_their_entry(): void
    {
        $user = User::factory()->create();
        $entry = DiaryEntry::factory()->for($user)->create(['title' => 'Old title']);

        Sanctum::actingAs($user);

        $this->putJson('/api/diary-entries/'.$entry->id, [
            'title' => 'Updated title',
            'mood' => 'happy',
        ])
            ->assertOk()
            ->assertJsonPath('data.entry.title', 'Updated title')
            ->assertJsonPath('data.entry.mood', 'happy');
    }

    public function test_user_cannot_update_another_users_entry(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $entry = DiaryEntry::factory()->for($owner)->create();

        Sanctum::actingAs($intruder);

        $this->putJson('/api/diary-entries/'.$entry->id, [
            'title' => 'Hacked',
        ])->assertNotFound();
    }

    public function test_user_can_delete_their_entry(): void
    {
        $user = User::factory()->create();
        $entry = DiaryEntry::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $this->deleteJson('/api/diary-entries/'.$entry->id)
            ->assertOk();

        $this->assertDatabaseMissing('diary_entries', ['id' => $entry->id]);
    }

    public function test_user_cannot_delete_another_users_entry(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $entry = DiaryEntry::factory()->for($owner)->create();

        Sanctum::actingAs($intruder);

        $this->deleteJson('/api/diary-entries/'.$entry->id)->assertNotFound();
    }

    public function test_index_filters_by_mood_and_search(): void
    {
        $user = User::factory()->create();
        DiaryEntry::factory()->for($user)->create([
            'title' => 'Calm morning',
            'mood' => 'calm',
        ]);
        DiaryEntry::factory()->for($user)->create([
            'title' => 'Busy afternoon',
            'mood' => 'energized',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/diary-entries?mood=calm')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.mood', 'calm');

        $this->getJson('/api/diary-entries?search=Busy')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Busy afternoon');
    }

    public function test_calendar_endpoint_returns_entries_in_date_range(): void
    {
        $user = User::factory()->create();

        DiaryEntry::factory()->for($user)->create([
            'title' => 'In range',
            'entry_date' => '2026-05-10',
            'is_draft' => false,
        ]);
        DiaryEntry::factory()->for($user)->create([
            'title' => 'Out of range',
            'entry_date' => '2026-04-01',
            'is_draft' => false,
        ]);
        DiaryEntry::factory()->for($user)->create([
            'title' => 'Draft in range',
            'entry_date' => '2026-05-12',
            'is_draft' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/diary-entries?calendar=true&from=2026-05-01&to=2026-05-31');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['title' => 'In range'])
            ->assertJsonFragment(['title' => 'Draft in range'])
            ->assertJsonMissing(['title' => 'Out of range']);
    }
}
