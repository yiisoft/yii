Apache- och Nginx-konfigurationer
=================================

Apache
------

Yii är klar att arbeta med en standardkonfiguration för Apache webbserver. 
Filerna `.htaccess` i kataloger som innehåller Yii Framework och applikationer 
förhindrar tillgång till de skyddade resurserna. Startskriptet (vanligen `index.php`) 
kan gömmas i URL:er genom tillägg av `mod_rewrite`-instruktioner i `.htaccess`-filen 
placerad i dokumentrotkatalogen, alternativt läggas till i konfiguration för virtuell host:

~~~
RewriteEngine on

# om en katalog eller fil existerar, använd den
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# i annat fall omdirigera till index.php
RewriteRule . index.php
~~~


Nginx
-----

Yii kan användas med [Nginx](http://wiki.nginx.org/) samt PHP med [FPM SAPI](http://php.net/install.fpm).
Här följer ett exempel på host-konfiguration. Det definierar startskript och ser till att Yii fångar upp alla 
request till ej existerande filer, vilket tillåter användning av estetiskt tilltalande URL:er.

~~~
server {
    set $host_path "/www/mysite";
    access_log  /www/mysite/log/access.log  main;

    server_name  mysite;
    root   $host_path/htdocs;
    set $yii_bootstrap "index.php";

    charset utf-8;

    location / {
        index  index.html $yii_bootstrap;
        try_files $uri $uri/ $yii_bootstrap?$args;
    }

    location ~ ^/(protected|framework|themes/\w+/views) {
        deny  all;
    }

    # undvik bearbetning av anrop till ej existerande statiska filer, av Yii
    location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
        try_files $uri =404;
    }

    # dirigera PHP-skript till FastCGI-servern på 127.0.0.1:9000
    #
    location ~ \.php {
        fastcgi_split_path_info  ^(.+\.php)(.*)$;

        #let yii catch the calls to unexising PHP files
        set $fsn /$yii_bootstrap;
        if (-f $document_root$fastcgi_script_name){
            set $fsn $fastcgi_script_name;
        }

        fastcgi_pass   127.0.0.1:9000;
        include fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fsn;

        #PATH_INFO and PATH_TRANSLATED can be omitted, but RFC 3875 specifies them for CGI
        fastcgi_param  PATH_INFO        $fastcgi_path_info;
        fastcgi_param  PATH_TRANSLATED  $document_root$fsn;
    }

    location ~ /\.ht {
        deny  all;
    }
}
~~~
Med denna konfiguration kan man sätta cgi.fix_pathinfo=0 i php.ini och därmed undvika många onödiga 
stat() systemanrop.

<div class="revision">$Id: quickstart.apache-nginx-config.txt 3512 2011-12-27 16:50:03Z haertl.mike $</div>