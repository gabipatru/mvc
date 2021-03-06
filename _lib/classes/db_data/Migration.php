<?php

/**
 * Allow running migrations when a page is loaded
 */

class Migration extends DbData {
    const TABLE_NAME    = '_migration';
    const ID_FIELD      = 'migration_id';
    
    protected $aFields = array(
        'migration_id',
        'name',
        'version'
    );
    
    function __construct($table = self::TABLE_NAME, $id = self::ID_FIELD, $status = '') {
        parent::__construct($table, $id, $status);
    }
    
    /**
     * Find the migrations which have to be run and run them
     */
    public function runMigrations() {
        // load current mirations status
        require_once(CONFIG_DIR . '/migration.php');
        
        db::startTransaction();
        db::lock_transaction('migrations');
        
        // load database version for all migrations
        $oDatabaseVersion = $this->Get();
        if (!count($oDatabaseVersion)) {
            $this->deployMigrations('migrations', '000');
            $oDatabaseVersion = $this->Get();
        }

        // create array with migration names from database
        $aDatabaseMigrationNames = array();
        foreach ($oDatabaseVersion as $oMigration) {
            $aDatabaseMigrationNames[] = $oMigration->getName();
        }
        
        // check if there are any new migration groups
        foreach ($aMigrationsConfig as $migrationName => $latestVersion) {
            if (!in_array($migrationName, $aDatabaseMigrationNames)) {
                $this->deployMigrations($migrationName, '000');
            }
        }
        
        // check for new migrations
        foreach ($aMigrationsConfig as $migrationName => $latestVersion) {
            foreach ($oDatabaseVersion as $oMigration) {
                if ($oMigration->getName() == $migrationName) {
                    if ($oMigration->getVersion() != $latestVersion) {
                        $this->deployMigrations($migrationName, $oMigration->getVersion());
                    }
                    break;
                }
            }
        }
        
        db::commitTransaction();
    }
    
    /**
     * Run the migrations
     */
    private function deployMigrations($migrationName, $currentVersion) {
        $folder = MIGRATIONS_DIR . '/' . $migrationName;
        $nextMigrationName = $this->getNextMigration($currentVersion);

        // while there are migrations, run them
        while (file_exists($folder . '/' . $this->getNextMigration($currentVersion) . '.php')) {
            // never run the previous migration
            $executedMigrations = '';
            if (isset($migrationSql)) {
                unset($migrationSql);
            }
            
            $nextMigrationName = $this->getNextMigration($currentVersion);
            require_once($folder . '/' . $nextMigrationName . '.php');
            if (empty($migrationSql)) {
                die('Failed to run migration ' . $migrationName . ' : ' . $nextMigrationName);
            }

            $timeStart = microtime(true);

            // run the migration(s)
            if (is_array($migrationSql)) {
                foreach ($migrationSql as $mig) {
                    $r = db::query($mig);
                    if (!$r) {
                        die('Failed to run migration '.$mig);
                    }
                    $executedMigrations .= $mig;
                }
            }
            else {
                $r = db::query($migrationSql);
                if (!$r) {
                    die('Failed to run migration '.$mig);
                }
                $executedMigrations = $migrationSql;
            }
            
            // migration ran, update the database version
            if ($nextMigrationName === '001') {
                
                $oItem = new SetterGetter();
                $oItem->setName($migrationName);
                $oItem->setVersion($nextMigrationName);
                
                $r = $this->Add($oItem);
                if (!$r) {
                    die('Failed to add migration name : ' . $migrationName);
                }
                $migrationId = db::lastInsertId();
            }
            else {
                $filters = array('name' => $migrationName);
                $oMigration = $this->singleGet($filters);
                if (!count($oMigration)) {
                    die('Failed to fetch migration name : ' . $migrationName);
                }

                $oItem = new SetterGetter();
                $oItem->setVersion($nextMigrationName);
                
                $r = $this->Edit($oMigration->getMigrationId(), $oItem);
                if (!$r) {
                    die('Failed to update migration version : ' . $migrationName);
                }
                $migrationId = $oMigration->getMigrationId();
            }
            
            $timeEnd = microtime(true);
            $time = $timeEnd - $timeStart;
            
            // update the migrations log table
            $this->updateMigrationLog($executedMigrations, $migrationId, $time);
            
            $currentVersion = $nextMigrationName;
        }
    }
    
    /**
     * Update the migrations log
     */
    private function updateMigrationLog($sql, $migrationId, $time) {
        $oItem = new SetterGetter();
        $oItem->setMigrationId($migrationId);
        $oItem->setQuery($sql);
        $oItem->setDuration($time);
        
        $oMigrationLog = new MigrationLog();
        $r = $oMigrationLog->Add($oItem);
        if (!$r) {
            log_message('migrations.log', 'Failed to update migrations log with query '>$sql);
            return false;
        }
        return true;
    }
    
    /**
     * Return the next version fo the migrations based on the current version
     */
    private function getNextMigration($currentVersion) {
        $nextVersion = (int) $currentVersion;
        $nextVersion++;
        if ($nextVersion < 10) {
            return '00' . $nextVersion;
        }
        if ($nextVersion < 100) {
            return '0' . $nextVersion;
        }
        return (string) $nextVersion;
    }
}