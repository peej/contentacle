<?php

namespace Contentacle\Services;

class Diff extends \cogpowered\FineDiff\Diff {

    private $diffString;

    public function calculate($lines) {
        $diff = array();
        $minusLine = null;

        foreach ($lines as $line) {
            preg_match('/^([0-9-]+),([0-9+]+) (.*)$/', $line, $match);
            
            if ($match && $match[1] && $match[2]) {
                $item = array(
                    'to' => $match[2],
                    'from' => $match[1],
                    'text' => htmlspecialchars($match[3])
                );

                if ($minusLine === null && $item['from'] == '-') {
                    $minusLine = count($diff);
                } elseif ($minusLine !== null && $item['to'] == '+' && $diff[$minusLine]['from'] == '-') {
                    list(
                        $diff[$minusLine]['text'],
                        $item['text']
                    ) = $this->diffLine(
                        html_entity_decode($diff[$minusLine]['text']),
                        $match[3]
                    );
                    $minusLine++;
                } elseif ($item['from'] != '-') {
                    $minusLine = null;
                }

                $diff[] = $item;
            }
        }

        return $diff;
    }

    private function diffLine($from, $to)
    {
        $diffString = $this->render($from, $to);
        $regex = '#^<(ins|del)>([^<]*)</\1>$#';

        return array(
            preg_replace($regex, '$2', preg_replace('#<ins>.*?</ins>#', '', $diffString)),
            preg_replace($regex, '$2', preg_replace('#<del>.*?</del>#', '', $diffString))
        );
    }

}