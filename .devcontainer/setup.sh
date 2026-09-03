#!/usr/bin/env bash

set -e

WP_PATH="/var/www/html"


echo "Corrigiendo permisos de directorios de IA montados..."

sudo chown -R vscode:vscode \
    /home/vscode/.claude \
    /home/vscode/.codex \
    2>/dev/null || true


echo "Esperando a WordPress..."

until [ -f "${WP_PATH}/wp-load.php" ]; do
    sleep 1
done


echo "Comprobando WordPress..."


if ! wp core is-installed \
    --path="${WP_PATH}" \
    --allow-root 2>/dev/null
then

    echo "Instalando WordPress..."

    wp core install \
        --path="${WP_PATH}" \
        --url="http://172.30.1.111:9080/" \
        --title="AGJ Theme Development" \
        --admin_user="admin" \
        --admin_password="admin" \
        --admin_email="dev@example.com" \
        --skip-email \
        --allow-root
fi


echo "Comprobando tema..."

CURRENT_THEME=$(
    wp option get stylesheet \
        --path="${WP_PATH}" \
        --allow-root
)


if [ "${CURRENT_THEME}" != "AGJ_Editorial" ]; then

    echo "Activando AGJ_Editorial..."

    wp theme activate AGJ_Editorial \
        --path="${WP_PATH}" \
        --allow-root
fi


echo "Comprobando ZentryGate..."

if ! wp plugin is-active zentrygate \
    --path="${WP_PATH}" \
    --allow-root 2>/dev/null
then

    echo "Activando ZentryGate..."

    wp plugin activate zentrygate \
        --path="${WP_PATH}" \
        --allow-root
fi


echo
echo "=========================================="
echo
echo " WordPress preparado."
echo
echo " Web:   http://172.30.1.111:9080/"
echo " Admin: http://172.30.1.111:9080/wp-admin"
echo
echo " Usuario:    admin"
echo " Contraseña: admin"
echo
echo "=========================================="
echo
