<?php

use Bitrix\Main\Localization\Loc;

class MyComplexProperty
{
    private static $showedCss = false;
    private static $showedJs = false;

    // ---------- Описание типа для свойств Инфоблоков ----------
    public static function GetUserTypeDescription()
    {
        return [
            'PROPERTY_TYPE'        => 'S',
            'USER_TYPE'            => 'my_complex',
            'DESCRIPTION'          => 'Мое комплексное свойство (+HTML)',
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'ConvertToDB'          => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB'        => [__CLASS__, 'ConvertFromDB'],
            'GetSettingsHTML'      => [__CLASS__, 'GetSettingsHTML'],
            'PrepareSettings'      => [__CLASS__, 'PrepareUserSettings'],
            'GetLength'            => [__CLASS__, 'GetLength'],
            'GetPublicViewHTML'    => [__CLASS__, 'GetPublicViewHTML'],
        ];
    }

    // ---------- Описание типа для Пользовательских полей (UF) ----------
    public static function GetUserTypeDescriptionUF()
    {
        return [
            'USER_TYPE_ID' => 'complex_super_final',
            'CLASS_NAME'   => __CLASS__,
            'DESCRIPTION'  => 'ФИНАЛЬНЫЙ ТЕСТ (HTML)',
            'BASE_TYPE'    => 'string',
            'GetEditFormHTML' => [__CLASS__, 'GetEditFormHTML'],
            'OnBeforeSave'    => [__CLASS__, 'OnBeforeSave'],
        ];
    }

    // ---------- Отображение для Инфоблоков ----------
    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        self::showCss();
        self::showJs();

        if (empty($arProperty['USER_TYPE_SETTINGS'])) {
            $arFields = [
                'DESCRIPTION' => ['TITLE' => 'Описание', 'TYPE' => 'html', 'SORT' => 500]
            ];
        } else {
            $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);
        }

        $result = '<table class="mf-fields-list active" style="width: 100%; border-spacing: 10px;">';
        foreach ($arFields as $code => $arItem) {
            if ($arItem['TYPE'] === 'html') {
                $result .= self::showHtmlEditor($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } else {
                $result .= self::showString($code, $arItem['TITLE'], $value, $strHTMLControlName);
            }
        }
        $result .= '</table>';

        return $result;
    }

    // ---------- Отображение для UF (рабочий вариант из FinalComplexProp) ----------
    public static function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        \Bitrix\Main\Loader::includeModule('fileman');

        $rawVal = $arHtmlControl['VALUE'];
        $val = unserialize($rawVal, ['allowed_classes' => false]);

        $text = '';
        if (is_array($val) && isset($val['DESCRIPTION'])) {
            $text = $val['DESCRIPTION'];
        } elseif (!is_array($val) && !empty($val)) {
            // если данные сохранились строкой, а не массивом
            $text = $val;
        }

        $name = $arHtmlControl['NAME'] . '[DESCRIPTION]';

        ob_start();
        \CFileMan::AddHTMLEditorFrame(
            $name,
            $text,
            $name . '_TYPE',
            'html',
            ['height' => 200, 'width' => '100%']
        );
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    // ---------- Сохранение для UF (из FinalComplexProp) ----------
    public static function OnBeforeSave($arUserField, $value)
    {
        $fieldName = $arUserField['FIELD_NAME'];
        $badKey = $fieldName . 'DESCRIPTION';

        // если в POST есть "склеенный" ключ
        if (isset($_POST[$badKey]) && $_POST[$badKey] !== 'html' && !empty($_POST[$badKey])) {
            return serialize(['DESCRIPTION' => $_POST[$badKey]]);
        }

        // резервный вариант, если данные пришли в массиве
        if (is_array($value) && isset($value['DESCRIPTION'])) {
            return serialize($value);
        }

        return $value;
    }

    // ---------- Сохранение для Инфоблоков ----------
    public static function ConvertToDB($arProperty, $value)
    {
        $propID = $arProperty['ID'];
        $result = [];

        foreach ($_POST as $key => $val) {
            if (strpos($key, 'PROP' . $propID) !== false && strpos($key, 'DESCRIPTION') !== false) {
                if ($val !== 'html' && !empty($val)) {
                    $result['DESCRIPTION'] = $val;
                }
            }
        }

        return (!empty($result)) ? ['VALUE' => serialize($result)] : ['VALUE' => ''];
    }

    // ---------- Извлечение из БД ----------
    public static function ConvertFromDB($arProperty, $value)
    {
        $return = [];
        if (!empty($value['VALUE'])) {
            $return['VALUE'] = unserialize($value['VALUE'], ['allowed_classes' => false]);
        }

        return $return;
    }

    // ---------- Обязательный метод для UF ----------
    public static function GetDBColumnType($arUserField)
    {
        return 'text';
    }

    // ---------- Вспомогательные методы (отображение, настройки) ----------
    protected static function showHtmlEditor($code, $title, $value, $strHTMLControlName)
    {
        $name = $strHTMLControlName['VALUE'] . '[' . $code . ']';
        $val = $value['VALUE'][$code] ?? '';

        ob_start();
        echo '<tr><td><b>' . $title . ':</b></td></tr><tr><td>';
        \CFileMan::AddHTMLEditorFrame($name, $val, $name . '_TYPE', 'html', ['height' => 200, 'width' => '100%']);
        echo '</td></tr>';
        $return = ob_get_contents();
        ob_end_clean();

        return $return;
    }

    private static function showString($code, $title, $value, $strHTMLControlName)
    {
        $name = $strHTMLControlName['VALUE'] . '[' . $code . ']';
        $val = $value['VALUE'][$code] ?? '';

        return '<tr><td>' . $title . ':</td><td><input type="text" name="' . $name . '" value="' . $val . '"></td></tr>';
    }

    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        $arPropertyFields = ['HIDE' => ['ROW_COUNT', 'COL_COUNT', 'DEFAULT_VALUE']];

        return '<tr><td>Настройки комплексного свойства (настройте поля в коде или через JS)</td></tr>';
    }

    public static function PrepareUserSettings($arProperty)
    {
        return $arProperty['USER_TYPE_SETTINGS'] ?? [];
    }

    public static function GetLength($arProperty, $arValue)
    {
        return true;
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return $value['VALUE'];
    }

    private static function showCss()
    {
        if (!self::$showedCss) {
            echo '<style>.mf-fields-list{width:100%}</style>';
            self::$showedCss = true;
        }
    }

    private static function showJs()
    {
        self::$showedJs = true;
    }

    private static function showJsForSetting($n)
    {
    }

    private static function showCssForSetting()
    {
    }

    private static function prepareSetting($s)
    {
        return is_array($s) ? $s : [];
    }

    private static function getOptionList($s = '')
    {
        return '';
    }
}