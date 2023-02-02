<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\MysqlBin\Commands
     * User: Brahma
     * Date: 2023/1/17
     * Time: 13:52
     */

    namespace Liujinyong\MysqlBin\Commands;

    use Illuminate\Database\Capsule\Manager;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Filesystem\Filesystem;

    class CreateDoc extends Acommand
    {


        protected function configure()
        {
            $this->setName('create:doc')->setDescription('创建数据库字段显示的table给前端同学看')
                 ->addArgument('directory', InputArgument::OPTIONAL, 'Directory name for composer-driven project');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $this->askDatabaseInfo($input, $output);

            //$tables = Manager::select('show tables');
            $tables = Manager::select("SELECT TABLE_NAME,TABLE_COMMENT FROM INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA= '" . $this->databaseInfo['database'] . "' and Table_type='BASE TABLE'");

            $info = [];
            if (is_array($tables)) {
                foreach ($tables as $table) {
                    //$field   = "Tables_in_" . $this->databaseInfo['database'];

                    //$excuSql = "SELECT COLUMN_NAME,column_comment,column_type FROM information_schema.COLUMNS WHERE table_schema = '" . $this->databaseInfo['database'] . "' AND table_name = '" . $table->$field . "'";
                    $excuSql = "SELECT COLUMN_NAME,column_comment,column_type FROM information_schema.COLUMNS WHERE table_schema = '" . $this->databaseInfo['database'] . "' AND table_name = '" . $table->TABLE_NAME . "'";

                    $index        = $table->TABLE_COMMENT == "" ? $table->TABLE_NAME : $table->TABLE_NAME . "(" . $table->TABLE_COMMENT . ")";
                    $info[$index] = Manager::select($excuSql);

                }
            }


            $html = "";
            if ($info && is_array($info) && !empty($info)) {
                foreach ($info as $tableName => $item) {
                    $html .= "<table  class=\"tab_top\">
        <th class=\"header\" colspan=\"3\">{$tableName}</th>
        <tr>
            <td class=\"left title\">字段</td>
            <td class=\"right title\">类型</td>
            <td class=\"right title\">注释</td>
        </tr>";

                    if (is_array($item)) {
                        foreach ($item as $field) {

                            $html .= "<tr>
            <td class=\"left\">$field->COLUMN_NAME</td>
            <td class=\"left\">$field->column_type</td>
            <td class=\"right\">{$field->column_comment}</td>
        </tr>";
                        }
                    }

                    $html .= "</table> \n";


                }
            }
            $fs = new Filesystem();

            $content = str_replace("{{html}}", $html, file_get_contents(__DIR__ . '/../stubs/doc'));

            if ($fs->exists('./mysql-bin-api.html')){
                $fs->remove('./mysql-bin-api.html');
            }
            $fs->dumpFile("./mysql-bin-api.html", $content);
            $output->writeln("<info>请访问当前文件夹下的mysql-bin-api.html,请注意数据安全!</info>");


            return 0;

        }



    }