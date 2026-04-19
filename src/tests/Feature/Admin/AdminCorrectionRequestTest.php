<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_承認待ちの修正申請が全て表示されている()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $generalUser = User::factory()->create([
            'name' => '申請者',
            'role' => 'general',
        ]);

        $attendance = Attendance::create([
            'user_id' => $generalUser->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $generalUser->id,
            'requested_clock_in' => '2026-04-14 10:00:00',
            'requested_clock_out' => '2026-04-14 19:00:00',
            'requested_note' => '修正申請',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSee('申請者');
        $response->assertSee('承認待ち');
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $generalUser = User::factory()->create([
            'name' => '承認済申請者',
            'role' => 'general',
        ]);

        $attendance = Attendance::create([
            'user_id' => $generalUser->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $generalUser->id,
            'requested_clock_in' => '2026-04-14 10:00:00',
            'requested_clock_out' => '2026-04-14 19:00:00',
            'requested_note' => '承認済み申請',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済申請者');
        $response->assertSee('承認済み');
    }

    public function test_修正申請の詳細内容が正しく表示されている()
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

        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $generalUser->id,
            'requested_clock_in' => '2026-04-14 10:00:00',
            'requested_clock_out' => '2026-04-14 19:00:00',
            'requested_note' => '詳細確認用',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get("/stamp_correction_request/approve/{$correction->id}");

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('詳細確認用');
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $generalUser = User::factory()->create([
            'name' => '一般ユーザー',
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $generalUser->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $generalUser->id,
            'requested_clock_in' => '2026-04-14 10:00:00',
            'requested_clock_out' => '2026-04-14 19:00:00',
            'requested_note' => '承認テスト',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post("/stamp_correction_request/approve/{$correction->id}", [
            'status' => 'approved',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $correction->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '2026-04-14 10:00:00',
            'clock_out' => '2026-04-14 19:00:00',
        ]);

        $adminApprovedResponse = $this->actingAs($admin)->get('/stamp_correction_request/list?status=approved');
        $adminApprovedResponse->assertStatus(200);
        $adminApprovedResponse->assertSee('一般ユーザー');

        $userApprovedResponse = $this->actingAs($generalUser)->get('/stamp_correction_request/list?status=approved');
        $userApprovedResponse->assertStatus(200);
        $userApprovedResponse->assertSee('承認済み');
    }
}