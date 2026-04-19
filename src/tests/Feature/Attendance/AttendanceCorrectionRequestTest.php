<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤時間が退勤時間より後の場合エラー()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->from("/attendance/detail/{$attendance->id}")
            ->actingAs($user)
            ->post("/attendance/detail/{$attendance->id}", [
                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'note' => '修正',
            ]);

        $response->assertRedirect("/attendance/detail/{$attendance->id}");
        $response->assertSessionHasErrors();

        $this->followRedirects($response)
            ->assertSeeText('出勤時間もしくは退勤時間が不適切な値です');
    }

    public function test_休憩開始時間が退勤時間より後の場合エラー()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->from("/attendance/detail/{$attendance->id}")
            ->actingAs($user)
            ->post("/attendance/detail/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '19:00', 'break_end' => '19:30'],
                ],
                'note' => '修正理由',
            ]);

        $response->assertRedirect("/attendance/detail/{$attendance->id}");
        $response->assertSessionHasErrors();

        $this->followRedirects($response)
            ->assertSeeText('休憩時間が不適切な値です');
    }

    public function test_休憩終了時間が退勤時間より後の場合エラー()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->from("/attendance/detail/{$attendance->id}")
            ->actingAs($user)
            ->post("/attendance/detail/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '17:00', 'break_end' => '19:00'],
                ],
                'note' => '修正理由',
            ]);

        $response->assertRedirect("/attendance/detail/{$attendance->id}");
        $response->assertSessionHasErrors();

        $this->followRedirects($response)
            ->assertSeeText('休憩時間もしくは退勤時間が不適切な値です');
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->from("/attendance/detail/{$attendance->id}")
            ->actingAs($user)
            ->post("/attendance/detail/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '',
            ]);

        $response->assertRedirect("/attendance/detail/{$attendance->id}");
        $response->assertSessionHasErrors(['note']);

        $this->followRedirects($response)
            ->assertSeeText('備考');
    }

    public function test_修正申請処理が実行される()
    {
        $user = User::factory()->create([
            'name' => '一般ユーザー',
            'email_verified_at' => now(),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'note' => '修正理由',
        ]);

        $this->assertDatabaseHas('attendance_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);

        $correction = AttendanceCorrection::where('user_id', $user->id)->latest()->first();

        $userListResponse = $this->actingAs($user)->get('/stamp_correction_request/list?status=pending');
        $userListResponse->assertStatus(200);
        $userListResponse->assertSee('修正理由');

        $adminListResponse = $this->actingAs($admin)->get('/stamp_correction_request/list?status=pending');
        $adminListResponse->assertStatus(200);
        $adminListResponse->assertSee('一般ユーザー');

        $adminDetailResponse = $this->actingAs($admin)->get("/stamp_correction_request/approve/{$correction->id}");
        $adminDetailResponse->assertStatus(200);
        $adminDetailResponse->assertSee('10:00');
        $adminDetailResponse->assertSee('19:00');
        $adminDetailResponse->assertSee('修正理由');
    }

    public function test_承認待ちにログインユーザーが行った申請が全て表示されている()
    {
        $user = User::factory()->create([
            'name' => '一般ユーザー',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => '2026-04-14 10:00:00',
            'requested_clock_out' => '2026-04-14 19:00:00',
            'requested_note' => '承認待ち申請',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('承認待ち申請');
    }

    public function test_承認済みに管理者が承認した修正申請が全て表示されている()
    {
        $user = User::factory()->create([
            'name' => '一般ユーザー',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => '2026-04-14 10:00:00',
            'requested_clock_out' => '2026-04-14 19:00:00',
            'requested_note' => '承認済み申請',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('承認済み申請');
    }

    public function test_各申請の詳細を押下すると勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-14',
            'clock_in' => '2026-04-14 09:00:00',
            'clock_out' => '2026-04-14 18:00:00',
            'status' => 'finished',
        ]);

        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => '2026-04-14 10:00:00',
            'requested_clock_out' => '2026-04-14 19:00:00',
            'requested_note' => '詳細確認',
            'status' => 'pending',
        ]);

        $listResponse = $this->actingAs($user)->get('/stamp_correction_request/list?status=pending');

        $listResponse->assertStatus(200);
        $listResponse->assertSee('詳細');

        $detailResponse = $this->actingAs($user)->get(
            route('correction_request_detail', [
                'attendance_correct_request_id' => $correction->id,
            ])
        );

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('詳細確認');
        $detailResponse->assertSee('10:00');
        $detailResponse->assertSee('19:00');
    }
}