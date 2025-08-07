#!/usr/bin/env bash
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive

# 1. Install the minimal binaries
apt-get update -qq
apt-get install -yqq --no-install-recommends \
        mysql-server-core-8.0 mysql-client-core-8.0 passwd

# 2. Ensure the mysql system account exists
if ! id -u mysql >/dev/null 2>&1; then
  groupadd --system mysql
  useradd  --system --gid mysql --home /nonexistent \
           --shell /usr/sbin/nologin mysql
fi

# 3. Data directories **and** secure-file-priv directory
mkdir -p /var/lib/mysql            \
         /var/lib/mysql-files      \
         /var/run/mysqld
chown -R mysql:mysql /var/lib/mysql /var/lib/mysql-files /var/run/mysqld
chmod 750 /var/lib/mysql-files     # MySQL expects owner-only access:contentReference[oaicite:1]{index=1}

# 4. Initialise and start detached
mysqld --initialize-insecure --user=mysql                                # empty root pwd:contentReference[oaicite:2]{index=2}
mysqld --daemonize --user=mysql \
       --socket=/var/run/mysqld/mysqld.sock \
       --pid-file=/var/run/mysqld/mysqld.pid

# 5. Wait until the server is ready
until mysqladmin --socket=/var/run/mysqld/mysqld.sock ping --silent; do sleep 1; done
#verify
# Should print exactly one mysqld line
ps -fp $(cat /var/run/mysqld/mysqld.pid)
# Fast “is it alive?” check
mysqladmin --socket=/var/run/mysqld/mysqld.sock ping
# →  mysqld is alive

# A few server counters & compile flags
mysqladmin --socket=/var/run/mysqld/mysqld.sock version

# Install PHP and Composer
/bin/bash -c "$(curl -fsSL https://php.new/install/linux)"
export PATH="/root/.config/herd-lite/bin/:$PATH"

# Set root password and create databases
mysql -u root <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'secret';
FLUSH PRIVILEGES;
CREATE DATABASE ShipTown;
CREATE DATABASE ShipTown_phpunit;
EOF

# Install Composer dependencies
composer install

# Install ShipTown
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --force -n
php artisan migrate:fresh --force -n --database=phpunit
npm install
