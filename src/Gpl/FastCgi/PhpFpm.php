<?php

namespace Gpl\FastCgi;

class PhpFpm
{
    const CONTENT_CONFIG_DEFAULT =  <<<EOL
[{{user_process}}]
user = {{user_process}}
group = {{user_process}}
listen = {{path_socket}}
listen.owner = {{webserver_listen_owner}}
listen.group = {{webserver_listen_group}}
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
EOL;
}
