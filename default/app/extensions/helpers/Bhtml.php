<?php

class Bhtml
{
    protected static function attrsdefaut($attrs, $defaults)
    {
        foreach ($defaults as $k => $v) {
            if (isset($attrs[$k])) {
                if (strpos($attrs[$k], $v) === false) {
                    $attrs[$k] .= ' ' . $v;
                }
            } else {
                $attrs[$k] = $v;
            }
        }
        return $attrs;
    }

    public static function img($src, $alt = '', $attrs = [])
    {
        $attrs = Bhtml::attrsdefaut($attrs, ["class" => ""]);
        $attrs = Tag::getAttrs($attrs);
        return '<img src="' . PUBLIC_PATH . "storage/$src\" alt=\"$alt\" " . $attrs. '/>';
    }

    public static function link($action, $text, $attrs = [])
    {
        $text = " " . $text;
        $attrs = Bhtml::attrsdefaut($attrs, []);
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . "$action\" $attrs>$text</a>";
    }



}