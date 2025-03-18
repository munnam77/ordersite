As a Senior Principal Engineer, I’ll revise the instructions to ensure the ordering site aligns with Japanese standards, considering the client is Japanese and the site will be fully in Japanese. This includes using Japanese language throughout the UI, adhering to Japanese web design conventions (e.g., clean layouts, polite messaging), and ensuring compatibility with Japanese character encoding (UTF-8). Below is the updated, detailed guide for you, the junior developer, to build the site from start to finish.

---

### Project Overview
- **Objective**: Build a simple ordering site in Japanese where store staff (店舗スタッフ) log in, input quantities for predefined schedules (e.g., "4/7週"), and submit orders, while admins (管理者) manage schedules and view orders.
- **Tech Stack**: PHP with Laravel, MySQL, Bootstrap (Japanese-friendly styling).
- **Pages**:
  1. ログイン画面 (Login Page).
  2. 店舗ダッシュボード (Store Dashboard).
  3. 管理者ダッシュボード (Admin Dashboard).
- **Database**: Use `db.xlsx` tables (`admins`, `stores`, `schedules`, `orders`).
- **Timeline**: 14–17 days (March 19–April 4, 2025).
- **Budget**: 20–28 hours at ~2,500 JPY/hour.
- **Language**: All text in Japanese, UTF-8 encoding.

---

### Prerequisites
- PHP 8.1+, Composer, MySQL, Git, and a code editor (e.g., VS Code).
- Familiarity with Japanese input methods (e.g., IME) for coding Japanese text.
- Client’s server and domain access (for deployment).

---

### Phase 1: Project Setup (Day 1–2, March 19–20)
#### Instructions:
1. **Initialize Laravel Project**:
   - Run:
     ```bash
     composer create-project laravel/laravel order-site
     cd order-site
     ```

2. **Configure Environment**:
   - Edit `.env`:
     ```
     APP_NAME="発注サイト"
     APP_ENV=local
     APP_DEBUG=true
     APP_URL=http://localhost:8000
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=order_site
     DB_USERNAME=root
     DB_PASSWORD=your_password
     ```
   - Create MySQL database:
     ```sql
     CREATE DATABASE order_site CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     ```

3. **Install Dependencies**:
   - Install Laravel UI with Bootstrap:
     ```bash
     composer require laravel/ui
     php artisan ui bootstrap --auth
     npm install && npm run dev
     ```

4. **Set Up Git**:
   - Initialize:
     ```bash
     git init
     git add .
     git commit -m "初期設定完了"
     ```

5. **Database Schema**:
   - Create migrations based on `db.xlsx`:
     - **Admins Table** (管理者):
       ```bash
       php artisan make:migration create_admins_table
       ```
       Edit `database/migrations/[timestamp]_create_admins_table.php`:
       ```php
       public function up()
       {
           Schema::create('admins', function (Blueprint $table) {
               $table->id();
               $table->string('admin_name')->comment('管理者名');
               $table->string('login_id')->unique()->comment('ログインID');
               $table->string('login_password')->comment('ログインパスワード');
               $table->string('mail')->unique()->comment('メールアドレス');
               $table->timestamps();
           });
       }
       ```
     - **Stores Table** (店舗):
       ```bash
       php artisan make:migration create_stores_table
       ```
       Edit `database/migrations/[timestamp]_create_stores_table.php`:
       ```php
       public function up()
       {
           Schema::create('stores', function (Blueprint $table) {
               $table->id();
               $table->integer('store_id')->unique()->comment('店舗ID');
               $table->string('prefectures')->comment('都道府県');
               $table->string('store_name')->comment('店舗名');
               $table->string('login_id')->unique()->comment('ログインID');
               $table->string('login_password')->comment('ログインパスワード');
               $table->boolean('admin')->default(0)->comment('管理者フラグ');
               $table->timestamps();
           });
       }
       ```
     - **Schedules Table** (スケジュール):
       ```bash
       php artisan make:migration create_schedules_table
       ```
       Edit `database/migrations/[timestamp]_create_schedules_table.php`:
       ```php
       public function up()
       {
           Schema::create('schedules', function (Blueprint $table) {
               $table->id();
               $table->integer('schedule_id')->unique()->comment('スケジュールID');
               $table->string('schedule_name')->comment('スケジュール名');
               $table->integer('p_total_number')->comment('総数量上限');
               $table->timestamps();
           });
       }
       ```
     - **Orders Table** (発注):
       ```bash
       php artisan make:migration create_orders_table
       ```
       Edit `database/migrations/[timestamp]_create_orders_table.php`:
       ```php
       public function up()
       {
           Schema::create('orders', function (Blueprint $table) {
               $table->id();
               $table->foreignId('store_id')->constrained('stores')->comment('店舗ID');
               $table->foreignId('schedule_id')->constrained('schedules')->comment('スケジュールID');
               $table->string('schedule_name')->comment('スケジュール名');
               $table->float('p_quantity')->comment('発注数量');
               $table->text('comment')->nullable()->comment('コメント');
               $table->date('delivery_date')->nullable()->comment('配送日');
               $table->string('vehicle')->nullable()->comment('車両');
               $table->dateTime('working_day')->nullable()->comment('作業日');
               $table->time('working_time')->nullable()->comment('作業時間');
               $table->timestamps();
           });
       }
       ```
   - Run migrations:
     ```bash
     php artisan migrate
     ```

