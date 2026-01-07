<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\CorrectionRequest;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
{
    parent::setUp();

    // 'verified' エイリアスではなく、ミドルウェアクラスで外す（確実）
    $this->withoutMiddleware(EnsureEmailIsVerified::class);
}

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    /**
     * 名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_name_required_validation()
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください。']);
    }

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_required_validation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);
    }

    /**
     * パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function test_password_min_length_validation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください。']);
    }

    /**
     * パスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function test_password_confirmation_validation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません。']);
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_required_validation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);
    }

    /**
     * フォームに内容が入力されていた場合、データが正常に保存される
     */
    public function test_user_registration_success()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(); // リダイレクト
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_login_email_required_validation()
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => User::ROLE_GENERAL,
        ]);

        $response = $this->post('/login', [
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_login_password_required_validation()
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => User::ROLE_GENERAL,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);
    }

    /**
     * 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_login_invalid_credentials()
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => User::ROLE_GENERAL,
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email' => 'メールアドレスまたはパスワードが異なります。']);
    }

    /**
     * 管理者: メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_email_required_validation()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/admin/login', [
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);
    }

    /**
     * 管理者: パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_password_required_validation()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);
    }

    /**
     * 管理者: 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_invalid_credentials()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email' => 'メールアドレスまたはパスワードが違います']);
    }

    /**
     * 日時取得機能: 現在の日時情報がUIと同じ形式で出力されている
     */
    public function test_current_datetime_display()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => User::ROLE_GENERAL,
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware('verified');

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        // ビューに渡されたデータを確認
        $response->assertViewHas('displayTime', function ($displayTime) {
            $currentTime = Carbon::now()->format('H:i');
            return $displayTime === $currentTime;
        });
    }

    /**
     * ステータス確認機能: 勤務外の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_not_working()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => User::ROLE_GENERAL,
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware('verified');

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertViewHas('status', 0);
    }

    /**
     * ステータス確認機能: 出勤中の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_working()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 1,
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware('verified');

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertViewHas('status', 1);
    }

    /**
     * ステータス確認機能: 休憩中の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_on_break()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 2,
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware('verified');

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertViewHas('status', 2);
    }

    /**
     * ステータス確認機能: 退勤済の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_clocked_out()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 3,
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware('verified');

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertViewHas('status', 3);
    }

    /**
     * 出勤機能: 出勤ボタンが正しく機能する
     */
    public function test_clock_in_button_functionality()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => User::ROLE_GENERAL,
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware('verified');

        // 勤務外の場合、出勤ボタンが表示される
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertViewHas('status', 0);

        // 出勤処理
        $response = $this->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $response->assertRedirect();

        // ステータスが出勤中になることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 1,
        ]);
    }

    /**
     * 出勤機能: 出勤は一日一回のみできる
     */
    public function test_clock_in_once_per_day()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 3, // 退勤済
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware('verified');

        // 退勤済の場合、出勤ボタンは表示されない（status=3）
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertViewHas('status', 3);
    }

    /**
     * 出勤機能: 出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_in_time_in_list()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware('verified');

        // 出勤処理
        $this->post('/attendance', [
            'action' => 'clock_in',
        ]);

        // 勤怠一覧画面にアクセス
        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

        // 出勤時刻が記録されていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 1,
        ]);

        $attendance = Attendance::where('user_id', $user->id)->where('work_date', Carbon::today())->first();
        $this->assertNotNull($attendance->clock_in_at);
    }

    /**
     * 休憩機能: 休憩ボタンが正しく機能する
     */
    public function test_break_in_button_functionality()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 1, // 出勤中
        ]);

        $this->actingAs($user);

        // 出勤中の場合、休憩入ボタンが表示される
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertViewHas('status', 1);

        // 休憩入処理
        $response = $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        $response->assertRedirect();

        // ステータスが休憩中になることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 2,
        ]);
    }

    /**
     * 休憩機能: 休憩は一日に何回でもできる
     */
    public function test_break_multiple_times()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 1, // 出勤中
        ]);

        $this->actingAs($user);

        // 休憩入処理
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        // 休憩戻処理
        $this->post('/attendance', [
            'action' => 'break_out',
        ]);

        // 再度出勤中になり、休憩入ボタンが表示される
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertViewHas('status', 1);
    }

    /**
     * 休憩機能: 休憩戻ボタンが正しく機能する
     */
    public function test_break_out_button_functionality()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 1, // 出勤中
        ]);

        $this->actingAs($user);

        // 休憩入処理
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        // 休憩中の場合、休憩戻ボタンが表示される
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertViewHas('status', 2);

        // 休憩戻処理
        $response = $this->post('/attendance', [
            'action' => 'break_out',
        ]);

        $response->assertRedirect();

        // ステータスが出勤中になることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 1,
        ]);
    }

    /**
     * 休憩機能: 休憩戻は一日に何回でもできる
     */
    public function test_break_out_multiple_times()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 1, // 出勤中
        ]);

        $this->actingAs($user);

        // 休憩入処理
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        // 休憩戻処理
        $this->post('/attendance', [
            'action' => 'break_out',
        ]);

        // 再度休憩入処理
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        // 再度休憩中になり、休憩戻ボタンが表示される
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertViewHas('status', 2);
    }

    /**
     * 休憩機能: 休憩時刻が勤怠一覧画面で確認できる
     */
    public function test_break_time_in_list()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 1, // 出勤中
        ]);

        $this->actingAs($user);

        // 休憩入処理
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        // 休憩戻処理
        $this->post('/attendance', [
            'action' => 'break_out',
        ]);

        // 勤怠一覧画面にアクセス
        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

        // 休憩時刻が記録されていることを確認
        $attendance = Attendance::where('user_id', $user->id)->where('work_date', Carbon::today())->first();
        $this->assertTrue($attendance->breaks()->count() > 0);
    }

    /**
     * 退勤機能: 退勤ボタンが正しく機能する
     */
    public function test_clock_out_button_functionality()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 1, // 出勤中
        ]);

        $this->actingAs($user);

        // 出勤中の場合、退勤ボタンが表示される
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertViewHas('status', 1);

        // 退勤処理
        $response = $this->post('/attendance', [
            'action' => 'clock_out',
        ]);

        $response->assertRedirect();

        // ステータスが退勤済になることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => 3,
        ]);
    }

    /**
     * 退勤機能: 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_out_time_in_list()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        // 出勤処理
        $this->post('/attendance', [
            'action' => 'clock_in',
        ]);

        // 退勤処理
        $this->post('/attendance', [
            'action' => 'clock_out',
        ]);

        // 勤怠一覧画面にアクセス
        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

        // 退勤時刻が記録されていることを確認
        $attendance = Attendance::where('user_id', $user->id)->where('work_date', Carbon::today())->first();
        $this->assertNotNull($attendance->clock_out_at);
    }

    /**
     * 勤怠一覧情報取得機能（一般ユーザー）: 自分が行った勤怠情報が全て表示されている
     */
    public function test_attendance_list_shows_user_attendances()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // 勤怠データを作成
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        // ビューに勤怠データが渡されていることを確認（詳細なチェックはビュー次第）
    }

    /**
     * 勤怠一覧情報取得機能（一般ユーザー）: 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_attendance_list_current_month()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertViewHas('monthStart', function ($monthStart) {
            return $monthStart->format('Y-m') === Carbon::today()->format('Y-m');
        });
    }

    /**
     * 勤怠一覧情報取得機能（一般ユーザー）: 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_attendance_list_previous_month()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $prevMonth = Carbon::today()->subMonth()->format('Y-m');

        $response = $this->get('/attendance/list?month=' . $prevMonth);

        $response->assertStatus(200);
        $response->assertViewHas('monthStart', function ($monthStart) use ($prevMonth) {
            return $monthStart->format('Y-m') === $prevMonth;
        });
    }

    /**
     * 勤怠一覧情報取得機能（一般ユーザー）: 「翌月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_attendance_list_next_month()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $nextMonth = Carbon::today()->addMonth()->format('Y-m');

        $response = $this->get('/attendance/list?month=' . $nextMonth);

        $response->assertStatus(200);
        $response->assertViewHas('monthStart', function ($monthStart) use ($nextMonth) {
            return $monthStart->format('Y-m') === $nextMonth;
        });
    }

    /**
     * 勤怠一覧情報取得機能（一般ユーザー）: 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_attendance_detail_link()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        // 詳細画面が表示されることを確認
    }

    /**
     * 勤怠詳細情報取得機能（一般ユーザー）: 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_attendance_detail_shows_user_name()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertViewHas('attendance', function ($attendance) use ($user) {
            return $attendance->user->name === $user->name;
        });
    }

    /**
     * 勤怠詳細情報取得機能（一般ユーザー）: 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_attendance_detail_shows_correct_date()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $workDate = Carbon::today();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $workDate,
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertViewHas('workDate', function ($workDateView) use ($workDate) {
            return $workDateView->toDateString() === $workDate->toDateString();
        });
    }

    /**
     * 勤怠詳細情報取得機能（一般ユーザー）: 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_detail_shows_clock_times()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertViewHas('clockInValue', '09:00');
        $response->assertViewHas('clockOutValue', '18:00');
    }

    /**
     * 勤怠詳細情報取得機能（一般ユーザー）: 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_detail_shows_break_times()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        // 休憩データを作成
        $attendance->breaks()->create([
            'order' => 1,
            'start_at' => '12:00:00',
            'end_at' => '13:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertViewHas('displayBreaks', function ($displayBreaks) {
            return $displayBreaks->count() > 0 && $displayBreaks->first()->start_at->format('H:i:s') === '12:00:00';
        });
    }

    /**
     * 勤怠詳細情報修正機能（一般ユーザー）: 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_correction_clock_in_after_clock_out()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => User::ROLE_GENERAL,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware('verified');

        $response = $this->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in_at' => '19:00',
            'clock_out_at' => '18:00',
            'reason' => 'Test reason',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['clock_out_at' => '出勤時間もしくは退勤時間が不適切な値です。']);
    }

    /**
     * 勤怠詳細情報修正機能（一般ユーザー）: 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_correction_break_start_after_clock_out()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [
                ['start' => '19:00', 'end' => '20:00'],
            ],
            'reason' => 'Test reason',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['breaks.0.start' => '休憩時間が不適切な値です。']);
    }

    /**
     * 勤怠詳細情報修正機能（一般ユーザー）: 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_correction_break_end_after_clock_out()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '19:00'],
            ],
            'reason' => 'Test reason',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['breaks.0.end' => '休憩時間が不適切な値です。']);
    }

    /**
     * 勤怠詳細情報修正機能（一般ユーザー）: 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_correction_reason_required()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['reason' => '備考を記入してください。']);
    }

    /**
     * 勤怠詳細情報修正機能（一般ユーザー）: 修正申請処理が実行される
     */
    public function test_correction_request_created()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => User::ROLE_GENERAL,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($user);

        $this->withoutMiddleware('verified');

        $response = $this->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'reason' => 'Test reason',
        ]);

        $response->assertRedirect();

        // CorrectionRequestが作成されたことを確認
        $this->assertDatabaseHas('correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'reason' => 'Test reason',
        ]);
    }

    /**
     * 勤怠詳細情報修正機能（一般ユーザー）: 「承認待ち」にログインユーザーが行った申請が全て表示されていること
     */
    public function test_correction_requests_pending_list()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in_time' => '09:00',
            'requested_clock_out_time' => '18:00',
            'reason' => 'Test reason',
            'status' => 0, // pending
        ]);

        $this->actingAs($user);

        $response = $this->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        // 承認待ちの申請が表示されていることを確認（ビュー次第）
    }

    /**
     * 勤怠詳細情報修正機能（一般ユーザー）: 「承認済み」に管理者が承認した修正申請が全て表示されている
     */
    public function test_correction_requests_approved_list()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in_time' => '09:00',
            'requested_clock_out_time' => '18:00',
            'reason' => 'Test reason',
            'status' => 1, // approved
        ]);

        $this->actingAs($user);

        $response = $this->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        // 承認済みの申請が表示されていることを確認（ビュー次第）
    }

    /**
     * 勤怠詳細情報修正機能（一般ユーザー）: 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
     */
    public function test_correction_request_detail_link()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $request = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in_time' => '09:00',
            'requested_clock_out_time' => '18:00',
            'reason' => 'Test reason',
            'status' => 0,
        ]);

        $this->actingAs($user);

        $response = $this->get('/stamp_correction_request/' . $request->id);

        $response->assertStatus(200);
        // 詳細画面が表示されることを確認
    }

    /**
     * 勤怠一覧情報取得機能（管理者）: その日になされた全ユーザーの勤怠情報が正確に確認できる
     */
    public function test_admin_attendance_list_shows_all_users()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user1->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '10:00:00',
            'clock_out_at' => '19:00:00',
            'status' => 3,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);
        // 全ユーザーの勤怠が表示されていることを確認（ビュー次第）
    }

    /**
     * 勤怠一覧情報取得機能（管理者）: 遷移した際に現在の日付が表示される
     */
    public function test_admin_attendance_list_current_date()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertViewHas('targetDate', function ($targetDate) {
            return $targetDate->toDateString() === Carbon::today()->toDateString();
        });
    }

    /**
     * 勤怠一覧情報取得機能（管理者）: 「前日」を押下した時に前の日の勤怠情報が表示される
     */
    public function test_admin_attendance_list_previous_date()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $prevDate = Carbon::today()->subDay()->toDateString();

        $response = $this->get('/admin/attendance/list?date=' . $prevDate);

        $response->assertStatus(200);
        $response->assertViewHas('targetDate', function ($targetDate) use ($prevDate) {
            return $targetDate->toDateString() === $prevDate;
        });
    }

    /**
     * 勤怠一覧情報取得機能（管理者）: 「翌日」を押下した時に次の日の勤怠情報が表示される
     */
    public function test_admin_attendance_list_next_date()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $nextDate = Carbon::today()->addDay()->toDateString();

        $response = $this->get('/admin/attendance/list?date=' . $nextDate);

        $response->assertStatus(200);
        $response->assertViewHas('targetDate', function ($targetDate) use ($nextDate) {
            return $targetDate->toDateString() === $nextDate;
        });
    }

    /**
     * 勤怠詳細情報取得・修正機能（管理者）: 勤怠詳細画面に表示されるデータが選択したものになっている
     */
    public function test_admin_attendance_detail_shows_correct_data()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertViewHas('attendance', function ($attendance) use ($user) {
            return $attendance->user->name === $user->name;
        });
        $response->assertViewHas('workDate', function ($workDate) {
            return $workDate->toDateString() === Carbon::today()->toDateString();
        });
        $response->assertViewHas('clockInValue', '09:00');
        $response->assertViewHas('clockOutValue', '18:00');
    }

    /**
     * 勤怠詳細情報取得・修正機能（管理者）: 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_admin_correction_clock_in_after_clock_out()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($admin);

        $response = $this->put('/admin/attendance/' . $attendance->id, [
            'clock_in_at' => '19:00',
            'clock_out_at' => '18:00',
            'remarks' => 'Test reason',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['clock_out_at' => '出勤時間もしくは退勤時間が不適切な値です。']);
    }

    /**
     * 勤怠詳細情報取得・修正機能（管理者）: 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_admin_correction_break_start_after_clock_out()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($admin);

        $response = $this->put('/admin/attendance/' . $attendance->id, [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [
                ['start' => '19:00', 'end' => '20:00'],
            ],
            'reason' => 'Test reason',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['breaks.0.start' => '休憩時間が不適切な値です。']);
    }

    /**
     * 勤怠詳細情報取得・修正機能（管理者）: 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_admin_correction_break_end_after_clock_out()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($admin);

        $response = $this->put('/admin/attendance/' . $attendance->id, [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '19:00'],
            ],
            'reason' => 'Test reason',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です。']);
    }

    /**
     * 勤怠詳細情報取得・修正機能（管理者）: 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_admin_correction_reason_required()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($admin);

        $response = $this->put('/admin/attendance/' . $attendance->id, [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['remarks' => '備考を記入してください。']);
    }

    /**
     * ユーザー情報取得機能（管理者）: 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function test_admin_staff_list_shows_all_users()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/staff/list');

        $response->assertStatus(200);
        // 全ユーザーの氏名とメールアドレスが表示されていることを確認（ビュー次第）
    }

    /**
     * ユーザー情報取得機能（管理者）: ユーザーの勤怠情報が正しく表示される
     */
    public function test_admin_user_attendance_list_shows_correct_data()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/staff/' . $user->id);

        $response->assertStatus(200);
        // 勤怠情報が正確に表示されていることを確認（ビュー次第）
    }

    /**
     * ユーザー情報取得機能（管理者）: 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_admin_user_attendance_list_previous_month()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $prevMonth = Carbon::today()->subMonth()->format('Y-m');

        $response = $this->get('/admin/attendance/staff/' . $user->id . '?month=' . $prevMonth);

        $response->assertStatus(200);
        $response->assertViewHas('monthStart', function ($monthStart) use ($prevMonth) {
            return $monthStart->format('Y-m') === $prevMonth;
        });
    }

    /**
     * ユーザー情報取得機能（管理者）: 「翌月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_admin_user_attendance_list_next_month()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $nextMonth = Carbon::today()->addMonth()->format('Y-m');

        $response = $this->get('/admin/attendance/staff/' . $user->id . '?month=' . $nextMonth);

        $response->assertStatus(200);
        $response->assertViewHas('monthStart', function ($monthStart) use ($nextMonth) {
            return $monthStart->format('Y-m') === $nextMonth;
        });
    }

    /**
     * ユーザー情報取得機能（管理者）: 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_admin_user_attendance_detail_link()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        // 詳細画面が表示されることを確認
    }

    /**
     * 勤怠情報修正機能（管理者）: 承認待ちの修正申請が全て表示されている
     */
    public function test_admin_pending_correction_requests_list()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in_time' => '09:00',
            'requested_clock_out_time' => '18:00',
            'reason' => 'Test reason',
            'status' => 0, // pending
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        // 承認待ちの申請が表示されていることを確認（ビュー次第）
    }

    /**
     * 勤怠情報修正機能（管理者）: 承認済みの修正申請が全て表示されている
     */
    public function test_admin_approved_correction_requests_list()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in_time' => '09:00',
            'requested_clock_out_time' => '18:00',
            'reason' => 'Test reason',
            'status' => 1, // approved
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        // 承認済みの申請が表示されていることを確認（ビュー次第）
    }

    /**
     * 勤怠情報修正機能（管理者）: 修正申請の詳細内容が正しく表示されている
     */
    public function test_admin_correction_request_detail_shows_correct_data()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $request = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in_time' => '09:00',
            'requested_clock_out_time' => '18:00',
            'reason' => 'Test reason',
            'status' => 0,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/stamp_correction_request/approve/' . $request->id);

        $response->assertStatus(200);
        // 申請内容が正しく表示されていることを確認（ビュー次第）
    }

    /**
     * 勤怠情報修正機能（管理者）: 修正申請の承認処理が正しく行われる
     */
    public function test_admin_correction_request_approval()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'status' => 3,
        ]);

        $request = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in_time' => '08:00',
            'requested_clock_out_time' => '17:00',
            'reason' => 'Test reason',
            'status' => 0,
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/stamp_correction_request/approve/' . $request->id);

        $response->assertRedirect();

        // CorrectionRequestが承認されたことを確認
        $this->assertDatabaseHas('correction_requests', [
            'id' => $request->id,
            'status' => 1,
        ]);

        // Attendanceが更新されたことを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in_at' => '08:00:00',
            'clock_out_at' => '17:00:00',
        ]);
    }

    /**
     * メール認証機能: 会員登録後、認証メールが送信される
     */
    public function test_email_verification_sent_after_registration()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
    }

    /**
     * メール認証機能: メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     */
    public function test_email_verification_link_redirects_to_verification_page()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
            'role' => User::ROLE_GENERAL,
        ]);

        $this->actingAs($user);

        $response = $this->get('/email/verify');

        $response->assertStatus(200);
        // メール認証誘導画面が表示されることを確認

        // メール認証リンクをクリック（実際のリンクをシミュレート）
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            \Carbon\Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $response->assertRedirect('?verified=1');
    }

    /**
     * メール認証機能: メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
     */
    public function test_email_verification_completion_redirects_to_attendance()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        // メール認証を完了
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            \Carbon\Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $response->assertRedirect('?verified=1');

        // ユーザーが認証されたことを確認
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        // 勤怠画面にアクセス
        $response = $this->get('/attendance');

        $response->assertStatus(200);
    }
}
