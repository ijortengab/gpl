<?php

namespace Gpl\WebServer;

class Nginx
{

    const CONTENT_CONFIG_DEFAULT = <<<'EOL'
server {
    listen 80;
    listen [::]:80;
    root {{web_root}};
    index index.php index.html;
    server_name {{host_name}};
    location / {
        try_files $uri /index.php?$query_string;
    }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:{{path_socket}};
    }
}
EOL;
    /**
     *
     */
    public function __construct()
    {
        // return $this;
    }

    /**
     *
     */
    public function execute()
    {

        // return $this;
    }
}
