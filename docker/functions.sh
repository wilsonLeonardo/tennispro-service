#!/usr/bin/env bash

f_version="2.5.0"
f_authors="Marcos Freitas, Yuri Koster";

# colors
# Black        0;30     Dark Gray     1;30
# Red          0;31     Light Red     1;31
# Green        0;32     Light Green   1;32
# Brown/Orange 0;33     Yellow        1;33
# Blue         0;34     Light Blue    1;34
# Purple       0;35     Light Purple  1;35
# Cyan         0;36     Light Cyan    1;36
# Light Gray   0;37     White         1;37

RED="\033[0;31m";
CYAN="\033[0;36m";
YELLOW="\033[1;33m";
LIGHT_GREEN="\033[1;32m";

# No Color
NC="\033[0m";

# script status
SCRIPT_STATUS=$?

# run this script as sudo
if [[ $EUID -ne "0" ]]; then
    sudo "${0}" "${@}";
fi

#
# Common functions used into configuration.sh files for All Images
#

# Receive a current count number on position $1;
# Receive a function name on position $2;
# not using but $0 is the name of the script itself;
function Count() {

    # check if position $1 exists
    if [ -z "$1" ]; then
        echo "Expected param 1";
        exit 0;
    fi

    if [ -z "$2" ]; then
        echo "Expected param 2";
        exit 0;
    fi

    if [ ${1} -ge 1 ]; then
        count=$1;
    fi

    if [ ${count} -le 3 ]; then
        echo -e ${RED};
        printf "\nUma saída inesperada ocorreu durante a última instrução, mas tudo pode estar bem.\nDeseja executar novamente o processo $2?\n"
        echo -e ${NC};
        read -n1 -r -p "Pressione S para continuar ou N para cancelar: " key

        # $key is empty when ENTER/SPACE is pressed
        if [ "$key" = 'S' -o "$key" = 's' ]; then
            echo -e ${CYAN};
            echo "Tentativa " ${count} " de 3...";
            echo -e ${NC};
            ${2} $((count += 1));
        else
            return 1;
        fi

    else
        echo "Não foi possível realizar a operação em $2, abortando o processo";
    fi
}

# receive the output string on position ${1} and a optional color on position ${2}
# version 1.1.0
function Separator() {
    echo '';
    echo '';

    # if ${2} is empty
    if [ -z "${2}" ]; then
        echo -e ${YELLOW};
    else
        echo -e ${2};
    fi

    echo '::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::';
    echo ' ' ${1};
    echo '::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::';
    echo -e ${NC};
    echo '';
    echo '';
}

# wait a key press to continue the process
# version 1.0.0
function PressKeyToContinue() {
    printf "\n";
    read -n1 -r -p "Pressione S para continuar ou qualquer outra tecla para cancelar a execução do script: " key

    if [ "${key}" = 'S' -o "${key}" = 's' ]; then
        # $key is empty when ENTER/SPACE is pressed
        return 1;
    else
        exit 1;
    fi
}

# change values into configuration files. Receives $key $separator $value $file
# version 1.0.0
function ChangeValueConfig(){
    {
        Separator 'sed  -i "s|\('${1}' *'${2}'*\).*|\1'${3}'|" '${4}';';
        sed  -i "s|\('${1}' *'${2}'*\).*|\1'${3}'|" '${4}';
    } || {
        Count ${1} 'ChangeValueConfig';
    }
}

# @todo improve this function to receive "upgrade" command
function AptUpdate() {
    Separator "Atualizando repositórios..." ${LIGHT_GREEN};
    apt-get -y update;
}

function AddExtraPackages() {
    {
        Separator "Preparando pré-requisitos";

        Separator "Instalando o pacote wget para capturar o conteúdo de uma URL, o editor nano e o pacote unzip para manipular arquivos .zip e outros pacotes auxiliares" ${LIGHT_GREEN};
        apt-get -y install wget nano unzip curl tree;

        Separator "Instalando pacotes adicionais para a configuração do NGINX e PHP" ${LIGHT_GREEN};
        apt-get -y install tar bzip2 gcc;

	    Separator "Instalando pacotes que auxiliam no gerenciamento e debug de conflitos de infraestrutura" ${LIGHT_GREEN};
	    apt-get install -y net-tools;

        Separator "Instalando pacote de Idiomas Inglês:" ${LIGHT_GREEN};

        apt-get -y install language-pack-en;
        
        printf '\n[ Status dos locales do sistema ]\n';
        locale;

    } || {
        Count ${1} 'AddExtraPackages';
    }
}


# Install UFW Firewall
function InstallUFW(){
    {
        Separator "Instalando UFW Firewall e configurando para não usar IPv6";
        apt-get install -y ufw;

        # desativando IPV6, o docker não utiliza neste contexto
        sed  -i "s/\(IPV6 *= *\).*/\1no/" /etc/default/ufw;

    } || {
        exit 0;
    }
}

function ProtectProjectDirectories() {
    {
        Separator "Protegendo os diretórios e arquivos do Projeto";
        cd /var/www/;
        find html -type d -exec chmod -R 755 {} \; && \
        find html -type f -exec chmod -R 644 {} \;

    } || {
        Count ${1} 'ProtectProjectDirectories';
    }
}


