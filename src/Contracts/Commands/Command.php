<?php
/**
 * Author: 沧澜
 * Date: 2019-10-30
 */

namespace Calject\LaravelProductivity\Contracts\Commands;

use Illuminate\Console\Command as LaravelCommand;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class Command
 * @package Calject\LaravelProductivity\Contracts\Commands
 *
 * ---------- 获取参数及配置信息 ----------
 * @method string|array|null argument(string $key = null)           获取命令参数 {user*}(多个参数)、{user=foo}(带默认参数)、{user}、{user?}
 * @method array arguments()                                        获取命令参数列表
 *
 * @method string|array|bool|null option(string $key = null)        获取命令配置{--path}、{--path=}、{--path=*}(多个参数)
 * @method array options()                                          获取命令配置列表
 *
 * ---------- 获取输入信息 ----------
 * @method mixed ask(string $question, string $default = null)      获取用户提供的输入信息 ($name = $this->ask('string', 'default'))
 * @method mixed secret($question, $fallback = true)                获取用户提供的输入信息(不可见)($password = $this->secret('string'))
 * @method bool confirm(string $question, bool $default = false)    确认信息,输入y返回true，否则返回false，默认返回false
 *
 * // 方法可用于为可能的选项提供自动完成功能，用户仍然可以选择答案(自动补全) $name = $this->anticipate('What is your name?', ['Taylor', 'Dayle']);
 * @method mixed anticipate($question, array $choices, $default = null)
 *
 * // 给用户提供选择 如果你需要给用户预定义的选择，可以使用 choice 方法。用户选择答案的索引，但是返回给你的是答案的值。如果用户什么都没选的话你可以设置默认返回的值
 * // $name = $this->choice('What is your name?', ['Taylor', 'Dayle'], 0);
 * @method mixed choice(string $question, array $choices, string $default = null, $attempts = null, bool $multiple = null)
 *
 * ---------- 编写输出 ----------
 * @method void info(string $string, $verbosity = null)             显示一条信息消息给用户, 使用 info 方法在终端显示为绿色
 * @method void error(string $string, $verbosity = null)            显示一条信息消息给用户, 错误消息文本通常是红色
 * @method void line(string $string, $verbosity = null)             显示一条信息消息给用户, 输出的字符不带颜色
 *
 * // 表格布局 table 方法使输出多行/列格式的数据变得简单，只需要将头和行传递给该方法，宽度和高度将基于给定数据自动计算
 * // $this->table(['name', 'password'], [['张三', 'xxxx'], ['李四', 'xxxx']])
 * @method void table($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
 *
 * // 进度条 对需要较长时间运行的任务，显示进度指示器很有用，使用该输出对象，我们可以开始、前进以及停止该进度条。在开始进度时你必须定义步数，然后每走一步进度条前进一格
 * // ProgressBar
 * {ProgressBar}->advance();        // 进度条加一格
 * {ProgressBar}->finish();         // 进度条完成
 */
abstract class Command extends LaravelCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';
    
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
     * @return mixed
     */
    abstract public function handle();
    
    /**
     * @param int $max
     * @return ProgressBar
     */
    protected function createProgressBar(int $max = 0): ProgressBar
    {
        return $this->output->createProgressBar($max);
    }
}