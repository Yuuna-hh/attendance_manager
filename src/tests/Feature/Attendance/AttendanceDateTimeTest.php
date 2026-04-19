<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 18, 9, 30, 0));

        $user = User::factory()->create([
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        $expectedDate = Carbon::now()->isoFormat('YYYY年M月D日(dd)');
        $expectedTime = Carbon::now()->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);

        Carbon::setTestNow();
    }
}