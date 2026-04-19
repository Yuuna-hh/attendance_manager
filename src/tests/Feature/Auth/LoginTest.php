<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $user = User::factory()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);

        $this->followRedirects($response)
            ->assertSeeText('メールアドレスを入力してください');
    }

    public function test_パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $user = User::factory()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => '',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['password']);

        $this->followRedirects($response)
            ->assertSeeText('パスワードを入力してください');
    }

    public function test_登録内容と一致しない場合バリデーションメッセージが表示される()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors();

        $this->followRedirects($response)
            ->assertSeeText('ログイン情報が登録されていません');
    }
}