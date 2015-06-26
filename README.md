# enyphp study framework

个人学习框架开发使用，目前框架还不成熟<br/>
慢慢的开发与更新<br/>
代码经常变化,目前bug一堆<br/>

---------------------------------------------------
框架简介：<br/>
00. Mvc模式,面向对象<br/>
01. xml数据自动验证过滤格式化<br/>
02. 自动错误处理与日志记录<br/>
03. 基于path_info路由,目前只支持这个<br/>
04. ActiveRcord数据库模式<br/>
05. 钩子插件机制<br/>
06. 模版机制,插件机制<br/>
07. 集成单元测试<br/>
08. session支持文件|mysql|memcached|redis<br/>
09. 支持分布式memcached和redis<br/>
10. 封装常用类库[文件上传下载|HTTP|分页|锁|分布式|WebSocket]<br/>

---------------------------------------------------
计划：<br/>
01. image类,mail类,excel类<br/>
02. 调整整体框架,BUG调整<br/>
03. 加入restful风格接口<br/>

---------------------------------------------------
版本信息：alpha 2015.05.10_0.3<br/>
作者：enychen<br/>

---------------------------------------------------
nginx配置文件<br/>
server {<br/>
        listen 80;<br/>
        server_name www.test.com;<br/>
        root /var/www/enyphp/Bootstrap;<br/>

        try_files $uri /index.php$uri?$args;<br/>

        location ~* \/.*\.php {<br/>
                fastcgi_pass unix:/dev/shm/php-fpm.sock;<br/>
                fastcgi_split_path_info         ^(.+\.php)(.*)$;<br/>
                fastcgi_param PATH_INFO         $fastcgi_path_info;<br/>
                fastcgi_param SCRIPT_NAME       $fastcgi_script_name;<br/>
                fastcgi_param SCRIPT_FILENAME   $document_root$fastcgi_script_name;<br/>
                include fastcgi.conf;<br/>
        }<br/>

        location ~ .*\.(gif|jpg|jpeg|png|bmp|swf|js|css|ico)$ {<br/>
                access_log off;<br/>
                expires 30d;<br/>
        }<br/>

        location ~* /\.ht {<br/>
                deny all;<br/>
        }<br/>

        access_log /var/log/nginx/www.test.com_access_log;<br/>
        error_log /var/log/nginx/www.test.com_error_log;<br/>
}<br/>
---------------------------------------------------

