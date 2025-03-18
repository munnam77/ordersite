# 発注サイト (Ordering Site)

## プロジェクト概要 (Project Overview)
このプロジェクトは、店舗スタッフが簡単に発注数量を入力し送信できるウェブベースの発注システムです。管理者はスケジュールを管理し、発注状況を確認できます。

This project is a web-based ordering system where store staff can easily input and submit order quantities. Administrators can manage schedules and view order status.

## 主な機能 (Key Features)
- 店舗スタッフ向け認証システム (Store staff authentication)
- 管理者向け認証システム (Administrator authentication)
- スケジュールごとの発注数量入力 (Order quantity input per schedule)
- 発注履歴表示 (Order history display)
- スケジュール管理 (Schedule management)
- 発注状況の集計 (Order status aggregation)

## 技術スタック (Tech Stack)
- PHP 8.1+
- Laravel 10.x
- MySQL 8.0
- Bootstrap 5
- jQuery

## 開発環境構築 (Development Environment Setup)
### 前提条件 (Prerequisites)
- PHP 8.1以上
- Composer
- Node.js と npm
- MySQL

### インストール手順 (Installation Steps)
```bash
# リポジトリをクローン (Clone the repository)
git clone [リポジトリURL]

# プロジェクトディレクトリに移動 (Move to project directory)
cd ordersite

# 依存関係のインストール (Install dependencies)
composer install
npm install

# 環境設定 (Environment setup)
cp .env.example .env
php artisan key:generate

# データベース設定 (Configure your database in .env)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=ordersite
# DB_USERNAME=root
# DB_PASSWORD=

# マイグレーションとシーディング (Run migrations and seeders)
php artisan migrate --seed

# 開発サーバー起動 (Start development server)
php artisan serve
```

## 使用方法 (Usage)
### 店舗スタッフ (Store Staff)
1. ログイン: 店舗IDとパスワードを使用
2. 発注画面: スケジュールを選択し、数量を入力
3. 発注履歴: 過去の発注を確認

### 管理者 (Administrator)
1. ログイン: 管理者IDとパスワードを使用
2. スケジュール管理: スケジュールの追加・編集・削除
3. 発注状況確認: 全店舗の発注状況と集計

## プロジェクト構造 (Project Structure)
```
ordersite/
├── app/
│   ├── Http/
│   │   ├── Controllers/    # コントローラー
│   │   └── Middleware/     # ミドルウェア
│   └── Models/             # データモデル
├── database/
│   ├── migrations/         # データベースマイグレーション
│   └── seeders/           # データシーダー
├── resources/
│   └── views/             # ビューテンプレート
├── routes/
│   └── web.php            # ルート定義
└── public/                # 公開アセット
```

## 本番環境へのデプロイ (Production Deployment)
1. 環境設定
   ```
   APP_ENV=production
   APP_DEBUG=false
   ```
2. 依存関係の最適化
   ```
   composer install --optimize-autoloader --no-dev
   ```
3. アセットのコンパイル
   ```
   npm run build
   ```

## ライセンス (License)
このプロジェクトは非公開です。許可なく複製・配布することはできません。
This project is private. Reproduction or distribution without permission is prohibited.

## コンタクト (Contact)
- 開発者: [開発者名]
- メール: [メールアドレス] 