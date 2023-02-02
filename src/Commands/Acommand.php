<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\MysqlBin\Commands
     * User: Brahma
     * Date: 2023/1/17
     * Time: 14:01
     */

    namespace Liujinyong\MysqlBin\Commands;

    use Illuminate\Container\Container;
    use Illuminate\Database\Capsule\Manager;
    use Illuminate\Events\Dispatcher;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Helper\Table;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Question\Question;

    class Acommand extends Command
    {
        /**
         * @var string
         */
        protected $subDir;
        /**
         * @var string
         */
        protected $packDir;
        /**
         * @var string[]
         */
        protected $databaseInfo = [
            'host'     => '',
            'port'     => '',
            'database' => '',
            'user'     => '',
            'password' => '',
            'prefix'   => '',
        ];

        public function __construct(string $name = null) { parent::__construct($name); }

        protected function getHelperHandle()
        {
            return $this->getHelper("question");

        }

        protected function askDatabaseInfo($input, $output)
        {
            $helper = $this->getHelperHandle();
            $output->writeln("<info>【mysql-bin】是一个自由度较高的控制台命令,仅使用于mysql5.7以上版本</info>");
            $output->writeln("<comment>  以下是此命令的流程步骤</comment>");
            $table = new Table($output);
            $table->setHeaders(array('步骤', '事项'))->setRows(array(
                                                               array('[1.连接实例]', '创建连接实例'),
                                                               array('[2.选择数据库]', '选择数据库'),
                                                               array('[3.执行命令]', '执行全局命令'),
                                                           ));
            $table->render();

            $question = new Question("[1.连接实例]，例如(<fg=green> mysql -h127.0.0.1 -uroot -p123456 -P3306 </fg=green>):");
            $question->setValidator(function($value) use ($output) {
                if (trim($value) == '') {
                    throw new \Exception('数据库连接不能为空！');
                }
                $databaseInfo = explode(" ", $value);
                $host         = $user = $password = $port = "";
                foreach ($databaseInfo as $item) {

                    if (strpos(trim($item), '-h') !== false && '-h' == substr(trim($item), 0, 2)) {
                        $host = substr(trim($item), 2);
                    }
                    if (strpos(trim($item), '-u') !== false && '-u' == substr(trim($item), 0, 2)) {
                        $user = substr(trim($item), 2);
                    }
                    if (strpos(trim($item), '-P') !== false && '-P' == substr(trim($item), 0, 2)) {
                        $port = substr(trim($item), 2);
                    }
                    if (strpos(trim($item), '-p') !== false && '-p' == substr(trim($item), 0, 2)) {
                        $password = substr(trim($item), 2);
                    }
                }

                if ($host == "" || $user == "" || $password == "") {
                    throw new \Exception('参数有误');
                }
                if ($port == "") {
                    $port = 3306;
                }
                if ((int)trim($port) < 0 || (int)trim($port) > 65535) {
                    throw new \Exception('数据库端口有误');
                }

                return [$host, $user, $password, $port];
            });

            $database = $helper->ask($input, $output, $question);
            $question->setMaxAttempts(3);
            $this->databaseInfo['host']     = $database[0];
            $this->databaseInfo['user']     = $database[1];
            $this->databaseInfo['password'] = $database[2];
            $this->databaseInfo['port']     = $database[3];

            $question = new Question("[2.选择数据库],例如(<fg=green> use database </fg=green>):");
            $question->setValidator(function($value) use ($output) {
                if (trim($value) == '') {
                    throw new \Exception('选择数据库不能为空！');
                }
                $databaseInfo = explode(" ", $value);
                $database     = trim($databaseInfo[1]);

                if ($database == "") {
                    throw new \Exception('数据库选择错误');
                }

                return [$database];
            });

            $database = $helper->ask($input, $output, $question);
            $question->setMaxAttempts(3);
            $this->databaseInfo['database'] = $database[0];
            $this->databaseInfo['prefix']   = '';


            //连接数据库
            $capsule = new Manager();
            $a       = $capsule->addConnection([
                                                   'driver'    => 'mysql',
                                                   'host'      => $this->databaseInfo['host'],
                                                   'database'  => $this->databaseInfo['database'],
                                                   'username'  => $this->databaseInfo['user'],
                                                   'password'  => $this->databaseInfo['password'],
                                                   'port'      => $this->databaseInfo['port'],
                                                   'charset'   => 'utf8mb4',
                                                   'collation' => 'utf8mb4_general_ci',
                                                   'prefix'    => $this->databaseInfo['prefix'],
                                               ]);


            $capsule->setEventDispatcher(new Dispatcher(new Container()));
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            try {
                Manager::select('show tables');

            }catch (\Exception $exception){
                throw new \Exception("数据库连接失败,请检查配置");
            }


        }
    }