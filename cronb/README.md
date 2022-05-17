## 定时器
### 放定时器文件
#### 所有定时器由cronb/index.php调度，根据配置文件config/inc_cronb.php周期调用，cronb/index.php该文件已作为定时器执行，每分钟执行。
#### 比如增加定时器test.php，在config/inc_cronb配置执行时间的位置加入test.php即可。
