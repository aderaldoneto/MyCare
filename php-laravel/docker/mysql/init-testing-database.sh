#!/bin/sh

# The MySQL image sources .sh init files into its entrypoint shell. Avoid `set -u`
# here because it would affect the entrypoint after this file returns.
set -e

mysql --user=root --password="$MYSQL_ROOT_PASSWORD" <<SQL
CREATE DATABASE IF NOT EXISTS testing;
GRANT ALL PRIVILEGES ON testing.* TO '${MYSQL_USER}'@'%';
FLUSH PRIVILEGES;
SQL
