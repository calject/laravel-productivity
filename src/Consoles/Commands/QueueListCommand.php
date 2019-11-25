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
            (new GeneratorFileLoad($dirPath))->eachFiles(function ($filePath) use ($dirPath, &$queueList, &$jobList) {
                if (!file_exists($filePath) || strpos($filePath, '.php') === false) {
                    return;
                }
                $fileContent = file_get_contents($filePath);
                $class = rtrim(str_replace('/', '\\', str_replace($dirPath, 'App\Jobs', $filePath)), '.php');
                $info['class'] = $class;
                $info['path'] = $filePath;
                if (preg_match("/\\\$this->onQueue\(['\"]+(\w+)['\"]+\)/", $fileContent, $func)) {
                    $queueList[$func[1]][] = $info;
                } else if (preg_match("/(?:private|protected|public)[ ]+\\\$queue[ =]*['\"]+(\w+)['\"]+/", $fileContent, $property)) {
                    $queueList[$property[1]][] = $info;
                } else {
                    $queueList['default'][] = $info;
                }
            });
            if ($queueList) {
                foreach ($queueList as $queue => $items) {
                    foreach ($items as $index => $item) {
                        array_unshift($item, $index == 0 ? $queue : '');
                        $rows[] = $item;
                    }
                }
                $this->table(['queue', 'class', 'path'], $rows ?? []);
            } else {
                $this->error("未订生成任何Job类");
            }
        } else {
            $this->error("Jobs 目录不存在.");
        }
    }
}