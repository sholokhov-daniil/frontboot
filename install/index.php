<?php

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;

class sholokhov_frontboot extends CModule
{
    var $MODULE_ID = "sholokhov.frontboot";
    var $PARTNER_NAME = 'Шолохов Даниил';
    var $PARTNER_URI = 'https://github.com/sholokhov-daniil';

    private const PHP_VERSION = '8.2.0';

    /**
     * @var class-string<DataManager>[]
     */
    private array $orm = [
        \Sholokhov\FrontBoot\Models\ExtensionTable::class,
    ];

    public function __construct()
    {
        $arModuleVersion = [];

        include(__DIR__ . DIRECTORY_SEPARATOR . "version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        } else {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = Loc::getMessage("SHOLOKHOV_EXCHANGE_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("SHOLOKHOV_EXCHANGE_MODULE_DESCRIPTION");
    }

    public function DoInstall(): bool
    {
        global $APPLICATION;

        try {
            $this->checkPhpVersion();
            $this->checkComposer();
            $this->InstallDB();
        } catch (Throwable $exception) {
            $APPLICATION->ThrowException($exception->getMessage());
            return false;
        }

        return true;
    }

    public function DoUninstall(): void
    {
        $this->UnInstallDB();
        $this->Remove();
    }

    public function InstallDB(): void
    {
        $this->registrationEvents();
        $this->Add();

        self::IncludeModule($this->MODULE_ID);

        $this->dropTables();
        $connection = Application::getConnection();
        foreach ($this->orm as $orm) {
            $tableName = $orm::getTableName();

            if ($connection->isTableExists($tableName)) {
                $connection->dropTable($tableName);
            }

            $orm::getEntity()->createDbTable();
        }
    }

    public function UnInstallDB(): void
    {
        $this->unRegistrationEvents();
        $this->dropTables();
        $this->Remove();
    }

    private function dropTables(): void
    {
        $connection = Application::getConnection();
        foreach ($this->orm as $orm) {
            $tableName = $orm::getTableName();
            if ($connection->isTableExists($tableName)) {
                $connection->dropTable($tableName);
            }
        }
    }

    private function checkPhpVersion(): void
    {
        if (version_compare(phpversion(), self::PHP_VERSION) == -1) {
            throw new Exception(
                Loc::getMessage("SHOLOKHOV_EXCHANGE_MODULE_INVALID_PHP", ['#VERSION#' => self::PHP_VERSION])
            );
        }
    }

    private function checkComposer(): void
    {
        $autoload = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        if (!file_exists($autoload)) {
            throw new Exception(
                Loc::getMessage('SHOLOKHOV_EXCHANGE_MODULE_INVALID_COMPOSER')
            );
        }
    }

    private function registrationEvents(): void
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible("main", "OnBeforeProlog", $this->MODULE_ID);
    }

    private function unRegistrationEvents(): void
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler("main", "OnBeforeProlog", $this->MODULE_ID);
    }
}