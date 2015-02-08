<?php

namespace Contentacle\Services;

class Template extends \LightnCandy
{
    private function getCompiledPath($templateFilename)
    {
        $templatePath = 'src/Contentacle/Views/'.$templateFilename;
        $compliedPath = sys_get_temp_dir().'/contentacle-'.$templateFilename;

        if (!file_exists($templatePath)) {
            throw new \Exception('Template "'.$templateFilename.'" could not be found');
        }

        if (
            true||
            !file_exists($compliedPath) ||
            filemtime($templatePath) > filemtime($compliedPath)
        ) {
            $compiled = self::compile(file_get_contents($templatePath), array(
                'flags' => self::FLAG_SPVARS | self::FLAG_THIS | self::FLAG_ERROR_EXCEPTION | self::FLAG_ADVARNAME,
                'helpers' => array(
                    'rel' => function ($args) {
                        if (substr($args[0], 0, 5) == 'cont:') {
                            return '<a href="/rels/'.substr($args[0], 5).'">'.$args[0].'</a>';
                        } else {
                            return array($args[0], 'encq');
                        }
                    },
                    'uppercase' => function ($args) {
                        return strtoupper($args[0]);
                    },
                    'default' => function ($args) {
                        return $args[0] ? $args[0] : $args[1];
                    }
                )
            ));
            file_put_contents($compliedPath, $compiled);
        }
        return $compliedPath;
    }

    public function render($templateName, $data)
    {
        $layout = include($this->getCompiledPath('layout.html'));
        $main = include($this->getCompiledPath($templateName.'.html'));

        return $layout(array_merge($data, array(
            '_main' => $main($data),
            'section' => $templateName
        )));
    }

}