function InstallModSecurity() {
    {
        Separator "Instalando o ModSecurity 3";

        Separator "Instalando os pré-requisitos" ${LIGHT_GREEN};

        apt-get install -y apt-utils autoconf automake build-essential git libcurl4-openssl-dev libgeoip-dev liblmdb-dev libpcre++-dev libtool libxml2-dev libyajl-dev pkgconf wget zlib1g-dev;


        Separator "Baixando e Compilando o código-fonte do ModSecurity 3" ${LIGHT_GREEN};

        cd /etc/ && \
        git clone --depth 1 -b v3/master --single-branch https://github.com/SpiderLabs/ModSecurity;

        cd ModSecurity && git submodule init && git submodule update && \
        ./build.sh && ./configure && make && make install;

        "Baixando e Compilando o conector do NGINX para o ModSecurity" ${LIGHT_GREEN};

        git clone --depth 1 https://github.com/SpiderLabs/ModSecurity-nginx.git;

        wget http://nginx.org/download/nginx-1.15.5.tar.gz;

        tar zxvf nginx-1.15.5.tar.gz;

        Separator "Compilando e copiando o módulo dinâmico para o diretório padrão de módulos do NGINX" ${LIGHT_GREEN};

        cd nginx-1.15.5 && \
        ./configure --with-compat --add-dynamic-module=../ModSecurity-nginx;

        make modules && \
        cp objs/ngx_http_modsecurity_module.so /etc/nginx/modules;

        Separator "Carregando o Conector Compilado" ${LIGHT_GREEN}

        touch /etc/nginx/modules-enabled/modsecurity.conf;
        echo 'load_module modules/ngx_http_modsecurity_module.so;' >> /etc/nginx/modules-enabled/modsecurity.conf;

        Separator "Configurando, Ativando e Testando o ModSecurity" ${LIGHT_GREEN}

        mkdir /etc/nginx/modsec && \
        wget -P /etc/nginx/modsec/ https://raw.githubusercontent.com/SpiderLabs/ModSecurity/v3/master/modsecurity.conf-recommended && \
        mv /etc/nginx/modsec/modsecurity.conf-recommended /etc/nginx/modsec/modsecurity.conf;

        sed -i 's/SecRuleEngine DetectionOnly/SecRuleEngine On/' /etc/nginx/modsec/modsecurity.conf;

        echo '# From https://github.com/SpiderLabs/ModSecurity/blob/master/' >  /etc/nginx/modsec/main.conf &&\
        echo '# modsecurity.conf-recommended' >> /etc/nginx/modsec/main.conf &&\
        echo '#' >> /etc/nginx/modsec/main.conf &&\
        echo '# Edit to set SecRuleEngine On' >> /etc/nginx/modsec/main.conf &&\
        echo 'Include "/etc/nginx/modsec/modsecurity.conf"' >> /etc/nginx/modsec/main.conf

        Separator "Regra para teste de funcionamento do ModSecurity" ${LIGHT_GREEN}

        echo '# Basic test rule' >> /etc/nginx/modsec/main.conf &&\
        echo 'SecRule ARGS:testparam "@contains test" "id:1234,deny,status:403"' >> /etc/nginx/modsec/main.conf;

        sed -i '3i modsecurity on;' /etc/nginx/sites-available/app &&\
        sed -i '3i modsecurity_rules_file /etc/nginx/modsec/main.conf;' /etc/nginx/sites-available/app;

        sudo service nginx restart;

    } || {
        Count ${1} 'InstallModSecurity';
    }
}


function InstallOWASPCRS() {
    {
        Separator "OWASP ModSecurity Core Rule Set (CRS)";

        cd /etc/nginx/modsec && git clone https://github.com/SpiderLabs/owasp-modsecurity-crs.git && \
        cd owasp-modsecurity-crs && cp crs-setup.conf.example crs-setup.conf && cd ../ && \
        sed -i '6iInclude "/etc/nginx/modsec/owasp-modsecurity-crs/crs-setup.conf"' /etc/nginx/modsec/main.conf && \
        sed -i '7iInclude "/etc/nginx/modsec/owasp-modsecurity-crs/rules/*.conf"' /etc/nginx/modsec/main.conf;

        sudo service nginx restart;

    } || {
       exit 0;
    }
}

function Security() {
    {
        # Deny ICMP timestamp
        sed -i '46i-A ufw-before-input -p icmp --icmp-type timestamp-reply -j DROP' /etc/ufw/before.rules;
        sed -i '47i-A ufw-before-input -p icmp --icmp-type timestamp-request -j DROP' /etc/ufw/before.rules;
    } || {
       exit 0;
    }
}

function Fail2Ban() {
    {
        sudo apt-get -y install fail2ban;

        mv /fail2ban/fail2ban-jail.conf /etc/fail2ban/jail.local;

        cd /etc/fail2ban/filter.d;

        cp apache-badbots.conf nginx-badbots.conf;

        mv /fail2ban/fail2ban-nginx-nohome.conf ./nginx-nohome.conf;
        mv /fail2ban/fail2ban-nginx-noproxy.conf ./nginx-noproxy.conf;
        
         touch /var/log/auth.log;

        service fail2ban restart;
        iptables -S;
        fail2ban-client status;

    } || {
      exit 0;
    }
}

Separator "Using functions.sh version $f_version | Authors: $f_authors" ${LIGHT_GREEN};