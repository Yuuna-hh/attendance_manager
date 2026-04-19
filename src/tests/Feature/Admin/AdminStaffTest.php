<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者ユーザーが全一般ユーザーの氏名とメールアドレスを確認できる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'role' => 'general',
        ]);

        User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
            'role' => 'general',
        ]);

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('yamada@example.com');
        $response->assertSee('佐藤花子');
        $response->assertSee('sato@example.com');
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $generalUser = User::factory()->create([
            'name' => '一般ユーザー',
            'role' => 'general',
        ]);

        Attendance::create([
            'user_id' => $generalUser->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$generalUser->id}?month=2026-04");

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 12, 0, 0));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $generalUser = User::factory()->create([
            'role' => 'general',
        ]);

        $listResponse = $this->actingAs($admin)->get("/admin/attendance/staff/{$generalUser->id}?month=2026-04");

        $listResponse->assertStatus(200);
        $listResponse->assertSee('前月');

        $prevMonthResponse = $this->actingAs($admin)->get("/admin/attendance/staff/{$generalUser->id}?month=2026-03");

        $prevMonthResponse->assertStatus(200);
        $prevMonthResponse->assertSee('2026/03');

        Carbon::setTestNow();
    }

    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14, 12, 0, 0));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $generalUser = User::factory()->create([
            'role' => 'general',
        ]);

        $listResponse = $this->actingAs($admin)->get("/admin/attendance/staff/{$generalUser->id}?month=2026-04");

        $listResponse->assertStatus(200);
        $listResponse->assertSee('翌月');

        $nextMonthResponse = $this->actingAs($admin)->get("/admin/attendance/staff/{$generalUser->id}?month=2026-05");

        $nextMonthResponse->assertStatus(200);
        $nextMonthResponse->assertSee('2026/05');

        Carbon::setTestNow();
    }

    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $generalUser = User::factory()->create([
            'role' => 'general',
        ]);

        $attendance = Attendance::create([
            'user_id' => $generalUser->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        $listResponse = $this->actingAs($admin)->get("/admin/attendance/staff/{$generalUser->id}?month=2026-04");

        $listResponse->assertStatus(200);
        $listResponse->assertSee('詳細');

        $detailResponse = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('09:00');
        $detailResponse->assertSee('18:00');
    }
}