As a Senior Principal Engineer, I’ll provide you with clear, step-by-step instructions to deploy your Laravel-based ordering site (fully in Japanese, based on `db.xlsx`) to Heroku for a free demo to show your client. These instructions assume you’ve followed my earlier guidance (March 18, 2025 response) to build the project locally and are ready to deploy it. I’ll ensure it’s straightforward, aligns with the project constraints (budget: 50,000–70,000 JPY, deadline: early April 2025), and meets Japanese standards.

---

### Prerequisites
Before starting, ensure you have:
- Your Laravel project completed locally (e.g., in a folder named `order-site`).
- Git installed (`git --version` to check).
- Heroku CLI installed (`heroku --version` to check; install via `npm install -g heroku` or download from `heroku.com`).
- A Heroku account (sign up at `heroku.com`—no credit card needed for free tier).
- A working local database with migrations and seeders (from my instructions).

---

### Step-by-Step Instructions for Deploying to Heroku

#### Step 1: Prepare Your Project for Heroku
1. **Initialize Git (if not already done)**:
   - In your project root (`order-site`):
     ```bash
     git init
     git add .
     git commit -m "Initial commit for Heroku deployment"
     ```

2. **Add a Procfile**:
   - Heroku needs a `Procfile` to run your app. Create `Procfile` (no extension) in the root:
     ```
     web: vendor/bin/heroku-php-apache2 public/
     ```
   - This tells Heroku to use PHP’s Apache server and serve from the `public/` directory (Laravel’s entry point).

3. **Set PHP Version**:
   - Heroku defaults to an older PHP version. Specify PHP 8.1+ by adding a `composer.json` tweak:
     - Open `composer.json`, ensure:
       ```json
       "require": {
           "php": "^8.1",
           "laravel/framework": "^10.0"
       }
       ```
     - Run:
       ```bash
       composer update
       ```

4. **Optimize `.env` for Heroku**:
   - Heroku uses environment variables instead of a `.env` file. Copy your local `.env` settings for reference, but don’t commit it:
     - Edit `.gitignore` to include:
       ```
       .env
       ```
   - Commit changes:
     ```bash
     git add .
     git commit -m "Added Procfile and PHP version for Heroku"
     ```

---

#### Step 2: Set Up Heroku App
1. **Log In to Heroku**:
   - Run:
     ```bash
     heroku login
     ```
   - A browser window will open; log in with your Heroku credentials.

2. **Create a Heroku App**:
   - In your project root:
     ```bash
     heroku create ordering-site-demo-jp
     ```
   - This creates an app with a URL like `https://ordering-site-demo-jp.herokuapp.com`. Replace `ordering-site-demo-jp` with a unique name if it’s taken.

3. **Verify App Creation**:
   - Check:
     ```bash
     heroku apps
     ```
   - You’ll see `ordering-site-demo-jp` listed.

---

#### Step 3: Add a Free Database (ClearDB MySQL)
1. **Add ClearDB Add-on**:
   - Heroku’s free tier doesn’t include a built-in DB, but ClearDB offers a free MySQL plan:
     ```bash
     heroku addons:create cleardb:ignite -a ordering-site-demo-jp
     ```
   - This provisions a free MySQL database (10 MB, 10 connections—enough for a demo).

2. **Get Database Credentials**:
   - Run:
     ```bash
     heroku config | grep CLEARDB_DATABASE_URL
     ```
   - Output looks like:
     ```
     CLEARDB_DATABASE_URL: mysql://b1234567890abc:abc12345@us-cdbr-east-06.cleardb.net/heroku_1234567890abc?reconnect=true
     ```
   - Parse it:
     - `DB_USERNAME`: `b1234567890abc`
     - `DB_PASSWORD`: `abc12345`
     - `DB_HOST`: `us-cdbr-east-06.cleardb.net`
     - `DB_DATABASE`: `heroku_1234567890abc`

