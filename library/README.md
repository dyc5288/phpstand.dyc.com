## 第三方库
### 根目录的php文件为封装后的调用类，子目录为可能要引入的文件，当然有些库composer引入的。
### 方法可直接调用，不需要require，composer.json的classmap有定义该目录，包括子目录object、xml下的类，可直接使用，不需要require。
#### 1、lib_alarm为报警相关
#### 2、lib_database为数据库操作
#### 3、lib_function为全局方法，init.php中引入，跟之前function.php类似
##### 该文件引入了分类的function目录的部分文件，没有引入的，可使用include_function方法引入，如：include_function('device'); // 引入文件library/function/device.func.php
#### 4、lib_otheruser为第三方认证的库，如钉钉、飞书、企业微信、微信等，封装otheruser目录下的，对外提供调用方式。
#### 5、lib_phpmail为邮件库的封装，对外提供调用方式。
#### 6、lib_queue为队列的封装，对外提供调用方式。
#### 7、lib_redis为redis数据读取写入的封装，对外提供调用方式。
#### 8、lib_request为请求处理的封装，对外提供调用方式。
#### 9、lib_upload为上传的封装，对外提供调用方式。
#### 10、lib_yar为yar的rpc方式调用的封装，对外提供调用方式。