6. **Seed Initial Data**:
   - Create seeder:
     ```bash
     php artisan make:seeder DatabaseSeeder
     ```
   - Edit `database/seeders/DatabaseSeeder.php`:
     ```php
     public function run()
     {
         DB::table('admins')->insert([
             'admin_name' => '管理者',
             'login_id' => 'admin1',
             'login_password' => Hash::make('password1'),
             'mail' => 'kitamura@abukan.com',
         ]);

         $stores = [
             ['store_id' => 1, 'prefectures' => '大阪', 'store_name' => '泉北', 'login_id' => '1', 'login_password' => Hash::make('001_abc'), 'admin' => 0],
             ['store_id' => 2, 'prefectures' => '大阪', 'store_name' => '鳳', 'login_id' => '2', 'login_password' => Hash::make('002_abc'), 'admin' => 0],
             // Add remaining 8 stores from db.xlsx
         ];
         DB::table('stores')->insert($stores);

         $schedules = [
             ['schedule_id' => 1, 'schedule_name' => '4/7週', 'p_total_number' => 100],
             ['schedule_id' => 2, 'schedule_name' => '4/14週', 'p_total_number' => 120],
             // Add remaining 5 schedules from db.xlsx
         ];
         DB::table('schedules')->insert($schedules);

         DB::table('orders')->insert([
             'store_id' => 1, 'schedule_id' => 1, 'schedule_name' => '4/7週', 'p_quantity' => 6, 'comment' => 'よろしくお願いします', 'delivery_date' => '2025-04-09',
         ]);
     }
     ```
   - Run:
     ```bash
     php artisan db:seed
     ```

#### Deliverables:
- Laravel project with Japanese database schema and sample data.
- Git commit: `git commit -m "データベース設定と初期データ投入"`

---

### Phase 2: Authentication and Models (Day 3–4, March 21–22)
#### Instructions:
1. **Create Models**:
   - **Admin**:
     ```bash
     php artisan make:model Admin
     ```
     Edit `app/Models/Admin.php`:
     ```php
     class Admin extends Authenticatable
     {
         protected $fillable = ['admin_name', 'login_id', 'login_password', 'mail'];
         protected $hidden = ['login_password'];
         public function getAuthPassword()
         {
             return $this->login_password;
         }
     }
     ```
   - **Store**:
     ```bash
     php artisan make:model Store
     ```
     Edit `app/Models/Store.php`:
     ```php
     class Store extends Authenticatable
     {
         protected $fillable = ['store_id', 'prefectures', 'store_name', 'login_id', 'login_password', 'admin'];
         protected $hidden = ['login_password'];
         public function getAuthPassword()
         {
             return $this->login_password;
         }
         public function orders()
         {
             return $this->hasMany(Order::class);
         }
     }
     ```
   - **Schedule**:
     ```bash
     php artisan make:model Schedule
     ```
     Edit `app/Models/Schedule.php`:
     ```php
     class Schedule extends Model
     {
         protected $fillable = ['schedule_id', 'schedule_name', 'p_total_number'];
         public function orders()
         {
             return $this->hasMany(Order::class);
         }
     }
     ```
   - **Order**:
     ```bash
     php artisan make:model Order
     ```
     Edit `app/Models/Order.php`:
     ```php
     class Order extends Model
     {
         protected $fillable = ['store_id', 'schedule_id', 'schedule_name', 'p_quantity', 'comment', 'delivery_date', 'vehicle', 'working_day', 'working_time'];
         public function store()
         {
             return $this->belongsTo(Store::class);
         }
         public function schedule()
         {
             return $this->belongsTo(Schedule::class);
         }
     }
     ```

