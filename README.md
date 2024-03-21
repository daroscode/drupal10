# drupal10

Projeto para estudos e testes Drupal 10.

- Versão do Lando: ```3.20.8```
- Versão PHP: ```8.2.16```
- Versão do Node: ```VERIFICAR```
- Versão do Gulp: ```VERIFICAR```

## Acesso Drupal:
- Usuário: ```admin```
- Senha: ```admin```

## Instalação / Estrutura:
- Criar o arquivo settings local: ```cp web/sites/default/example.settings.local.php web/sites/default/settings.local.php```
- Criar o arquivo settings padrão: ```cp web/sites/default/example.settings.php web/sites/default/settings.php```
- Criar o arquivo services: ```cp web/sites/default/example.services.yml web/sites/default/services.yml```
- Iniciar ambiente: ```lando start```
- Instalar Drupal: ```lando composer install```
- Subir banco: ```lando db-import db/database.sql.gz```
- Importar configurações: ```lando drush cim -y```
- Limpar cache: ```lando drush cr```
- Acessar projeto: ```lando drush uli```

## Instalação / Banco de dados:
Quando for criado algo que seja gravado no banco de dados (ao invés de configurações)
- Criar dump do banco: ```lando drush sql-dump > db/database.sql```
- Atualizar no repo: ```rm db/database.tar.xz && gzip -c db/database.sql > db/database.tar.xz && rm db/database.sql```

## Comandos DRUSH:
- Limpeza de cache: ```lando drush cr```
- Exportação de configurações: ```lando drush cex --yes```
- Importação de configurações: ```lando drush cim --yes```
- Acesso ao Admin: ```lando drush uli```
- Importar banco de dados: ```lando drush sql-cli < db/databse.sql```

## Comandos LANDO:
- Iniciar Lando: ```lando start```
- Terminar Lando: ```lando poweroff```
- Reiniciar Lando: ```lando restart -y```
- Destuir Lando: ```lando destroy -y```
- Instalar pacote: ```lando composer require...```
- Identificar erros: ```lando logs -s NOME_SERVICO -f```

## Tema:
- Instalar NPM e Gulp (caso tenha erros durante lando start): ```lando npm-cli install && lando gulp-cli install```
- Entre na pasta do tema: ```cd web/themes/custom/xtreme_bootstrap_sass/```
- Instale o NPM: ```lando npm install```
- Instale o Gulp: ```lando npm install --global gulp-cli```
- Instale Material Design Bootstrap: ```lando npm install mdbootstrap```
- Para atualizar alterações feitas rode: ```lando gulp```
