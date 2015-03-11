<?php

namespace Contentacle\Services;

class Template extends \LightnCandy
{
    private $currentDay;

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
                    'capitalise' => function ($args) {
                        return ucwords($args[0]);
                    },
                    'default' => function ($args) {
                        return $args[0] ? $args[0] : $args[1];
                    },
                    'truncate' => function ($args) {
                        $length = isset($args[1]) ? $args[1] : 30;
                        if (strlen($args[0]) - 3 > $length) {
                            return substr($args[0], 0, $length).'...';
                        }
                        return $args[0];
                    },
                    'date' => function ($args) {
                        if (isset($args[1])) {
                            return date($args[1], $args[0]);
                        }
                        if (date('Y', $args[0]) == date('Y')) {
                            return date('M j', $args[0]);
                        }
                        return date('M j, \'y', $args[0]);
                    },
                    'isodate' => function ($args) {
                        return date('d-m-Y h:i:s', $args[0]);
                    },
                    'since' => '\Contentacle\Services\Template::since',
                    'markdown' => function ($args) {
                        return \Michelf\Markdown::defaultTransform($args[0]);
                    }
                ),
                'blockhelpers' => array(
                    'showDay' => function ($cs, $args) {
                        $date = date('dmY', $args[0]);
                        if ($this->currentDay != $date) {
                            $this->currentDay = $date;
                            return $cs;
                        }
                    },
                    'showDayEnd' => function ($cs, $args) {
                        $date = date('dmY', $args[0]);
                        if ($this->currentDay && $this->currentDay != $date) {
                            return $cs;
                        }
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

    protected function since($args) {
        $diff = time() - $args[0];
        if ($diff < 60) {
            $denomination = $diff;
            $division = 'second';
        } elseif ($diff < 3600) {
            $denomination = $diff / 60;
            $division = 'minute';
        } elseif ($diff < 86400) {
            $denomination = $diff / 3600;
            $division = 'hour';
        } elseif ($diff < 2592000) {
            $denomination = $diff / 86400;
            $division = 'day';
        } elseif ($diff < 31536000) {
            $denomination = $diff / 2592000;
            $division = 'month';
        } else {
            $denomination = $diff / 31536000;
            $division = 'year';
        }
        $value = floor($denomination);
        return $value.' '.$division.($value == 1 ? '' : 's').' ago';
    }

}