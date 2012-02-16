Configurazione Apache e Nginx
===================================

Apache
------

Yii è già pronto per lavorare con la configurazione di default di un web server Apache. 
I file .htaccess nelle cartelle del framework e dell'applicazione scritta con Yii limitano 
l'accesso alle risorse. Per nascondere il file di avvio (solitamente index.php) 
negli URLs bisogna aggiungere le istruzioni mod_rewrite al file .htaccess nella 
cartella radice del web server o alla configurazione del virtual host:

~~~
RewriteEngine on

# if a directory or a file exists, use it directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# otherwise forward it to index.php
RewriteRule . index.php
~~~


Nginx
-----

Puoi utilizzare Yii con [Nginx](http://wiki.nginx.org/) e PHP con [FPM SAPI](http://php.net/install.fpm).
Ecco un esempio di configurazione dell'host. Qui viene definito il file di avvio 
e permette a Yii di catturare tutte le richieste di file inesistenti che consente 
di ottenere URL leggibili.

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

    #avoid processing of calls to unexisting static files by yii
    location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
        try_files $uri =404;
    }

    # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
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
Usando questa configurazione, per evitare molte chiamate di sistema stat(), puoi settare cgi.fix_pathinfo=0 nel file php.ini. 

<div class="revision">$Id: quickstart.apache-nginx-config.txt 3512 2011-12-27 16:50:03Z haertl.mike $</div>