2. **Configure Authentication**:
   - Edit `config/auth.php`:
     ```php
     'guards' => [
         'web' => ['driver' => 'session', 'provider' => 'stores'],
         'admin' => ['driver' => 'session', 'provider' => 'admins'],
     ],
     'providers' => [
         'stores' => ['driver' => 'eloquent', 'model' => App\Models\Store::class],
         'admins' => ['driver' => 'eloquent', 'model' => App\Models\Admin::class],
     ],
     ```

3. **Create Login Controller**:
   - ```bash
     php artisan make:controller Auth/LoginController
     ```
   - Edit `app/Http/Controllers/Auth/LoginController.php`:
     ```php
     use Illuminate\Http\Request;
     use Illuminate\Support\Facades\Auth;

     class LoginController extends Controller
     {
         public function showLoginForm()
         {
             return view('auth.login');
         }

         public function login(Request $request)
         {
             $credentials = $request->only('login_id', 'login_password');
             if (Auth::guard('admin')->attempt($credentials)) {
                 return redirect('/admin')->with('success', 'ログインしました');
             } elseif (Auth::guard('web')->attempt($credentials)) {
                 return redirect('/dashboard')->with('success', 'ログインしました');
             }
             return back()->withErrors(['login_id' => 'ログインIDまたはパスワードが正しくありません']);
         }

         public function logout()
         {
             Auth::logout();
             return redirect('/login')->with('success', 'ログアウトしました');
         }
     }
     ```

4. **Update Routes**:
   - Edit `routes/web.php`:
     ```php
     use App\Http\Controllers\Auth\LoginController;

     Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
     Route::post('/login', [LoginController::class, 'login']);
     Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
     Route::get('/dashboard', fn() => view('dashboard'))->middleware('auth:web');
     Route::get('/admin', fn() => view('admin'))->middleware('auth:admin');
     ```

5. **Customize Login View**:
   - Edit `resources/views/auth/login.blade.php`:
     ```html
     <!DOCTYPE html>
     <html lang="ja">
     <head>
         <meta charset="UTF-8">
         <title>ログイン | 発注サイト</title>
         <link href="{{ asset('css/app.css') }}" rel="stylesheet">
     </head>
     <body>
         <div class="container mt-5">
             <h1 class="text-center">ログイン</h1>
             @if ($errors->any())
                 <div class="alert alert-danger">
                     {{ $errors->first() }}
                 </div>
             @endif
             <form method="POST" action="{{ route('login') }}" class="mt-4">
                 @csrf
                 <div class="form-group">
                     <label for="login_id">ログインID</label>
                     <input type="text" name="login_id" class="form-control" required>
                 </div>
                 <div class="form-group">
                     <label for="login_password">パスワード</label>
                     <input type="password" name="login_password" class="form-control" required>
                 </div>
                 <button type="submit" class="btn btn-primary btn-block">ログイン</button>
             </form>
         </div>
     </body>
     </html>
     ```

#### Deliverables:
- Japanese login system for admins and stores.
- Git commit: `git commit -m "認証機能と日本語ログイン画面実装"`

---

