<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_退勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $beforeResponse = $this->actingAs($user)->get('/attendance');
        $beforeResponse->assertStatus(200);
        $beforeResponse->assertSee('退勤');

        Carbon::setTestNow(Carbon::create(2026, 4, 14, 18, 0, 0));

        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_out',
        ]);

        $response->assertRedirect('/attendance');

        $afterResponse = $this->actingAs($user)->get('/attendance');
        $afterResponse->assertStatus(200);
        $afterResponse->assertSee('退勤済');
        $afterResponse->assertSee('お疲れ様でした。');

        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertNotNull($attendance->clock_out);
        $this->assertEquals('finished', $attendance->status);

        Carbon::setTestNow();
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 4, 14, 18, 0, 0));
        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_out',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}