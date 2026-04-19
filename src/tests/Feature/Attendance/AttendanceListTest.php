<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分が行った勤怠情報が全て表示されている()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01 00:00:00',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 18:00:00',
            'status' => 'finished',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-02 00:00:00',
            'clock_in' => '2026-04-02 09:30:00',
            'clock_out' => '2026-04-02 18:30:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-04');

        $response->assertStatus(200);
        $response->assertSee('04/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('04/02');
        $response->assertSee('09:30');
        $response->assertSee('18:30');

        Carbon::setTestNow();
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026/04');

        Carbon::setTestNow();
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('2026/03');

        Carbon::setTestNow();
    }

    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-05');

        $response->assertStatus(200);
        $response->assertSee('2026/05');

        Carbon::setTestNow();
    }

    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 9, 0, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-14 00:00:00',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        $listResponse = $this->actingAs($user)->get('/attendance/list?month=2026-04');

        $listResponse->assertStatus(200);
        $listResponse->assertSee('詳細');

        $detailResponse = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('09:00');
        $detailResponse->assertSee('18:00');

        Carbon::setTestNow();
    }
}