### Phase 3: Store Dashboard (Day 5–7, March 23–25)
#### Instructions:
1. **Create Order Controller**:
   - ```bash
     php artisan make:controller OrderController
     ```
   - Edit `app/Http/Controllers/OrderController.php`:
     ```php
     use App\Models\Schedule;
     use App\Models\Order;
     use Illuminate\Http\Request;
     use Illuminate\Support\Facades\Auth;

     class OrderController extends Controller
     {
         public function index()
         {
             $schedules = Schedule::all();
             $orders = Auth::user()->orders;
             return view('dashboard', compact('schedules', 'orders'));
         }

         public function store(Request $request)
         {
             $request->validate([
                 'schedule_id' => 'required|exists:schedules,id',
                 'p_quantity' => 'required|numeric|min:1',
             ], [
                 'schedule_id.required' => 'スケジュールを選択してください',
                 'p_quantity.required' => '数量を入力してください',
                 'p_quantity.numeric' => '数量は数値で入力してください',
                 'p_quantity.min' => '数量は1以上で入力してください',
             ]);

             $schedule = Schedule::find($request->schedule_id);
             $totalOrdered = $schedule->orders->sum('p_quantity');
             if ($totalOrdered + $request->p_quantity > $schedule->p_total_number) {
                 return back()->withErrors(['p_quantity' => '総数量上限を超えています']);
             }

             Order::create([
                 'store_id' => Auth::id(),
                 'schedule_id' => $request->schedule_id,
                 'schedule_name' => $schedule->schedule_name,
                 'p_quantity' => $request->p_quantity,
                 'comment' => $request->comment,
             ]);

             return redirect('/dashboard')->with('success', '発注が完了しました');
         }
     }
     ```

2. **Update Routes**:
   - Edit `routes/web.php`:
     ```php
     use App\Http\Controllers\OrderController;

     Route::middleware('auth:web')->group(function () {
         Route::get('/dashboard', [OrderController::class, 'index'])->name('dashboard');
         Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
     });
     ```

3. **Create Dashboard View**:
   - Create `resources/views/dashboard.blade.php`:
     ```html
     @extends('layouts.app')
     @section('content')
         <h1>{{ Auth::user()->store_name }}様、ようこそ</h1>
         @if (session('success'))
             <div class="alert alert-success">{{ session('success') }}</div>
         @endif
         <h2>発注入力</h2>
         <form method="POST" action="{{ route('orders.store') }}" class="mt-4">
             @csrf
             <div class="form-group">
                 <label>スケジュール</label>
                 <select name="schedule_id" class="form-control">
                     @foreach ($schedules as $schedule)
                         <option value="{{ $schedule->id }}">{{ $schedule->schedule_name }} (上限: {{ $schedule->p_total_number }})</option>
                     @endforeach
                 </select>
                 @error('schedule_id')
                     <span class="text-danger">{{ $message }}</span>
                 @endError
             </div>
             <div class="form-group">
                 <label>数量</label>
                 <input type="number" name="p_quantity" class="form-control" required>
                 @error('p_quantity')
                     <span class="text-danger">{{ $message }}</span>
                 @endError
             </div>
             <div class="form-group">
                 <label>コメント (任意)</label>
                 <textarea name="comment" class="form-control"></textarea>
             </div>
             <button type="submit" class="btn btn-primary">発注する</button>
         </form>
         <h2 class="mt-5">貴店の過去の発注</h2>
         <table class="table">
             <thead>
                 <tr>
                     <th>スケジュール</th>
                     <th>数量</th>
                     <th>コメント</th>
                 </tr>
             </thead>
             <tbody>
                 @foreach ($orders as $order)
                     <tr>
                         <td>{{ $order->schedule_name }}</td>
                         <td>{{ $order->p_quantity }}</td>
                         <td>{{ $order->comment ?? '-' }}</td>
                     </tr>
                 @endforeach
             </tbody>
         </table>
     @endsection
     ```

4. **Update Layout**:
   - Edit `resources/views/layouts/app.blade.php`:
     ```html
     <!DOCTYPE html>
     <html lang="ja">
     <head>
         <meta charset="UTF-8">
         <title>{{ config('app.name') }}</title>
         <link href="{{ asset('css/app.css') }}" rel="stylesheet">
     </head>
     <body>
         <nav class="navbar navbar-expand-lg navbar-light bg-light">
             <a class="navbar-brand" href="#">{{ config('app.name') }}</a>
             @auth
                 <form action="{{ route('logout') }}" method="POST" class="ml-auto">
                     @csrf
                     <button type="submit" class="btn btn-link">ログアウト</button>
                 </form>
             @endauth
         </nav>
         <div class="container mt-4">
             @yield('content')
         </div>
     </body>
     </html>
     ```

