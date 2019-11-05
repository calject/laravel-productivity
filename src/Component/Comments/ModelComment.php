<?php
/**
 * Author: 沧澜
 * Date: 2019-10-25
 */

namespace Calject\LaravelProductivity\Component\Comments;

use CalJect\Productivity\Constants\MysqlConstant;
use CalJect\Productivity\Contracts\Comments\ClassHeadComment;
use CalJect\Productivity\Models\FileInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ReflectionClass;

/**
 * Class ModelComments
 * @package Calject\LaravelProductivity\Component\Comments
 */
class ModelComment extends ClassHeadComment
{
    
    const COLUMN_TYPE_STR_POS = 'strpos';
    const COLUMN_TYPE_MATCH = 'match';
    
    // 默认保存数据类型
    protected $columnMatchDefault = 'string';
    
    // 最长的类型字符串，仅用于对齐
    protected $columnMaxString = "string";
    
    // 列值数据匹配
    protected $columnMatch = [
        'int' => [
            'type' => 'strpos',
            'match' => 'int'
        ],
        'string' => [
            'type' => 'match',
            'match' => '#^(int|float)#'
        ]
    ];
    
    /**
     * @param FileInfo $fileInfo
     * @param ReflectionClass $refClass
     * @param string $filePath
     * @return string
     */
    protected function getComments(FileInfo $fileInfo, ReflectionClass $refClass, string $filePath): string
    {
        /* ======== 创建类，获取table、connection、database属性 ======== */
        $modelInfo = $this->getModelProperties($fileInfo->getClass());
        $connection = $modelInfo['connection'];
        $database = $modelInfo['database'];
        $table = $modelInfo['table'];
        /* ======== 检查表是否存在 ======== */
        if (!$this->dbSelect($connection, sprintf(MysqlConstant::SQL_QUERY_TABLE, $table, $database), true)) {
            $this->errLog("no this table(${table}) in connection(${connection}).");
            return '';
        }
        /* ======== 获取数字库字段信息，并生成注释信息 ======== */
        $strMaxLen = ['var' => 0, 'pro' => 0];
        $columnInfo = $this->dbSelect($connection, sprintf(MysqlConstant::SQL_QUERY_COLUMN, $table, $database));
        array_map(function ($info) use (&$comment, &$proArr, &$noting, &$strMaxLen) {
            $vType = $this->match($info->data_type);
            $name = $info->column_name;
            $proArr[$name] = ['var' => $vType, 'str' => $proStr = " * @property \$var \$$name"];
            $noting[$name] = $info->comment;
            $strMaxLen['var'] = ($varLen = strlen($vType)) > $strMaxLen['var'] ? $varLen : $strMaxLen['var'];
            $strMaxLen['pro'] = ($proLen = strlen($proStr) - 4 + $strMaxLen['var']) > $strMaxLen['pro'] ? $proLen : $strMaxLen['pro'];
        }, $columnInfo);
        $comment = '';
        if ($strMaxLen) {
            foreach ($proArr as $name => $content) {
                $content = str_replace('$var', str_pad($content['var'], $strMaxLen['var'], ' '), $content['str']);
                $comment .= ($noting[$name] ? str_pad($content, $strMaxLen['pro'] + 4, ' ') . $noting[$name] : $content) . "\n";
            }
        }
        return $this->commentWithClassHead($fileInfo, $comment);
    }
    
    
    /*---------------------------------------------- info function ----------------------------------------------*/
    
    /**
     * 匹配并返回数据类型
     * @param string $string
     * @return int|string
     */
    public function match($string)
    {
        foreach ($this->columnMatch as $key => $match) {
            if ($match['type'] === self::COLUMN_TYPE_STR_POS && strpos($string, $match['match']) !== false) {
                return $key;
            } else if ($match['type'] === self::COLUMN_TYPE_MATCH && preg_match($match['match'], $string, $result)) {
                return $key;
            }
        }
        return $this->columnMatchDefault;
    }
    
    /**
     * 获取模型数据连接属性
     * @param string|Model $model
     * @return array
     */
    protected function getModelProperties($model)
    {
        if (!is_object($model)) {
            $model = new $model;
        }
        return [
            'table' => $model->getTable(),
            'connection' => $model->getConnection()->getConfig('name'),
            'database' => $model->getConnection()->getDatabaseName()
        ];
    }
    
    /*---------------------------------------------- query function ----------------------------------------------*/
    
    /**
     * 执行sql
     * @param string $connection    连接库
     * @param string $sql           执行的sql
     * @param bool $is_select_one   是否只查询一条数据
     * @return mixed
     */
    protected function dbSelect(string $connection, string $sql, bool $is_select_one = false)
    {
        $db = DB::connection($connection);
        return $is_select_one ? $db->selectOne($sql) : $db->select($sql);
    }
    
}