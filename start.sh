#!/usr/bin/env bash
# Start the CBW Business World site locally.
set -e
cd "$(dirname "$0")"

if ! mysqladmin ping -h 127.0.0.1 --silent 2>/dev/null; then
  echo "MySQL is not running. Start it with: sudo systemctl start mysql"
  exit 1
fi

PORT="${1:-8080}"
echo "Global Media Star   ->  http://localhost:${PORT}"
echo "Admin               ->  http://localhost:${PORT}/wp-admin  (admin / admin123)"
echo "Ctrl-C to stop."
# The built-in server is single-threaded by default; a magazine page pulls
# dozens of images at once and would otherwise serialise into a stall.
export PHP_CLI_SERVER_WORKERS="${PHP_CLI_SERVER_WORKERS:-8}"

exec php -S "127.0.0.1:${PORT}" -t .