#### Deliverables:
- Japanese store dashboard with order submission.
- Git commit: `git commit -m "店舗ダッシュボード実装"`

---

### Phase 4: Admin Dashboard (Day 8–10, March 26–28)
#### Instructions:
1. **Create Schedule Controller**:
   - ```bash
     php artisan make:controller ScheduleController
     ```
   - Edit `app/Http/Controllers/ScheduleController.php`:
     ```php
     use App\Models\Schedule;
     use App\Models\Order;
     use Illuminate\Http\Request;

     class ScheduleController extends Controller
     {
         public function index()
         {
             $schedules = Schedule::with('orders')->get();
             return view('admin', compact('schedules'));
         }

         public function store(Request $request)
         {
             $request->validate([
                 'schedule_name' => 'required|unique:schedules',
                 'p_total_number' => 'required|numeric|min:1',
             ], [
                 'schedule_name.required' => 'スケジュール名を入力してください',
                 'schedule_name.unique' => 'このスケジュール名は既に存在します',
                 'p_total_number.required' => '総数量上限を入力してください',
                 'p_total_number.numeric' => '総数量上限は数値で入力してください',
             ]);

             Schedule::create($request->all());
             return redirect('/admin')->with('success', 'スケジュールを追加しました');
         }
     }
     ```

2. **Update Routes**:
   - Edit `routes/web.php`:
     ```php
     use App\Http\Controllers\ScheduleController;

     Route::middleware('auth:admin')->group(function () {
         Route::get('/admin', [ScheduleController::class, 'index'])->name('admin');
         Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
     });
     ```

3. **Create Admin View**:
   - Create `resources/views/admin.blade.php`:
     ```html
     @extends('layouts.app')
     @section('content')
         <h1>管理者ダッシュボード</h1>
         @if (session('success'))
             <div class="alert alert-success">{{ session('success') }}</div>
         @endif
         <h2>スケジュール追加</h2>
         <form method="POST" action="{{ route('schedules.store') }}" class="mt-4">
             @csrf
             <div class="form-group">
                 <label>スケジュール名</label>
                 <input type="text" name="schedule_name" class="form-control" required>
                 @error('schedule_name')
                     <span class="text-danger">{{ $message }}</span>
                 @endError
             </div>
             <div class="form-group">
                 <label>総数量上限</label>
                 <input type="number" name="p_total_number" class="form-control" required>
                 @error('p_total_number')
                     <span class="text-danger">{{ $message }}</span>
                 @endError
             </div>
             <button type="submit" class="btn btn-primary">追加</button>
         </form>
         <h2 class="mt-5">スケジュールと発注状況</h2>
         <table class="table">
             <thead>
                 <tr>
                     <th>スケジュール</th>
                     <th>総数量上限</th>
                     <th>発注済み数量</th>
                 </tr>
             </thead>
             <tbody>
                 @foreach ($schedules as $schedule)
                     <tr>
                         <td>{{ $schedule->schedule_name }}</td>
                         <td>{{ $schedule->p_total_number }}</td>
                         <td>{{ $schedule->orders->sum('p_quantity') }}</td>
                     </tr>
                 @endforeach
             </tbody>
         </table>
     @endsection
     ```

#### Deliverables:
- Japanese admin dashboard with schedule management.
- Git commit: `git commit -m "管理者ダッシュボード実装"`

---

### Phase 5: Testing and Refinement (Day 11–14, March 29–April 1)
#### Instructions:
1. **Unit Tests**:
   - Create test:
     ```bash
     php artisan make:test OrderTest
     ```
   - Edit `tests/Feature/OrderTest.php`:
     ```php
     use App\Models\Store;
     use App\Models\Schedule;

     public function test_order_submission()
     {
         $store = Store::first();
         $schedule = Schedule::first();
         $response = $this->actingAs($store)->post('/orders', [
             'schedule_id' => $schedule->id,
             'p_quantity' => 5,
         ]);
         $response->assertRedirect('/dashboard')->assertSessionHas('success', '発注が完了しました');
     }
     ```
   - Run:
     ```bash
     php artisan test
     ```

