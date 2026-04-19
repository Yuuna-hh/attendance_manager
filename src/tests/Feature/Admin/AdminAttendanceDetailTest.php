<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-04-14 12:00:00',
            'break_end' => '2026-04-14 13:00:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->from("/admin/attendance/{$attendance->id}")
            ->actingAs($admin)
            ->post("/admin/attendance/{$attendance->id}", [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'note' => '修正理由',
            ]);

        $response->assertRedirect("/admin/attendance/{$attendance->id}");
        $response->assertSessionHasErrors();

        $this->followRedirects($response)
            ->assertSeeText('出勤時間もしくは退勤時間が不適切な値です');
    }

    public function test_休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->from("/admin/attendance/{$attendance->id}")
            ->actingAs($admin)
            ->post("/admin/attendance/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '19:00', 'break_end' => '19:30'],
                ],
                'note' => '修正理由',
            ]);

        $response->assertRedirect("/admin/attendance/{$attendance->id}");
        $response->assertSessionHasErrors();

        $this->followRedirects($response)
            ->assertSeeText('休憩時間が不適切な値です');
    }

    public function test_休憩終了時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->from("/admin/attendance/{$attendance->id}")
            ->actingAs($admin)
            ->post("/admin/attendance/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '17:00', 'break_end' => '19:00'],
                ],
                'note' => '修正理由',
            ]);

        $response->assertRedirect("/admin/attendance/{$attendance->id}");
        $response->assertSessionHasErrors();

        $this->followRedirects($response)
            ->assertSeeText('休憩時間もしくは退勤時間が不適切な値です');
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->from("/admin/attendance/{$attendance->id}")
            ->actingAs($admin)
            ->post("/admin/attendance/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '',
            ]);

        $response->assertRedirect("/admin/attendance/{$attendance->id}");
        $response->assertSessionHasErrors(['note']);

        $this->followRedirects($response)
            ->assertSeeText('備考');
    }
}