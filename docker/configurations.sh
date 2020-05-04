#!/usr/bin/env bash

author="Yuri Koster, Marcos Freitas";
version="3.8.0";
manual_version="1.0.2";

# @todo Review and Add security rules
# @todo Review function to install SSL with let's encrypt

# including functions.sh file
source "$(dirname "$0")"/functions.sh

# Add necessary repos
function AddRepositories() {
    {
        AptUpdate 'upgrade';

        Separator "Ativando repositórios extras para o Ubuntu 16.04 64 Bits";

        apt-get install -y software-properties-common apt-transport-https;

        Separator "ativando os repositórios Universe e Multiverse" ${LIGHT_GREEN};

        add-apt-repository "deb http://archive.ubuntu.com/ubuntu/ xenial universe multiverse" && \
        echo -ne "\n" | add-apt-repository ppa:ondrej/php  && \
        echo -ne "\n" | add-apt-repository ppa:ondrej/nginx  && \
        apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C && \
        apt-key adv --keyserver keyserver.ubuntu.com --recv-keys E5267A6C;

        AptUpdate 'upgrade';

        apt-get -y auto-remove;

    } || {

        Count $1 'AddRepositories';

    }
}

# Install Nginx and enable it on UFW Firewall, also, protect with chmod its directory
function InstallNginx() {
    {
        Separator "Instalando e Configurando o Servidor Nginx";

        wget http://nginx.org/keys/nginx_signing.key && \
        echo "deb http://nginx.org/packages/mainline/ubuntu/ xenial nginx" >> /etc/apt/sources.list && \
        echo "deb-src http://nginx.org/packages/mainline/ubuntu/ xenial nginx" >> /etc/apt/sources.list && apt-key add ./nginx_signing.key;

        AptUpdate;

        apt-get install -y nginx && systemctl enable nginx;

        mkdir /etc/nginx/sites-available;
        mkdir /etc/nginx/sites-enabled;

        service nginx stop;

        sudo ufw allow 80 && sudo ufw allow 443;
        ufw enable;

        Separator "Protegendo os diretórios de configuração do Nginx" ${LIGHT_GREEN};
        chmod 0750 -R /etc/nginx/;

    } || {
        Count $1 'InstallNginx';
    }
}

function CompileNginxModules() {
    {
        Separator "Compilando módulos extras para o NGINX" ${LIGHT_GREEN};
        printf "\nhttps://www.nginx.com/blog/compiling-dynamic-modules-nginx-plus\n";

        wget https://github.com/AirisX/nginx_cookie_flag_module/archive/master.zip;

        wget http://nginx.org/download/nginx-1.15.5.tar.gz;

        tar zxvf nginx-1.15.5.tar.gz;

        Separator "Compilando e copiando o módulo dinâmico para o diretório padrão de módulos do NGINX" ${CYAN};

        cd nginx-1.15.5 && \
        ./configure --with-compat --add-dynamic-module=../nginx_cookie_flag_module-master;

        make modules && \
        cp objs/ngx_http_cookie_flag_filter_module.so /etc/nginx/modules;

        Separator "Carregando o Conector Compilado" ${CYAN}

        mkdir /etc/nginx/modules-enabled;
        touch /etc/nginx/modules-enabled/50-ngx_http_cookie_flag_filter_module.conf;
        echo 'load_module modules/ngx_http_cookie_flag_filter_module.so;' > /etc/nginx/modules-enabled/50-ngx_http_cookie_flag_filter_module.conf;
        sed -i '21iset_cookie_flag HttpOnly secure;' /etc/nginx/snippets/security-locations.conf;

    } || {
        exit 0;
    }
}