2. **Manual Testing**:
   - Test login: `admin1/password1` (管理者), `1/001_abc` (店舗).
   - Submit order exceeding limit (e.g., 101 for "4/7週")—verify error: "総数量上限を超えています".
   - Add schedule as admin (e.g., "4/21週")—verify it appears on store dashboard.

3. **Japanese Standards**:
   - Ensure all text is polite (e.g., "よろしくお願いします" in comments).
   - Verify UTF-8 encoding works (no garbled text like "ã‚¹ã‚±ã‚¸ãƒ¥ãƒ¼ãƒ«").
   - Check Bootstrap layout is clean and minimal, per Japanese design norms.

4. **Responsiveness**:
   - Test on mobile (375px width)—ensure tables scroll horizontally if needed.

#### Deliverables:
- Fully tested Japanese site.
- Git commit: `git commit -m "テストと日本語対応の調整"`

---

### Phase 6: Deployment (Day 15–16, April 2–3)
#### Instructions:
1. **Prepare Production**:
   - Update `.env`:
     ```
     APP_NAME="発注サイト"
     APP_ENV=production
     APP_DEBUG=false
     DB_HOST=client_db_host
     DB_DATABASE=client_db_name
     DB_USERNAME=client_db_user
     DB_PASSWORD=client_db_pass
     ```

2. **Deploy**:
   - Upload to client’s server:
     ```bash
     git push origin main
     ```
   - On server:
     ```bash
     composer install --optimize-autoloader --no-dev
     php artisan migrate
     php artisan db:seed
     npm install && npm run prod
     ```

3. **Verify**:
   - Visit `http://clientdomain.com/login`—test login and order submission.

4. **Documentation**:
   - Create `README.md` (in Japanese):
     ```
     # 発注サイト
     ## 店舗スタッフ向けガイド
     - ログイン: `ログインID` (例: "1") と `パスワード` (例: "001_abc") を使用。
     - スケジュールを選択し、数量を入力して「発注する」を押してください。

     ## 管理者向けガイド
     - ログイン: `admin1/password1` を使用。
     - スケジュールを追加し、発注状況を確認できます。

     ## トラブルシューティング
     - パスワードを忘れた場合、管理者にデータベース経由でリセットを依頼してください。
     ```

#### Deliverables:
- Live Japanese site on client’s domain.
- Git commit: `git commit -m "本番環境へのデプロイ"`

---

### Phase 7: Handover and Support (Day 17+, April 4+)
#### Instructions:
1. **Handover**:
   - Email client (in Japanese):
     - URL: `http://clientdomain.com`
     - `README.md` 添付。
     - ソースコード (zip または Git リポジトリリンク)。
     - テスト用認証情報: `admin1/password1`, `1/001_abc`。
   - Sample email:
     ```
     件名: 発注サイトの納品について
     〇〇様、
     発注サイトが完成しました。以下のURLでご確認いただけます。
     URL: http://clientdomain.com
     詳細な利用方法は添付のREADMEをご覧ください。
     何かご不明点がございましたら、お気軽にご連絡ください。
     よろしくお願い申し上げます。
     ```

2. **Support Plan**:
   - Propose (in Japanese):
     ```
     継続的なサポート（バグ修正、新機能追加）について、月額10,000円の保守契約をご提案いたします。
     ```

#### Deliverables:
- Client handover completed.
- Support proposal sent.

---

### Japanese Standards Applied
- **Language**: All UI, errors, and messages in polite Japanese (e.g., "様", "お願いします").
- **Encoding**: UTF-8 for proper Japanese character display.
- **Design**: Clean, minimal Bootstrap layout, common in Japanese business sites.
- **Data**: Reflects `db.xlsx` (e.g., prefectures like "大阪", store names like "泉北").

Follow these steps exactly, and you’ll deliver a high-quality, Japanese-compliant site. If you hit any issues, let me know immediately—時間厳守でお願いしますね!