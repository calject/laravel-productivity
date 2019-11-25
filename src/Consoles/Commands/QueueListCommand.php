<?php
/**
 * Author: 沧澜
 * Date: 2019-11-25
 */

namespace Calject\LaravelProductivity\Consoles\Commands;


use Calject\LaravelProductivity\Contracts\Commands\Command;
use CalJect\Productivity\Utils\GeneratorFileLoad;

/**
 * Class QueueListCommand
 * @package Calject\LaravelProductivity\Consoles\Commands
 */
class QueueListCommand extends Command
{
    
    protected $signature = 'calject:queue:list';
    
    protected $description = '查询匹配所有已定义队列';
    
    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        $dirPath = app_path('Jobs');
        if (is_dir($dirPath)) {
            (new GeneratorFileLoad($dirPath))->eachFiles(function ($filePath) {
                $fileContent = file_get_contents($filePath);
                preg_match("/(?:private|protected|public)[ ]+\$queue[ =]*['\"]+(\w)+['\"]+/", $fileContent, $property);
                if ($property && isset($property[1])) {
                
                }
                preg_match("/\$this->onQueue\(['\"]+(\w)['\"]+\)/", $fileContent, $func);
                if ($func && isset($func[1])) {
                
                }
                
            });
        } else {
            $this->error("Jobs 目录不存在.");
        }
    }
}