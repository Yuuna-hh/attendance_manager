<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_休憩ボタンが正しく機能する()
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
        $beforeResponse->assertSee('休憩入');

        Carbon::setTestNow(Carbon::create(2026, 4, 14, 12, 0, 0));

        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'break_start',
        ]);

        $response->assertRedirect('/attendance');

        $afterResponse = $this->actingAs($user)->get('/attendance');
        $afterResponse->assertStatus(200);
        $afterResponse->assertSee('休憩中');

        $attendance = Attendance::where('user_id', $user->id)->first();

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
        ]);

        $this->assertEquals('on_break', $attendance->fresh()->status);

        Carbon::setTestNow();
    }

    public function test_休憩は一日に何回でもできる()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 4, 14, 12, 0, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);

        Carbon::setTestNow(Carbon::create(2026, 4, 14, 13, 0, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩入');

        Carbon::setTestNow();
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 4, 14, 12, 0, 0));
        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_start',
        ]);

        $beforeResponse = $this->actingAs($user)->get('/attendance');
        $beforeResponse->assertStatus(200);
        $beforeResponse->assertSee('休憩戻');

        Carbon::setTestNow(Carbon::create(2026, 4, 14, 13, 0, 0));

        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'break_end',
        ]);

        $response->assertRedirect('/attendance');

        $afterResponse = $this->actingAs($user)->get('/attendance');
        $afterResponse->assertStatus(200);
        $afterResponse->assertSee('出勤中');

        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertEquals('working', $attendance->fresh()->status);

        $break = BreakTime::first();
        $this->assertNotNull($break->break_end);

        Carbon::setTestNow();
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 4, 14, 12, 0, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);

        Carbon::setTestNow(Carbon::create(2026, 4, 14, 13, 0, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);

        Carbon::setTestNow(Carbon::create(2026, 4, 14, 15, 0, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        Carbon::setTestNow();
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 4, 14, 12, 0, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);

        Carbon::setTestNow(Carbon::create(2026, 4, 14, 13, 0, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('01:00');

        Carbon::setTestNow();
    }
}