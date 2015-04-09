<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;

/**
 * Class for rollback capabilities
 */
class Rollback
{
    /**
     * @var string
     */
    protected $backupFileDir;

    /**
     * @var string
     */
    protected $restoreTargetDir;

    /**
     * Initialize rollback.
     *
     * @param string|null $backupFileDir
     * @param string|null $restoreTargetDir
     */
    public function __construct($backupFileDir = null, $restoreTargetDir = null)
    {
        $this->backupFileDir = $backupFileDir ? $backupFileDir : UPDATER_BACKUP_DIR;
        $this->restoreTargetDir = $restoreTargetDir ? $restoreTargetDir : MAGENTO_BP;
    }

    /**
     * Rollback to an archive version using the backup file path specified by update_queue.json
     *
     * @param string $backupFilePath
     * @throws \Exception
     * @return bool
     */
    public function manualRollback($backupFilePath)
    {
        if (!file_exists($backupFilePath)) {
            throw new \Exception ("The backup file specified by update_queue.json does not exist.");
        }
        echo "Restoring archive from $backupFilePath ...";
        $this->rollbackHelper($backupFilePath);

        return true;
    }

    /**
     * Automatic rollback when any error happens during update process
     *
     * @return bool
     */
    public function autoRollback()
    {
        $backupFileName = $this->getLastBackupFile();
        $backupFilePath = $this->backupFileDir . $backupFileName;
        $this->rollbackHelper($backupFilePath);

        return true;
    }

    /**
     * Find the last backup file from backup directory
     *
     * @throws \Exception
     * @return string
     */
    protected function getLastBackupFile()
    {
        $allFileList = scandir($this->backupFileDir, SCANDIR_SORT_DESCENDING);
        $backupFileName = '';

        foreach ($allFileList as $fileName) {
            if (strpos($fileName, 'backup') !== false) {
                $backupFileName = $fileName;
                break;
            }
        }

        if (empty($backupFileName)) {
            throw new \Exception ("No available backup file found.");
        }
        return $backupFileName;
    }

    /**
     * Rollback
     *
     * @param string $backupFilePath
     * @throws \Exception
     * @return void
     */
    protected function rollbackHelper($backupFilePath)
    {
        exec('unzip ' . $backupFilePath . ' -d ' . $this->restoreTargetDir, $output, $return);
        if ($return) {
            throw new \Exception("Rollback was not successful.");
        }
        foreach ($output as $message) {
            printf("$message\n");
        }
    }
}
