<?php
return [
 'default'=>env('MAIL_MAILER','log'),
 'mailers'=>['log'=>['transport'=>'log','channel'=>env('MAIL_LOG_CHANNEL')],'smtp'=>['transport'=>'smtp','host'=>env('MAIL_HOST','127.0.0.1'),'port'=>env('MAIL_PORT',587),'encryption'=>env('MAIL_ENCRYPTION','tls'),'username'=>env('MAIL_USERNAME'),'password'=>env('MAIL_PASSWORD')]],
 'from'=>['address'=>env('MAIL_FROM_ADDRESS','noreply@example.test'),'name'=>env('MAIL_FROM_NAME','Tasks for Today')],
 'task_completed_to'=>env('TASK_COMPLETED_TO'),
];
