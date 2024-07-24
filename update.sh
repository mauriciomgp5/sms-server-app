#!/bin/bash

# Define variáveis
BRANCH="main"
WEB_ROOT="/var/www"

# Navegue até o diretório do projeto
cd $WEB_ROOT || exit

# Faça o pull do repositório
echo "Fazendo git pull da branch $BRANCH..."
git pull origin $BRANCH

# Ajustar permissões do diretório de cache
echo "Ajustando permissões do diretório de cache..."
chown -R $USER:www-data bootstrap/cache
chmod -R 775 bootstrap/cache

# Instale as dependências do Composer
echo "Instalando dependências do Composer..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Execute as migrações
echo "Executando migrações..."
php artisan migrate --force

# Otimize a aplicação
echo "Otimização da aplicação..."
php artisan optimize:clear
php artisan optimize

# Ajustar permissões dos arquivos gerados
echo "Ajustando permissões dos arquivos gerados..."
chown -R $USER:www-data bootstrap/cache
chmod -R 777 bootstrap/cache

echo "Deploy concluído com sucesso!"
