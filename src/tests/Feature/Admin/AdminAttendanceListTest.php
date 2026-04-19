<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 12, 0, 0));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user1 = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
        ]);

        Attendance::create([
            'user_id' => $user1->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 10:00:00',
            'clock_out' => '2026-04-14 19:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('佐藤花子');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        Carbon::setTestNow();
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 12, 0, 0));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026');
        $response->assertSee('04/14');

        Carbon::setTestNow();
    }

    public function test_前日を押下した時に前の日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 12, 0, 0));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $listResponse = $this->actingAs($admin)->get('/admin/attendance/list');

        $listResponse->assertStatus(200);
        $listResponse->assertSee('前日');

        $prevDayResponse = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-04-13');

        $prevDayResponse->assertStatus(200);
        $prevDayResponse->assertSee('2026');
        $prevDayResponse->assertSee('04/13');

        Carbon::setTestNow();
    }

    public function test_翌日を押下した時に次の日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 12, 0, 0));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $listResponse = $this->actingAs($admin)->get('/admin/attendance/list');

        $listResponse->assertStatus(200);
        $listResponse->assertSee('翌日');

        $nextDayResponse = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-04-15');

        $nextDayResponse->assertStatus(200);
        $nextDayResponse->assertSee('2026');
        $nextDayResponse->assertSee('04/15');

        Carbon::setTestNow();
    }
}