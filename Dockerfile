# Usa imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instala extensões necessárias (mysqli pra MySQL)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copia os arquivos do projeto pro /var/www/html (raiz do Apache)
COPY . /var/www/html/

# Ativa mod_rewrite (se precisar de URLs amigáveis)
RUN a2enmod rewrite
