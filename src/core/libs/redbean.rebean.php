<?php
/**
 * RedBean ReBean (Revision Bean)
 *
 * @file    ReBean.php
 * @desc    Revisionplugin to support each bean with custom revision tables and triggers
 * @author  Zewa
 *
 */
class RedBean_ReBean implements RedBean_Plugin
{
  /**
   * Creates the revision support for the given Bean
   *
   * @param  RedBean_OODBBean $bean          The bean-type to be revision supported
   */
  public function createRevisionSupport(RedBean_OODBBean $bean)
  {
    // check if the bean already has revision support
    if(R::getWriter()->tableExists("revision" . $bean->getMeta('type')))
    {
      throw new ReBean_Exception("The given Bean has already revision support");
    }

    $export = $bean->export();
    $duplicate = R::dispense("revision" . $bean->getMeta('type'));

    $duplicate->action      = "";                                 // real enum needed
    $duplicate->lastedit    = date('Y-m-d h:i:s');


    $l_props = array();
    foreach (array_keys($export) as $c_col)
    {
      $l_props[sprintf("old_%s", $c_col)] = $export[$c_col];
      $l_props[sprintf("new_%s", $c_col)] = $export[$c_col];
    }

    $duplicate->id = $export["id"];
    $duplicate->import($l_props);
    $duplicate->uid = null;
    $duplicate->setMeta('cast.action','string');
    $duplicate->setMeta('cast.lastedit','datetime');
    RedBean_Facade::store($duplicate);

    $this->createTrigger($bean, $duplicate);
  }

  private function getRevisionColumns(RedBean_OODBBean $bean, $p_type)
  {
    $l_cols = array();
    foreach (array_keys($bean->getProperties()) as $c_col)
    {
      if (empty($c_col) || $c_col == null)
        continue;

      switch ($p_type)
      {
      case "update":
        array_push($l_cols, sprintf("old_%s", $c_col));
        array_push($l_cols, sprintf("new_%s", $c_col));
        break;
      case "delete":
        array_push($l_cols, sprintf("old_%s", $c_col));
        break;
      case "insert":
        array_push($l_cols, sprintf("new_%s", $c_col));
        break;
      }
    }

    return implode(",", $l_cols);
  }

  private function getOriginalColumns(RedBean_OODBBean $bean, $p_type)
  {
    $l_cols = array();
    foreach (array_keys($bean->getProperties()) as $c_col)
    {
      if (empty($c_col) || $c_col == null)
        continue;

      switch ($p_type)
      {
      case "update":
        array_push($l_cols, sprintf("OLD.%s", $c_col));
        array_push($l_cols, sprintf("NEW.%s", $c_col));
        break;
      case "delete":
        array_push($l_cols, sprintf("OLD.%s", $c_col));
        break;
      case "insert":
        array_push($l_cols, sprintf("NEW.%s", $c_col));
        break;
      }
    }

    return implode(",", $l_cols);
  }

  private function createTrigger(RedBean_OODBBean $bean, RedBean_OODBBean $duplicate)
  {
    RedBean_Facade::$adapter->exec("DROP TRIGGER IF EXISTS `trg_" . $bean->getMeta('type') . "_AI`;");
    RedBean_Facade::$adapter->exec("CREATE TRIGGER `trg_" . $bean->getMeta('type') . "_AI` AFTER INSERT ON `" . $bean->getMeta('type') . "` FOR EACH ROW BEGIN
    \tINSERT INTO " . $duplicate->getMeta('type') . "(`action`, `lastedit`, `uid`, " . $this->getRevisionColumns($bean, "insert") . ") VALUES ('insert', NOW(), @uid, " . $this->getOriginalColumns($bean, 'insert') . ");
    END;");

    RedBean_Facade::$adapter->exec("DROP TRIGGER IF EXISTS `trg_" . $bean->getMeta('type') . "_AU`;");
    RedBean_Facade::$adapter->exec("CREATE TRIGGER `trg_" . $bean->getMeta('type') . "_AU` AFTER UPDATE ON `" . $bean->getMeta('type') . "` FOR EACH ROW BEGIN
    \tINSERT INTO " . $duplicate->getMeta('type') . "(`action`, `lastedit`, `uid`, " . $this->getRevisionColumns($bean, "update") . ") VALUES ('update', NOW(), @uid, " . $this->getOriginalColumns($bean, 'update') . ");
    END;");

    RedBean_Facade::$adapter->exec("DROP TRIGGER IF EXISTS `trg_" . $bean->getMeta('type') . "_AD`;");
    RedBean_Facade::$adapter->exec("CREATE TRIGGER `trg_" . $bean->getMeta('type') . "_AD` AFTER DELETE ON `" . $bean->getMeta('type') . "` FOR EACH ROW BEGIN
    \tINSERT INTO " . $duplicate->getMeta('type') . "(`action`, `lastedit`, `uid`, " . $this->getRevisionColumns($bean, "delete") . ") VALUES ('delete', NOW(), @uid, " . $this->getOriginalColumns($bean, 'delete') . ");
    END;");
  }
}

class ReBean_Exception extends Exception
{
  public function __construct($message, $code = 0, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}

// add plugin to RedBean facade
R::ext( 'createRevisionSupport', function(RedBean_OODBBean $p_bean) {
    $l_plugin = new RedBean_ReBean();
    $l_plugin->createRevisionSupport($p_bean);
});
