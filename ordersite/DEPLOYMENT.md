# 発注サイト デプロイメントガイド

このガイドでは、発注サイトをサーバーにデプロイする手順を説明します。

## 前提条件

- PHP 8.1以上
- Composer
- MySQL 8.0以上
- Webサーバー（Apache/Nginx）

## デプロイメント手順

### 1. サーバー環境の設定

#### PHPの拡張機能
以下のPHP拡張機能が有効になっていることを確認してください：
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML

### 2. プロジェクトファイルのアップロード

1. プロジェクトファイルをサーバーにアップロードします
2. ドキュメントルートをプロジェクトの`public`ディレクトリに設定します

### 3. 依存関係のインストール

```bash
cd /path/to/project
composer install --optimize-autoloader --no-dev
```

### 4. 環境設定

1. `.env.example`ファイルを`.env`にコピーします
   ```bash
   cp .env.example .env
   ```
2. アプリケーションキーを生成します
   ```bash
   php artisan key:generate
   ```
3. `.env`ファイルを編集し、データベース接続情報など必要な設定を行います
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ordersite
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

### 5. データベースのセットアップ

1. マイグレーションを実行してテーブルを作成します
   ```bash
   php artisan migrate
   ```
2. シーダーを実行して初期データを投入します
   ```bash
   php artisan db:seed
   ```

### 6. キャッシュの最適化

本番環境のパフォーマンスを向上させるために、以下のコマンドを実行します：

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. ファイルのパーミッション設定

以下のディレクトリにWebサーバーが書き込み権限を持っていることを確認してください：

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 8. Webサーバーの設定

#### Apache (.htaccess)

Apacheを使用している場合、`public`ディレクトリに`.htaccess`ファイルが既に存在しています。
`mod_rewrite`が有効になっていることを確認してください。

#### Nginx (nginx.conf)

Nginxを使用している場合、以下のような設定を行います：

```
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/project/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 9. SSLの設定

本番環境では必ずHTTPS通信を有効にしてください。Let's Encryptを使用して無料のSSL証明書を取得できます。

### 10. メンテナンスと更新

#### アプリケーションのアップデート

アプリケーションを更新する場合は、以下の手順を実行します：

```bash
# メンテナンスモードを有効にする
php artisan down

# コードを更新
git pull origin main

# 依存関係を更新
composer install --optimize-autoloader --no-dev

# マイグレーションを実行
php artisan migrate

# キャッシュをクリアして再生成
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# メンテナンスモードを解除
php artisan up
```

## 初期ログイン情報

デプロイ後、以下のアカウントでログインできます：

### 管理者アカウント
- ログインID: admin1
- パスワード: password1

### 店舗アカウント
- ログインID: 1 (大阪泉北店)
- パスワード: 001_abc

**重要**: 本番環境では必ずこれらのデフォルトパスワードを変更してください！

## トラブルシューティング

### 一般的な問題

1. **ページが表示されない**: ログを確認し、パーミッションやWebサーバーの設定を見直してください。
2. **データベース接続エラー**: `.env`ファイルの接続情報を確認してください。
3. **500エラー**: `storage/logs/laravel.log`を確認してエラーの詳細を調査してください。

サポートが必要な場合は、開発者にお問い合わせください。 