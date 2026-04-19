<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_名前が未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['name']);

        $this->followRedirects($response)
            ->assertSeeText('お名前を入力してください');
    }

    public function test_メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'test',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['email']);

        $this->followRedirects($response)
            ->assertSeeText('メールアドレスを入力してください');
    }

    public function test_パスワードが8文字未満の場合バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password']);

        $this->followRedirects($response)
            ->assertSeeText('パスワードは8文字以上で入力してください');
    }

    public function test_パスワードが一致しない場合バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password_confirmation']);

        $this->followRedirects($response)
            ->assertSeeText('パスワードと一致しません');
    }

    public function test_パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password']);

        $this->followRedirects($response)
            ->assertSeeText('パスワードを入力してください');
    }

    public function test_フォームに内容が入力されていた場合データが正常に保存される()
    {
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'test',
            'email' => 'test@example.com',
        ]);
    }
}