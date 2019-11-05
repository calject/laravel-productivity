<?php
/**
 * Author: 沧澜
 * Date: 2019-10-25
 */

namespace Calject\LaravelProductivity\Consoles\Commands;

use CalJect\Productivity\Components\Check\CkDef;
use Calject\LaravelProductivity\Component\Comments\ModelComment;
use Calject\LaravelProductivity\Contracts\Commands\Command;
use ReflectionException;

/**
 * Class ModelCommentCommand
 * @package Calject\LaravelProductivity\Consoles\Commands
 */
class ModelCommentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calject:comment:model {dir? : 执行目录,默认为app/Models}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据模型库表连接生成表注释';
    
    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function handle()
    {
        $options = CkDef::make($this->argument());
        if ($dir = $options['dir']) {
            if (!is_dir($dir)) {
                $this->error("${dir}不是一个正确的目录");
                exit;
            }
        } else {
            $dir = app_path('Models');
        }
        $modelComment = new ModelComment();
        $modelComment->outputByCommand($this)->handle($dir);
        // if ($errLogs = $modelComment->getErrLogs()) {
        //     foreach ($errLogs as $errLog) {
        //         $this->error($errLog);
        //     }
        // } else {
        //     $this->info("all success");
        // }
    }
}