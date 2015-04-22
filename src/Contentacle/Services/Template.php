<?php

namespace Contentacle\Services;

class Template extends \LightnCandy
{
    private $currentDay;
    private $options = array();

    function __construct($templatePath, $compiledPath, $markdown)
    {
        $this->templatePath = $templatePath;
        $this->compiledPath = $compiledPath;
        $this->markdown = $markdown;
        $this->options = array(
            'flags' => self::FLAG_SPVARS | self::FLAG_PARENT | self::FLAG_THIS | self::FLAG_ERROR_EXCEPTION | self::FLAG_ADVARNAME | self::FLAG_RUNTIMEPARTIAL,
            'basedir' => array(
                'src/Contentacle/Views'
            ),
            'fileext' => array(
                '.html'
            ),
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
                'count' => function ($args) {
                    $count = count($args[0]);
                    if (isset($args[1])) {
                        return $count.' '.$args[1].($count == 0 || $count > 1 ? 's' : '');
                    }
                    return $count;
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
                'markdown' => '\Contentacle\Services\Template::markdown',
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
                    return date('d-m-Y H:i:s', $args[0]);
                },
                'since' => '\Contentacle\Services\Template::since',
                'size' => function ($args) {
                    return round(strlen($args[0]) / 1024, 3);
                },
                'wordcount' => function ($args) {
                    return str_word_count($args[0]);
                },
                'arraysize' => function ($args) {
                    return count($args[0]);
                }
            ),
            'blockhelpers' => array(
                'equal' => function ($cs, $args) {
                    if ($args[0] == $args[1]) {
                        return $cs;
                    }
                },
                'contains' => function ($cs, $args) {
                    if (!is_array($args[0])) {
                        return;
                    }
                    if (isset($args[2])) {
                        foreach ($args[0] as $item) {
                            if (isset($item[$args[1]]) && $item[$args[1]] == $args[2]) {
                                return $item;
                            }
                        }
                    } elseif (isset($args[0][$args[1]])) {
                        return $cs;
                    }
                },
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
        );
    }

    private function getCompiledPath($templateFilename)
    {
        $templatePath = $this->templatePath.$templateFilename;
        $compliedPath = $this->compiledPath.'/contentacle-'.$templateFilename;

        if (!file_exists($templatePath)) {
            throw new \Exception('Template "'.$templateFilename.'" could not be found');
        }

        if (
            true||
            !file_exists($compliedPath) ||
            filemtime($templatePath) > filemtime($compliedPath)
        ) {
            $compiled = \LightnCandy::compile(file_get_contents($templatePath), $this->options);
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

    public function parse($template, $data = array())
    {
        $compliedPath = $this->compiledPath.'/contentacle-test';
        $compiledTemplate = \LightnCandy::compile($template, $this->options);
        file_put_contents($compliedPath, $compiledTemplate);
        $compiled = include($compliedPath);
        return $compiled($data);
    }

    protected function markdown($args) {
        return $this->markdown->transform($args[0]);
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