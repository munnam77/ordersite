# 発注サイト デプロイガイド

## 目次

1. [要件](#要件)
2. [サーバー準備](#サーバー準備)
3. [アプリケーションのインストール](#アプリケーションのインストール)
4. [環境設定](#環境設定)
5. [データベース設定](#データベース設定)
6. [Webサーバー設定](#webサーバー設定)
7. [セキュリティ設定](#セキュリティ設定)
8. [本番環境への移行](#本番環境への移行)
9. [バックアップ設定](#バックアップ設定)
10. [トラブルシューティング](#トラブルシューティング)

## 要件

### サーバー要件

- PHP 8.0以上
- MySQL 8.0以上（または同等のMariaDB）
- Nginx または Apache Webサーバー
- Composer
- SSL証明書（推奨）

### 最小サーバースペック

- CPU: 2コア以上
- メモリ: 4GB以上
- ディスク: 20GB以上（SSD推奨）
- 帯域幅: 10Mbps以上

## サーバー準備

### 必要なパッケージのインストール

```bash
# Ubuntuの例
sudo apt update
sudo apt install -y nginx mysql-server php8.1-fpm php8.1-mbstring php8.1-xml php8.1-mysql php8.1-curl php8.1-zip php8.1-gd unzip git

# PHPバージョンを確認
php -v

# MySQLバージョンを確認
mysql --version
```

### Composerのインストール

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

## アプリケーションのインストール

### コードのクローン

```bash
cd /var/www
sudo git clone [repository-url] ordersite
sudo chown -R www-data:www-data ordersite
cd ordersite
```

### 依存パッケージのインストール

```bash
sudo -u www-data composer install --no-dev --optimize-autoloader
```

## 環境設定

### 環境ファイルの設定

```bash
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate
```

### .envファイルの編集

```bash
sudo nano .env
```

以下の設定を環境に合わせて変更します：

```ini
APP_NAME="発注サイト"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ordersite
DB_USERNAME=ordersite_user
DB_PASSWORD=strong_password_here
```

## データベース設定

### データベースとユーザーの作成

```bash
sudo mysql -u root -p
```

MySQLプロンプトで以下を実行：

```sql
CREATE DATABASE ordersite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ordersite_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON ordersite.* TO 'ordersite_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### マイグレーションとシーディング

```bash
cd /var/www/ordersite
sudo -u www-data php artisan migrate --seed
```

## Webサーバー設定

### Nginxの設定

```bash
sudo nano /etc/nginx/sites-available/ordersite
```

以下の内容を追加：

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/ordersite/public;

    # SSL証明書の設定
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
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

シンボリックリンクを作成して設定を有効化：

```bash
sudo ln -s /etc/nginx/sites-available/ordersite /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### SSL証明書の取得（Let's Encrypt）

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

## セキュリティ設定

### ファイルパーミッションの設定

```bash
cd /var/www/ordersite
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data .
```

### 定期タスクの設定

```bash
sudo crontab -e
```

以下の行を追加：

```cron
* * * * * cd /var/www/ordersite && php artisan schedule:run >> /dev/null 2>&1
```

## 本番環境への移行

### キャッシュの最適化

```bash
cd /var/www/ordersite
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### ストレージリンクの作成

```bash
sudo -u www-data php artisan storage:link
```

## バックアップ設定

### 自動バックアップスクリプトの作成

```bash
sudo mkdir -p /var/backups/ordersite
sudo nano /usr/local/bin/backup_ordersite.sh
```

以下のスクリプトを追加：

```bash
#!/bin/bash
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/ordersite"
MYSQL_USER="ordersite_user"
MYSQL_PASSWORD="strong_password_here"
MYSQL_DATABASE="ordersite"

# データベースのバックアップ
mysqldump --user=$MYSQL_USER --password=$MYSQL_PASSWORD $MYSQL_DATABASE | gzip > $BACKUP_DIR/db_$TIMESTAMP.sql.gz

# アプリケーションファイルのバックアップ
tar -zcf $BACKUP_DIR/files_$TIMESTAMP.tar.gz -C /var/www ordersite

# 古いバックアップを削除（30日以上前のもの）
find $BACKUP_DIR -name "*.sql.gz" -type f -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -type f -mtime +30 -delete

echo "Backup completed: $TIMESTAMP"
```

スクリプトに実行権限を付与：

```bash
sudo chmod +x /usr/local/bin/backup_ordersite.sh
```

cronで定期実行するように設定：

```bash
sudo crontab -e
```

以下の行を追加（毎日午前3時に実行）：

```cron
0 3 * * * /usr/local/bin/backup_ordersite.sh >> /var/log/ordersite_backup.log 2>&1
```

## トラブルシューティング

### ログの確認方法

アプリケーションログの確認：

```bash
tail -f /var/www/ordersite/storage/logs/laravel.log
```

Nginxエラーログの確認：

```bash
tail -f /var/log/nginx/error.log
```

PHPエラーログの確認：

```bash
tail -f /var/log/php8.1-fpm.log
```

### よくある問題と解決策

#### 500エラーが発生する場合

1. ログファイルを確認
2. ストレージディレクトリの権限を確認
3. `.env`ファイルが正しく設定されているか確認

```bash
sudo chmod -R 775 /var/www/ordersite/storage
sudo chown -R www-data:www-data /var/www/ordersite/storage
```

#### データベース接続エラー

1. `.env`ファイルのデータベース設定を確認
2. MySQLサービスが実行されているか確認

```bash
sudo systemctl status mysql
```

3. データベースユーザーの権限を確認

```bash
sudo mysql -u root -p
```

```sql
SHOW GRANTS FOR 'ordersite_user'@'localhost';
```

#### キャッシュの問題

キャッシュをクリアして再生成：

```bash
cd /var/www/ordersite
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

---

## 保守と更新

### アプリケーションの更新手順

```bash
cd /var/www/ordersite
sudo -u www-data git pull origin main
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php artisan migrate
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

このガイドに従って発注サイトを正しくデプロイし、安全かつ効率的に運用してください。問題が発生した場合は、まずログを確認し、上記のトラブルシューティング手順を試してみてください。 