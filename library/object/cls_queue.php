<?php

/**
 * Description: 队列对象
 * User: duanyc@infogo.com.cn
 * Date: 2021/04/06 10:32
 * Version: $Id: cls_queue.php 147146 2021-06-17 02:04:51Z duanyc $
 */

/**
 * Class cls_queue
 */
class cls_queue
{
    /**
     * 执行的文件名
     * @var string
     */
    public $file_name = '';
    /**
     * 执行的参数
     * @var mixed
     */
    public $params = null;
    /**
     * 标识任务是否正常处理完成
     * @var bool
     */
    public $is_done = false;

    /**
     * 构造方法
     *
     * @param string $file_name
     * @param mixed $params
     */
    public function __construct($file_name = '', $params = null)
    {
        $this->file_name = $file_name;
        $this->params = $params;
    }

    /**
     * 执行
     * @return bool
     */
    public function execute()
    {
        $this->is_done = false;
        if (is_file($this->file_name)) {
            //被包含文件可以直接使用$this变量
            try {
                include $this->file_name;
                $this->is_done = true;
            } catch (Exception $e) {
                $this->is_done = false;
            }
        }
        return $this->is_done;
    }
}
