<?php

namespace Tests\Feature;

use App\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_send_feedback(): void
    {
        $this->from('/')
            ->post('/feedback', ['name' => 'Zhyaw', 'message' => 'Great library.'])
            ->assertRedirect('/')
            ->assertSessionHas('feedback_sent', true);

        $this->assertDatabaseHas('feedback', ['name' => 'Zhyaw', 'message' => 'Great library.']);
    }

    public function test_the_name_is_optional(): void
    {
        $this->from('/')->post('/feedback', ['message' => 'Anonymous note.'])->assertRedirect('/');

        $this->assertSame(1, Feedback::where('name', null)->count());
    }

    public function test_the_message_is_required(): void
    {
        $this->from('/')
            ->post('/feedback', ['name' => 'Zhyaw'])
            ->assertRedirect('/')
            ->assertSessionHasErrors('message');

        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_an_over_long_message_is_rejected(): void
    {
        $this->from('/')
            ->post('/feedback', ['message' => str_repeat('a', 2001)])
            ->assertSessionHasErrors('message');

        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_an_over_long_name_is_rejected(): void
    {
        $this->from('/')
            ->post('/feedback', ['name' => str_repeat('a', 121), 'message' => 'Hello.'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_only_the_name_and_message_can_be_set(): void
    {
        $this->from('/')->post('/feedback', [
            'message' => 'Hello.',
            'id' => 999,
        ]);

        $this->assertDatabaseMissing('feedback', ['id' => 999]);
    }

    public function test_the_confirmation_is_shown_on_the_home_page(): void
    {
        $this->from('/')->post('/feedback', ['message' => 'Hello.']);

        $this->withSession(['locale' => 'en', 'feedback_sent' => true])
            ->get('/')
            ->assertSee(__('messages.feedback.success', [], 'en'));
    }
}
