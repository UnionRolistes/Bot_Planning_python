#!/bin/sh
set -eu

# Génère php/config.php à partir des secrets Discord OAuth2 fournis en
# variables d'environnement (Komodo) — régénéré à chaque démarrage, pas
# besoin de volume pour ce fichier.
cat > /var/www/html/php/config.php <<EOF
<?php
define("CLIENT_ID", "${DISCORD_CLIENT_ID:?DISCORD_CLIENT_ID manquant}");
define("CLIENT_SECRET", "${DISCORD_CLIENT_SECRET:?DISCORD_CLIENT_SECRET manquant}");
define("REDIRECT_URI", "${DISCORD_REDIRECT_URI:?DISCORD_REDIRECT_URI manquant}");
?>
EOF

# Génère .htpasswd (accès à /Calendar/ADMIN/) depuis un secret Komodo au lieu
# de le committer en clair dans le repo (l'ancien .htpasswd était versionné,
# repéré comme un vrai souci en préparant cette migration).
htpasswd -cbB /var/www/html/Calendar/ADMIN/.htpasswd \
    "${CALENDAR_ADMIN_USER:-admin}" \
    "${CALENDAR_ADMIN_PASSWORD:?CALENDAR_ADMIN_PASSWORD manquant}"

exec "$@"
