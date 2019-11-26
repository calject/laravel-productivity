<?php
/**
 * Author: 沧澜
 * Date: 2019-10-25
 */

namespace Calject\LaravelProductivity\Consoles\Commands;

use CalJect\Productivity\Components\Check\CkDef;
use CalJect\Productivity\Components\Snippets\SnippetArray;
use Dotenv\Dotenv;
use Illuminate\Console\Command;

/**
 * Class EnvConfigCommand
 * @package Calject\LaravelProductivity\Consoles\Commands
 */
class EnvConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calject:config:env {comment?}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建env映射配置文件; 参数: no-del:不输出#注释掉的列';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        $options = CkDef::make($this->argument());
        $is_no_del = ($options['comment'] !== '--d');    // 是否不输出注释 即 #开头的列
        
        $envArr = Dotenv::create(app()->environmentPath(), app()->environmentFile())->load();
        $file = config_path('env.php');
        
        $sniArr = SnippetArray::create($envArr);
        $sniArr->callable(function ($key, $value) use ($is_no_del) {
            $name = $this->split($value);
            if ($is_no_del && 1 === strpos('$' . $name, '#')) {
                return '';
            } else {
                return "'${name}' => env('${name}'),";
            }
        });
        $strReader = $sniArr->addPhpHead()->head('return ')->end(';')->get();
        /* ======== 写入到配置文件目录 ======== */
        file_put_contents($file, $strReader);
    }
    
    
    /**
     * @param string $name
     * @return string
     */
    protected function split($name)
    {
        if (strpos($name, '=') !== false) {
            list($name, $value) = array_map('trim', explode('=', $name, 2));
            return trim(str_replace(['export ', '\'', '"'], '', $name));
        } else {
            return $name;
        }
    }
    
    
}