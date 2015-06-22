<?php
namespace Bilna\Libraries;

/**
 * Description of WidgetManager
 *
 * @author mariovalentino
 */
class WidgetManager {

    public static function get($widgetClass, $parameter = array()){
        $widgetClass = '\Frontend\Widgets\\'.$widgetClass;
        return new $widgetClass($parameter);
    }
}
