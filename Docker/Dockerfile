FROM php:8.4-apache

# 1. Installation des dépendances système pour PostgreSQL et ICU (pour Symfony)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    git \
    unzip \
    && docker-php-ext-install intl pdo pdo_pgsql opcache

# 2. Activation du mod_rewrite d'Apache pour Symfony
RUN a2enmod rewrite

# 3. Changement du DocumentRoot pour pointer vers le dossier /public de Symfony
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN printf '<Directory "${APACHE_DOCUMENT_ROOT}">\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony

# 4. Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Autoriser l'upload d'images d'annonces (jusqu'a 10 Mo)
RUN { \
    echo 'upload_max_filesize = 10M'; \
    echo 'post_max_size = 12M'; \
    } > /usr/local/etc/php/conf.d/uploads.ini

# 6. OPcache : sans lui, PHP recompile tout Symfony (+ vendor/, des milliers
# de fichiers) a chaque requete. Sur ce poste, le bind-mount Docker Desktop
# <-> Windows (le projet vit sur le disque C: et non dans le filesystem
# natif de WSL2) est extremement lent meme pour de simples stat() -- un
# `find vendor -type f` (rien que lister 6695 fichiers, sans les lire) a mis
# plus de 10s, et un `cp -r vendor` plus de 2 minutes. validate_timestamps=1
# (meme avec revalidate_freq eleve) refait donc un stat() par fichier a
# chaque requete et reste tres lent.
# validate_timestamps=0 fait que PHP ne retouche plus jamais le disque une
# fois un fichier mis en cache -- au prix de devoir relancer le conteneur
# pour voir un changement de code PHP :
#   docker restart assime_app
RUN { \
    echo 'opcache.enable = 1'; \
    echo 'opcache.validate_timestamps = 0'; \
    echo 'opcache.memory_consumption = 256'; \
    echo 'opcache.interned_strings_buffer = 16'; \
    echo 'opcache.max_accelerated_files = 20000'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# Ajustement des droits
RUN chown -R www-data:www-data /var/www/html
