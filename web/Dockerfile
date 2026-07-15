# Site web + formulaire de création d'événement (planning.unionrolistes.fr).
#
# Pas de CGI ici — V2 remplace justement le CGI Python (jugé "bad practice"
# dans les tickets de suivi de cette migration) par un appel direct PHP →
# planning-api. PHP pur : pas d'interpréteur supplémentaire à sandboxer, pas
# de mod_cgi, surface d'attaque nettement réduite par rapport à l'ancien
# CGI Apache.
#
# php-fpm + nginx sur Alpine plutôt que php:8.4-apache (Debian) : aucune
# RewriteRule/RewriteCond dans le repo, la seule fonctionnalité Apache
# utilisée est le Basic Auth de Calendar/ADMIN/ (.htaccess), qui a un
# équivalent nginx direct (auth_basic, même format .htpasswd). curl est
# déjà compilé statiquement dans php:8.4-fpm-alpine, comme dans la variante
# -apache. apache2-utils reste installé (nom du paquet identique sur
# Alpine) : htpasswd est toujours appelé par docker-entrypoint.sh.
FROM php:8.4-fpm-alpine

RUN apk add --no-cache nginx apache2-utils

COPY nginx.conf /etc/nginx/nginx.conf

COPY www/ /var/www/html/

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]

# php-fpm en arriere-plan (-D), nginx au premier plan comme PID 1.
CMD ["sh", "-c", "php-fpm -D && exec nginx -g 'daemon off;'"]
