<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $beforeResponse = $this->actingAs($user)->get('/attendance');
        $beforeResponse->assertStatus(200);
        $beforeResponse->assertSee('出勤');

        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $response->assertRedirect('/attendance');

        $afterResponse = $this->actingAs($user)->get('/attendance');
        $afterResponse->assertStatus(200);
        $afterResponse->assertSee('出勤中');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2026-04-14 00:00:00',
        ]);

        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertNotNull($attendance->clock_in);

        Carbon::setTestNow();
    }

    public function test_出勤は一日一回のみできる()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 18, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->startOfDay(),
            'clock_in' => Carbon::now()->copy()->setTime(9, 0),
            'clock_out' => Carbon::now()->copy()->setTime(18, 0),
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');

        Carbon::setTestNow();
    }
}