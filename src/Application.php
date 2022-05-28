<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\FastadminBin
     * User: Jack
     * Date: 2022/5/7
     * Time: 21:29
     */

    namespace Liujinyong\MysqlBin;

    use Liujinyong\MysqlBin\Commands\CreateTable;
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
        }
    }