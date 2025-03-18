# 発注サイト (Order Management System)

このプロジェクトは、日本の店舗管理者が製品の発注を行うためのウェブアプリケーションです。

## 機能

- 店舗ユーザーログイン
- 管理者ユーザーログイン
- スケジュール管理（管理者）
- 発注入力（店舗）
- 発注履歴の確認（店舗・管理者）
- CSVエクスポート（管理者）

## 技術スタック

- PHP 8.0+
- Laravel 9.x
- MySQL 8.0+
- Bootstrap 5
- JavaScript / jQuery

## セットアップ

### 必要条件

- PHP 8.0以上
- Composer
- MySQL 8.0以上
- Node.js と npm

### インストール

1. リポジトリをクローンする
```bash
git clone <repository-url>
cd ordersite
```

2. 依存関係をインストールする
```bash
composer install
npm install
```

3. 環境設定
```bash
cp .env.example .env
php artisan key:generate
```

4. データベース設定
`.env`ファイルを編集して、データベース接続情報を設定します：
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ordersite
DB_USERNAME=root
DB_PASSWORD=
```

5. データベースマイグレーションとシード
```bash
php artisan migrate
php artisan db:seed
```

6. アセットのコンパイル
```bash
npm run dev
```

7. サーバーを起動する
```bash
php artisan serve
```

アプリケーションは `http://localhost:8000` でアクセスできます。

## テスト

アプリケーションのテストを実行するには：

```bash
php artisan test
```

または特定のテストスイートを実行する：

```bash
# ユニットテストのみ実行
php artisan test --testsuite=Unit

# 機能テストのみ実行
php artisan test --testsuite=Feature
```

### テストカバレッジ

テストカバレッジレポートを生成するには（XDebug拡張が必要）：

```bash
XDEBUG_MODE=coverage php artisan test --coverage
```

## デフォルトユーザー

シードデータには以下のデフォルトユーザーが含まれています：

### 管理者
- ログインID: admin1
- パスワード: password

### 店舗ユーザー
- ログインID: tokyo1
- パスワード: password

## ディレクトリ構造

- `app/Models` - データモデル
- `app/Http/Controllers` - コントローラー
- `app/Http/Middleware` - ミドルウェア
- `database/migrations` - データベースマイグレーション
- `database/seeders` - シードデータ
- `resources/views` - Bladeビュー
- `routes` - ルート定義
- `tests` - テストファイル

## 貢献

プロジェクトへの貢献方法については、CONTRIBUTING.mdを参照してください。

## ライセンス

このプロジェクトはMITライセンスの下で公開されています。 