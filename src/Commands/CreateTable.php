<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\MysqlBin\Commands
     * User: Brahma
     * Date: 2022/5/20
     * Time: 16:57
     */

    namespace Liujinyong\MysqlBin\Commands;

    use Illuminate\Database\Capsule\Manager;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\Question;

    class CreateTable extends Acommand
    {


        public function __construct(string $name = null) { parent::__construct($name); }


        protected function configure()
        {
            $this->setName('create:base')->setDescription('创建一个基础表,主要是我很懒，不愿意总写那些个time')
                 ->addArgument('directory', InputArgument::OPTIONAL, 'Directory name for composer-driven project');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            //获取database信息
            $this->askDatabaseInfo($input, $output);

            do {
                $question = new Question("[3.命名数据表]，例如(<fg=green> project_user </fg=green>):");
                $question->setValidator(function($value) {
                    if (trim($value) == '') {
                        throw new \Exception('数据表不能为空！');
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
                    $table->enum('status', ['0', '1', '3'])->nullable(false)->comment("状态:1=XXX,2=XXX,3=XXX");
                    $table->integer('create_time')->nullable()->comment("创建时间");
                    $table->integer('update_time')->nullable()->comment("更新时间");
                    $table->integer('delete_time')->nullable()->comment("删除时间");
                });
                $output->writeln("<info>Table {$tableName} Create Success!!!</info>");
            } while (true);

            return 0;
        }


    }