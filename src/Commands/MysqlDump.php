<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\MysqlBin\Commands
     * User: Brahma
     * Date: 2023/2/11
     * Time: 10:14
     */

    namespace Liujinyong\MysqlBin\Commands;

    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class MysqlDump extends Acommand
    {

        public function __construct(string $name = null) { parent::__construct($name); }


        protected function configure()
        {
            $this->setName('mysql:dump')->setDescription('导出数据库的表结构和数据于当前目录下,很实用对不对！哈哈哈')
                 ->addArgument('directory', InputArgument::OPTIONAL, 'Directory name for composer-driven project');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {

            $this->askDatabaseInfo($input, $output);


            $commandStr = "mysqldump -h" . $this->databaseInfo['host'] . " -u" . $this->databaseInfo['user'] . " -p " . $this->databaseInfo['database'] . "  > ./" . $this->databaseInfo['database'] . ".sql";
            $output->writeln("<info>请再次输入mysql密码,用于数据安全验证!</info>");

            exec($commandStr);
            $output->writeln("<info>数据库导出为当前文件夹下的{$this->databaseInfo['database']}.sql,请注意数据安全!</info>");


            return 0;

        }
    }