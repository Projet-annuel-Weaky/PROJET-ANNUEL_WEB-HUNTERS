#!/bin/bash

# Dev only: lancer Apache directement
# PHP gère les erreurs de connexion MySQL au runtime

echo "Démarrage Apache..."
exec apache2-foreground

if [ -f "/var/www/html/database/schema.sql" ]; then
    if mysql -h "$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < /var/www/html/database/schema.sql 2>/dev/null; then
        echo -e "Base de données initialisée"
    else
        # Le schéma peut avoir déjà été appliqué
        echo -e "Schéma déjà appliqué ou table existante"
    fi
else
    echo -e "Fichier schema.sql non trouvé"
fi

echo ""

# Vérifier les permissions des dossiers
echo -e "Vérification des permissions..."
chmod 777 /var/www/html/assets/captcha/temp 2>/dev/null || true
chmod 777 /var/www/html/assets/uploads 2>/dev/null || true
chmod 777 /var/www/html/logs 2>/dev/null || true

# Fixer les permissions finales
chown -R www-data:www-data /var/www/html 2>/dev/null || true

echo -e "Permissions vérifiées"
echo ""

echo -e "Conteneur prêt !"
echo ""
echo "Application disponible sur : http://localhost:8080"
echo ""

# Lancer Apache en foreground
exec apache2-foreground
