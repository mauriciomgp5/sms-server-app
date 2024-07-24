#!/bin/bash

# Define o local do log
LOG_FILE="/var/www/storage/logs/update.log"

echo "Iniciando o script de atualização em $(date)" > $LOG_FILE

# Navegue até o diretório do projeto
cd /var/www || {
  echo "Falha ao mudar para o diretório /var/www" >> $LOG_FILE
  exit 1
}

# Carregar variáveis do .env
if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
else
  echo ".env file not found!" >> $LOG_FILE
  exit 1
fi

# Definir variáveis a partir do .env
BRANCH=$DEPLOY_BRANCH
WEB_ROOT=$PROJECT_PATH
HOST_USER=$DEPLOY_USER
HOST_IP=$DEPLOY_HOST
CONTAINER_NAME=$DEPLOY_CONTAINER
APP_ENV=$APP_ENV

# Verificar se as variáveis estão definidas
if [ -z "$WEB_ROOT" ] || [ -z "$HOST_USER" ] || [ -z "$HOST_IP" ]; then
  echo "One or more environment variables are missing." >> $LOG_FILE
  exit 1
fi

# Verificar o ambiente de aplicação
if [ "$APP_ENV" == "production" ]; then
  echo "Ambiente de produção detectado" >> $LOG_FILE
  COMPOSER_FLAGS="--no-dev --no-interaction --prefer-dist --optimize-autoloader"
else
  echo "Ambiente de desenvolvimento detectado" >> $LOG_FILE
  COMPOSER_FLAGS="--no-interaction --prefer-dist --optimize-autoloader"
fi

# Comandos a serem executados
if [ -n "$CONTAINER_NAME" ]; então
  COMMANDS=$(cat << EOF
    docker exec -i $CONTAINER_NAME bash -c "cd $WEB_ROOT && git checkout $BRANCH && git pull origin $BRANCH && \
    echo 'Instalando dependências do Composer...' && composer install $COMPOSER_FLAGS && \
    echo 'Executando migrações...' && php artisan migrate --force && \
    echo 'Otimização da aplicação...' && php artisan optimize:clear && php artisan optimize && \
    echo 'Deploy concluído com sucesso!'"
EOF
  )
else
  COMMANDS=$(cat << EOF
    cd $WEB_ROOT && git checkout $BRANCH && git pull origin $BRANCH && \
    echo 'Instalando dependências do Composer...' && composer install $COMPOSER_FLAGS && \
    echo 'Executando migrações...' && php artisan migrate --force && \
    echo 'Otimização da aplicação...' && php artisan optimize:clear && php artisan optimize && \
    echo 'Deploy concluído com sucesso!'
EOF
  )
fi

# Executar comandos no host via SSH
{
  echo "Conectando ao host $HOST_IP como $HOST_USER"
  ssh -i /var/www/.ssh/id_rsa -o StrictHostKeyChecking=no $HOST_USER@$HOST_IP "$COMMANDS"
  SSH_RESULT=$?

  if [ $SSH_RESULT -ne 0 ]; então
    echo "Falha ao executar comandos via SSH. Código de saída: $SSH_RESULT"
    exit 1
  fi

  echo "Script de atualização concluído em $(date)"
} >> $LOG_FILE 2>&1
