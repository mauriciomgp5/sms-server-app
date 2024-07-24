#!/bin/bash

# Navegue até o diretório do projeto
cd /var/www || exit

# Carregar variáveis do .env
if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
else
  echo ".env file not found!"
  exit 1
fi

# Definir variáveis a partir do .env
BRANCH="main"
WEB_ROOT=$PROJECT_PATH
HOST_USER=$DEPLOY_USER
HOST_IP=$DEPLOY_HOST

# Verificar se as variáveis estão definidas
if [ -z "$WEB_ROOT" ] || [ -z "$HOST_USER" ] || [ -z "$HOST_IP" ]; then
  echo "One or more environment variables are missing."
  exit 1
fi

# Comandos para executar no host
COMMANDS=$(cat << EOF
  cd $WEB_ROOT || exit;
  echo "Fazendo git pull da branch $BRANCH...";
  git pull origin $BRANCH;
  echo "Instalando dependências do Composer...";
  composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader;
  echo "Executando migrações...";
  php artisan migrate --force;
  echo "Otimização da aplicação...";
  php artisan optimize:clear;
  php artisan optimize;
  echo "Deploy concluído com sucesso!";
EOF
)

# Executar comandos no host via SSH
ssh $HOST_USER@$HOST_IP "$COMMANDS"
