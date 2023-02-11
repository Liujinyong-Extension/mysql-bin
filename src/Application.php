<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\FastadminBin
     * User: Jack
     * Date: 2022/5/7
     * Time: 21:29
     */

    namespace Liujinyong\MysqlBin;

    use Liujinyong\MysqlBin\Commands\CreateDoc;
    use Liujinyong\MysqlBin\Commands\CreateTable;
    use Liujinyong\MysqlBin\Commands\MysqlDump;
    use Liujinyong\MysqlBin\Commands\MysqlSource;
    use Symfony\Component\Console\Application as Base;

    class Application extends Base
    {
        /**
         * Application constructor.
         *
         * @param string $name
         * @param string $version
         */
        public function __construct()
        {
            parent::__construct();
            $this->add(new CreateTable());
            $this->add(new CreateDoc());
            $this->add(new MysqlDump());
            $this->add(new MysqlSource());
        }
    }