# Add and Enable virtual host files
# For each site hosted in this server, you should create a individual file for its virtual host
# - into the folder "/etc/nginx/sites-available", then enable the site creating a simbolic link into "/etc/nginx/site-enabled"
function AddVirtualHostFiles() {
    {

        Separator "Ajustando arquivo do VirtualHost do projeto:";

        cp /vhost/vhost-app.conf /etc/nginx/sites-available/app;
        mv /etc/nginx/nginx.conf /etc/nginx/nginx.conf.bkp;
        cp /vhost/nginx.conf /etc/nginx/nginx.conf;

        cp -r /vhost/snippets /etc/nginx;

        ln -s /etc/nginx/sites-available/app /etc/nginx/sites-enabled/;

        # file doesn't exist
        #rm /etc/nginx/sites-enabled/default;

    }||{
        Count $1 'AddVirtualHostFiles';
    }
}

# Configuring virtual host folders
# protect folders giving access only to the process of nginx
function AdjustVirtualHostFolders() {
    {
        Separator "Ajustando diretórios do VirtualHost do projeto:";

        # site
	    mkdir -p /var/www/html && \
        chmod 755 /var/www && \
        chmod 2755 /var/www/html && \
        chown -R www-data:www-data /var/www;

        # app
        mkdir -p /var/www/app && \
        chmod 755 /var/www && \
        chmod 2755 /var/www/app && \
        chown -R www-data:www-data /var/www;

    }||{
         Count $1 'AdjustVirtualHostFolders';
    }
}

# Install PHP7.2-fpm and its extensions
function InstallPHP() {
    {

        Separator "Instalando PHP 7.2 e as principais extensões utilizadas";

        apt-get -y install php7.2-fpm;

        service php7.2-fpm restart;
        service nginx restart;

        # extensions

        apt-get -y install php7.2-mbstring;
        apt-get -y install php7.2-bcmath;
        apt-get -y install php7.2-xml;
        apt-get -y install php7.2-curl;
        apt-get -y install php7.2-mysql

        service php7.2-fpm restart;
        service nginx restart;

    } || {
        Count $1 'InstallPHP'
    }
}

# Install some software dependencies packages
# @version 1.0.1
function InstallSoftwareDependencies() {
    {
        Separator "Instalando dependências de software comuns durante o desenvolvimento";

        apt-get -y install composer;

        export COMPOSER_HOME="$HOME/.config/composer"

    } || {
        Count $1 'InstallSoftwareDependencies'
    }
}

Separator "For Ubuntu 16.04 64-Bit | Version $version based on 'Manual de Infraestrutura' $manual_version | Author: $author" ${CYAN};
Separator "As configurações de SSL não estão inclusas neste processo" ${RED};

# working with the arguments received
develop=false;
while getopts ':i:d' opt; do
    case "${opt}" in
        d)
            develop=true;
        ;;
        i )
            install=${OPTARG}
            ;;
        : )
            echo "Invalid option: ${OPTARG} requires an argument" 1>&2
            ;;
        \? )
            echo "Invalid call"
            ;;
        * )
            echo "Usage: ./configurations.sh -i something"
            ;;
    esac
done
shift $((OPTIND -1));


if [ -z "$install" ]
then
    Separator "Argumento de instalação necessario" ${RED}
    exit
fi

AptUpdate;
AddExtraPackages 1;

if [ $develop == false ]; then
    Separator "Modo de produção escolhido"
    InstallUFW 1;
else
    Separator "Modo de desenvolvimento escolhido"
fi

if [[ $install == "nginx" || $install == "all" ]]; then

    # Calling all methods passing 1 as a initial value to counter;
    InstallNginx 1;
    # @bug Module not working
    AddVirtualHostFiles 1;
    #CompileNginxModules 1;
    AdjustVirtualHostFolders 1;
    ProtectProjectDirectories 1;
fi

if [[ $install == "php" || $install == "all" ]]; then
    AddRepositories 1;
    InstallPHP 1;
    InstallSoftwareDependencies 1;
    # @bug Module not working
    #InstallModSecurity 1;
    #InstallOWASPCRS 1;
fi

if [ $develop == false ]; then
    Security 1;
    Fail2Ban 1;
fi

if [[ "${install}" != "php" && "${install}" != "nginx" && "${install}" != "all" ]]; then
    echo "Invalid option for install: ${instfall}" 1>&2
fi
