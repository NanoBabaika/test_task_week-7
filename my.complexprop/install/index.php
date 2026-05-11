<?php

class my_complexprop extends CModule
{
    public $MODULE_ID = 'my.complexprop';
    public $MODULE_NAME = 'Тренировочный модуль (7ая неделя)';
    public $MODULE_DESCRIPTION = 'Задание 7 недели: комплексные свойства.';
    public $PARTNER_NAME = 'Alexandr';
    public $PARTNER_URI = 'http://localhost';

    public function __construct()
    {
        $this->MODULE_ID = 'my.complexprop';
        $this->MODULE_NAME = 'Тренировочный модуль (7ая неделя)';
        $this->MODULE_DESCRIPTION = 'Задание 7 недели: комплексные свойства.';
        $this->MODULE_VERSION = '1.0.0';
        $this->MODULE_VERSION_DATE = '2026-05-04 22:00:00';
    }

    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);
        RegisterModuleDependences(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            'MyComplexProperty',
            'GetUserTypeDescription'
        );
        RegisterModuleDependences(
            'main',
            'OnUserTypeBuildList',
            $this->MODULE_ID,
            'MyComplexProperty',
            'GetUserTypeDescriptionUF'
        );
    }

    public function DoUninstall()
    {
        UnRegisterModule($this->MODULE_ID);
    }
}