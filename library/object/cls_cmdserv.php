<?php

/**
 * 命令行操作类
 * Class cls_cmdserv
 */
class cls_cmdserv
{
    /**
     * 指定操作key
     * @var string
     */
    private $action_key = 'a';
    /**
     * 目标对象
     * @var object
     */
    private $target = null;

    /**
     * 构造方法
     * @param object $target
     * @throws Exception
     */
    public function __construct($target)
    {
        if (PHP_SAPI != 'cli') {
            header("http/1.1 404 Not Found");
            exit();
        }
        if (!is_object($target)) {
            throw new Exception('object only');
        }
        $this->target = $target;
    }

    /**
     * 执行命令行
     * @return mixed
     */
    public function handle()
    {
        $return = [];
        $flag = hlp_common::getCmdFlag();
        $action = !empty($flag[$this->action_key]) ? trim($flag[$this->action_key]) : '';
        if (!is_callable(array($this->target, $action))) {
            $this->help($flag);
        } else {
            unset($flag[$this->action_key]);
            $return = $this->target->$action($flag);
        }
        return $return;
    }

    /**
     * 帮助
     * @param $flag[name] string 指定名称
     */
    public function help($flag)
    {
        $ref = new ReflectionClass($this->target);
        $name = !empty($flag['name']) ? $flag['name'] : null;
        $mds  = array();
        if ($name) {
            try {
                $_md = $ref->getMethod($name);
                $mds[] = $_md;
            } catch (Exception $e) {
                printf($e->getMessage(), "\n");
                exit;
            }
        } else {
            $mds = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
        }
        printf("php " . pathinfo($ref->getFileName(). PATHINFO_BASENAME));
        printf(" -{$this->action_key} [");
        $val = array();
        $length = 0;
        foreach ($mds as $n) {
            $doc = $n->getDocComment();
            preg_match_all("/\\s+\\*\\s*(.+)\n/", $doc, $m);
            if (!empty($m[1])) {
                foreach ($m[1] as &$v) {
                    $v = preg_replace('/\@param(.+?\$(flag|args?|params?))?/i', '', $v);
                }
            }
            $length = max(strlen($n->name), $length);
            $glue   = str_pad("\n\t\t", $length + 3, ' ') . '-';
            $val[$n->name] = !empty($m[1]) ? implode($glue, $m[1]) : '';
        }
        $langData = ['help' => '打印本帮助信息'];
        $val['help'] = $langData['help'];
        if (count($val) > 5) {
            printf(implode('|', array_slice(array_keys($val), 0, 5)) . "|...]\n");
        } else {
            printf(implode('|', array_keys($val)). "]\n");
        }
        foreach ($val as $k => $c) {
            $k = str_pad($k, $length, ' ');
            printf(utf8ToGbk("\t{$k}\t{$c}\n"));
        }
        unset($ref);
        printf("\n");
    }
}
