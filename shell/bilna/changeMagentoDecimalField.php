<?php

require_once dirname(__FILE__) . '/../abstract.php';

class changeDecimalField extends Mage_Shell_Abstract {
  protected $data_type = "decimal";
  protected $numeric_precision = 12;
  protected $numeric_scale = 4;
  protected $new_column_type = "decimal(16,4)";
  protected $resource;
  protected $write;
  protected $read;
  protected $dbname;
  protected $dbuser;
  protected $dbpass;
  protected $backupfile;

  public function init() {
      $this->resource = Mage::getSingleton('core/resource');
      $this->read = $this->resource->getConnection('core_read');
      $this->write = $this->resource->getConnection('core_write');
      // $this->dbname = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');
      $config = Mage::getConfig()->getResourceConnectionConfig("default_setup");
      $this->dbname = $config->dbname;
      $this->backupfile = "/tmp/" . $this->dbname . "_back_" . now() . ".sql";
      $this->dbuser = $config->username;
      $this->dbpass = $config->password;
  }

  function run() {
    $this->init();
    // $mysqldump = "mysqldump -u $this->dbuser -p $this->dbpass --no-data $this->dbname > $this->backupfile";
    // shell_exec($mysqldump);

    $sql = "SELECT TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,COLUMN_DEFAULT,IS_NULLABLE,COLUMN_COMMENT
            FROM information_schema.columns
            WHERE
              table_schema = '".$this->dbname."' AND
              data_type= '".$this->data_type."' AND
              NUMERIC_PRECISION = ".$this->numeric_precision." AND
              NUMERIC_SCALE = ".$this->numeric_scale."
            ORDER BY table_name,ordinal_position";

    $allField = $this->read->fetchAll($sql);
    $sql_alter = "";
    $table_name_before = "";
    $countOfTable = 0;
    
    // if (count($allField) > 0) echo now() . " : first running\n";
    foreach ($allField as $key => $value) {
      if ($table_name_before != $value["TABLE_NAME"]) {
        $countOfTable++;
        if ($sql_alter != "") $this->alterTable($sql_alter);
        $sql_alter = "ALTER TABLE " . $value["TABLE_NAME"] . " MODIFY " . $value["COLUMN_NAME"] . " ".$this->new_column_type;
      } else {
        $sql_alter .= ", MODIFY " . $value["COLUMN_NAME"] . " " . $this->new_column_type;
      }
      if ($value["IS_NULLABLE"] == "NO") $sql_alter .= " NOT NULL";
      $sql_alter .= ($value['COLUMN_DEFAULT'] != NULL) ? " DEFAULT '" . $value['COLUMN_DEFAULT'] ."'" : " DEFAULT NULL";
      $sql_alter .= ($value['COLUMN_COMMENT'] != "") ? " COMMENT '".$value['COLUMN_COMMENT']."'" : "";
      $table_name_before = $value["TABLE_NAME"];
    }
    if ($countOfTable == 1) $this->alterTable($sql_alter);
  }

  function alterTable($sql_alter) {
    $sql_alter .= ";";
    //echo $sql_alter . "\n";
    if ($this->write->query($sql_alter)) {
      echo now(). " : sukses executing: " . $sql_alter . "\n\n";
    } else {
      echo now(). " : gagal executing: " . $sql_alter . "\n\n";
    }
  }
}
$shell = new changeDecimalField();
$shell->run();