<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\MysqlBin\Commands
     * User: Brahma
     * Date: 2022/5/20
     * Time: 16:57
     */

    namespace Liujinyong\MysqlBin\Commands;

    use Illuminate\Container\Container;
    use Illuminate\Database\Capsule\Manager;
    use Illuminate\Events\Dispatcher;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Helper\Table;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\ConfirmationQuestion;
    use Symfony\Component\Console\Question\Question;
    use Symfony\Component\Filesystem\Filesystem;

    class CreateTable extends Command
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

        protected function configure()
        {
            $this->setName('create:base')->setDescription('create table')
                 ->addArgument('directory', InputArgument::OPTIONAL, 'Directory name for composer-driven project');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            //获取database信息
            $this->askDatabaseInfo($input, $output);
            //创建数据表
            $capsule = new Manager();
            $capsule->addConnection([
                                        'driver'    => 'mysql',
                                        'host'      => $this->databaseInfo['host'],
                                        'database'  => $this->databaseInfo['database'],
                                        'username'  => $this->databaseInfo['user'],
                                        'password'  => $this->databaseInfo['password'],
                                        'port'      => $this->databaseInfo['port'],
                                        'charset'   => 'utf8',
                                        'collation' => 'utf8_unicode_ci',
                                        'prefix'    => $this->databaseInfo['prefix'],
                                    ]);
            $capsule->setEventDispatcher(new Dispatcher(new Container()));
            $capsule->setAsGlobal();
            $capsule->bootEloquent();


            do {
                $question = new Question("[3.name table]，example(<fg=green> project_user </fg=green>):");
                $question->setValidator(function($value) {
                    if (trim($value) == '') {
                        throw new \Exception('数据库连接不能为空！');
                    }
                    if (Manager::schema()->hasTable($value)) {
                        throw new \Exception('数据表已经存在！');

                    }

                    return $value;
                });
                $tableName = $this->getHelperHandle()->ask($input, $output, $question);

                $question->setMaxAttempts(3);

                Manager::schema()->create($tableName, function($table) {
                    $table->id('id')->comment("主键ID");
                    $table->string('name')->nullable(false)->comment("名称");
                    $table->enum('status',['0','1','3'])->nullable(false)->comment("状态:XX,2=XXX,3=XXX");
                    $table->integer('create_time')->nullable()->comment("创建时间");
                    $table->integer('update_time')->nullable()->comment("更新时间");
                    $table->integer('delete_time')->nullable()->comment("删除时间");
                });
                $output->writeln("<info>Table {$tableName} Create Success!!!</info>");
            } while (true);
            return 0;
        }

        protected function askDatabaseInfo($input, $output)
        {
            $helper = $this->getHelperHandle();
            $output->writeln("<info>【mysql-bin】是一个自由度较高的控制台命令</info>,<comment>以下是此命令的流程步骤</comment>");
            $table = new Table($output);
            $table->setHeaders(array('步骤', '事项'))->setRows(array(
                                                               array('[1.连接数据库]', '为创建forum表前需创建orm实例'),
                                                               array('[2.选择数据库]', '选择数据库及设置表前缀'),
                                                               array('[3.生成基础数据表]', '命名数据表'),
                                                           ));
            $table->render();

            $question = new Question("[1.connect database]，example(<fg=green> mysql -h127.0.0.1 -uroot -p123456 -P3306 </fg=green>):");
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
            $question                       = new Question("[2.choose database]，example(<fg=green> use database </fg=green>):");
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

        }


    }