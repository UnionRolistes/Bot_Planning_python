# Site web + formulaire de création d'événement (planning.unionrolistes.fr).
#
# Pas de CGI ici — V2 remplace justement le CGI Python (jugé "bad practice"
# dans les tickets de suivi de cette migration) par un appel direct PHP →
# planning-api. PHP pur : pas d'interpréteur supplémentaire à sandboxer, pas
# de mod_cgi, surface d'attaque nettement réduite par rapport à l'ancien
# CGI Apache.
FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libcurl4-openssl-dev \
    apache2-utils \
    && docker-php-ext-install curl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY www/ /var/www/html/

# Pas de listing de répertoire (Options -Indexes), et /Calendar/data/ n'est
# jamais servi en HTTP : PHP le lit directement sur disque
# (simplexml_load_file), pas de requête HTTP nécessaire. Le volume monté à
# cet endroit contient aussi webhooks.json (URLs de webhook Discord,
# sensibles — partagé avec planning-api), donc explicitement bloqué plutôt
# que de compter sur l'absence de lien vers ce fichier.
RUN { \
    echo '<Directory "/var/www/html">'; \
    echo '    Options -Indexes +FollowSymLinks'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    echo '<Directory "/var/www/html/Calendar/data">'; \
    echo '    Require all denied'; \
    echo '</Directory>'; \
    } > /etc/apache2/conf-enabled/planning.conf

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