3. **Set Environment Variables**:
   - Configure your Laravel app to use these:
     ```bash
     heroku config:set APP_NAME="発注サイト" -a ordering-site-demo-jp
     heroku config:set APP_ENV=production -a ordering-site-demo-jp
     heroku config:set APP_KEY= -a ordering-site-demo-jp
     heroku config:set APP_DEBUG=false -a ordering-site-demo-jp
     heroku config:set APP_URL=https://ordering-site-demo-jp.herokuapp.com -a ordering-site-demo-jp
     heroku config:set DB_CONNECTION=mysql -a ordering-site-demo-jp
     heroku config:set DB_HOST=us-cdbr-east-06.cleardb.net -a ordering-site-demo-jp
     heroku config:set DB_PORT=3306 -a ordering-site-demo-jp
     heroku config:set DB_DATABASE=heroku_1234567890abc -a ordering-site-demo-jp
     heroku config:set DB_USERNAME=b1234567890abc -a ordering-site-demo-jp
     heroku config:set DB_PASSWORD=abc12345 -a ordering-site-demo-jp
     ```
   - Generate an `APP_KEY`:
     ```bash
     php artisan key:generate --show
     ```
     Copy the output (e.g., `base64:xyz...`) and set it:
     ```bash
     heroku config:set APP_KEY=base64:xyz... -a ordering-site-demo-jp
     ```

---

#### Step 4: Deploy the Application
1. **Push Code to Heroku**:
   - Ensure your Git remote is set:
     ```bash
     heroku git:remote -a ordering-site-demo-jp
     ```
   - Deploy:
     ```bash
     git push heroku main
     ```
   - Heroku will install dependencies (`composer install`) and start the app.

2. **Run Migrations and Seed Data**:
   - Execute migrations:
     ```bash
     heroku run php artisan migrate --app ordering-site-demo-jp
     ```
   - Seed the database with initial data (from my instructions):
     ```bash
     heroku run php artisan db:seed --app ordering-site-demo-jp
     ```

3. **Open the App**:
   - Launch:
     ```bash
     heroku open -a ordering-site-demo-jp
     ```
   - URL: `https://ordering-site-demo-jp.herokuapp.com`.

---

#### Step 5: Verify and Test
1. **Check Login Page**:
   - Visit `https://ordering-site-demo-jp.herokuapp.com/login`.
   - Ensure the Japanese text (e.g., "ログイン") displays correctly (UTF-8 works by default).

2. **Test Authentication**:
   - Log in as admin: `admin1` / `password1`.
   - Log in as store: `1` / `001_abc`.
   - Verify redirects to `/admin` or `/dashboard`.

3. **Test Store Dashboard**:
   - Submit an order (e.g., 5 units for "4/7週").
   - Check success message: "発注が完了しました".
   - View order history.

4. **Test Admin Dashboard**:
   - Add a schedule (e.g., "4/21週", 150 units).
   - Verify totals update (e.g., 6 units for "4/7週" from seed data).

5. **Mobile Responsiveness**:
   - Open on your phone or browser’s mobile view—ensure Bootstrap layout adjusts.

---

#### Step 6: Share with Client
1. **Provide Demo URL**:
   - Share: `https://ordering-site-demo-jp.herokuapp.com`.

2. **Include Credentials**:
   - Email the client (in Japanese):
     ```
     件名: 発注サイトデモのご案内
     [クライアント名]様、
     発注サイトのデモを以下のURLでご確認いただけます：
     URL: https://ordering-site-demo-jp.herokuapp.com
     - 管理者ログイン: admin1 / password1
     - 店舗ログイン: 1 / 001_abc
     ご質問がございましたらお気軽にご連絡ください。
     よろしくお願い申し上げます。
     [あなたの名前]
     ```

3. **Note Free Tier Limits**:
   - Mention: “This is a free demo; the site sleeps after 30 minutes of inactivity and wakes on the next request (10–15s delay).”

---

#### Step 7: Post-Demo Adjustments
1. **Fix Issues**:
   - If the client reports bugs (e.g., Japanese text garbling), check encoding (`utf8mb4_unicode_ci` in DB) or logs:
     ```bash
     heroku logs --tail -a ordering-site-demo-jp
     ```

2. **Prepare for Production**:
   - Once approved, deploy to the client’s server (per my Phase 6 instructions) using their domain.

---

### Key Notes
- **Free Tier Limits**: 550 dyno hours/month (23 days if always on). For a demo (e.g., 1–2 days), this is fine. Sleep mode applies after 30 minutes.
- **Performance**: ClearDB’s free tier is slow (~100ms latency). It’s a demo, so acceptable, but warn the client it’s not production-grade.
- **Japanese Standards**: UTF-8 is default; Bootstrap ensures a clean UI. No extra font setup needed (Heroku uses system fonts like Noto Sans).

### Time Estimate
- Setup and deployment: 1–2 hours (assuming your local app is ready).

### Deliverables
- Live demo at `https://ordering-site-demo-jp.herokuapp.com`.
- Client email with access details.

Follow these steps exactly, and you’ll have a working demo on Heroku by the end of the day. Ping me if you hit errors (e.g., migration fails)—I’ll troubleshoot fast!