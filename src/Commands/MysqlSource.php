<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\MysqlBin\Commands
     * User: Brahma
     * Date: 2023/2/11
     * Time: 10:59
     */

    namespace Liujinyong\MysqlBin\Commands;

    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\Question;

    class MysqlSource extends Acommand
    {

        public function __construct(string $name = null) { parent::__construct($name); }


        protected function configure()
        {
            $this->setName('mysql:source')->setDescription('导入当前文件夹下的sql到指定的数据库里,可以配合mysql:dump命令联合使用')
                 ->addArgument('directory', InputArgument::OPTIONAL, 'Directory name for composer-driven project');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $helper = $this->getHelperHandle();

            $this->askDatabaseInfo($input, $output);


            $question = new Question("<info>当前导入默认sql文件" . $this->databaseInfo['database'] . ".sql,可变更导入的sql文件为(回车选择默认):</info>");
            $question->setValidator(function($value) use ($output) {
                return $value;
            });

            $sqlFile = $helper->ask($input, $output, $question);

            $file = !empty($sqlFile) ? $sqlFile : $this->databaseInfo['database'] . ".sql";

            if (is_file('./' . $file)) {
                $output->writeln("<info>请再次键入mysql密码，以保证数据安全!</info>");

                $command = "mysql -h" . $this->databaseInfo['host'] . " -u" . $this->databaseInfo['user'] . " -P" . $this->databaseInfo['port'] . " -p " . $this->databaseInfo['database'] . " <" . " ./" . $file;
                exec($command);
                $output->writeln("<info>数据已导入请查看,请注意数据安全!</info>");

            } else {
                $output->writeln("<error>sql文件未找到</error>");

            }


            return 0;

        }
    }