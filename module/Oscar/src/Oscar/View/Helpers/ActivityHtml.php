<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 07/10/15 13:37
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;

use Laminas\View\Helper\AbstractHtmlElement;

class ActivityHtml extends AbstractHtmlElement
{
    public function __invoke($message)
    {

        $matches = [];
        $re = "/\\[(Project|Organization|Person|Activity):([0-9]+):([^\\]]*)\\]/u";

        preg_match_all($re, $message, $matches);

        $subject = $matches[0];

        if (count($subject) == 0) {
            return $message;
        }

        $context = $matches[1];
        $id = $matches[2];
        $text = $matches[3];
        $tpl = '<a href="%s" class="link">%s</a>';

        for ($i = 0; $i<count($subject); $i++) {
            $icon = '';
            if ($context[$i] == 'Project') {
                $url = $this->getView()->url('project/show', ['id' => $id[$i]]);
                $icon = '<i class="icon-cubes"></i>';
            } elseif ($context[$i] == 'Person') {
                $url = $this->getView()->url('person/show', ['id' => $id[$i]]);
                $icon = '<i class="icon-user"></i>';
            } elseif ($context[$i] == 'Activity') {
                $url = $this->getView()->url('contract/show', ['id' => $id[$i]]);
                $icon = '<i class="icon-cube"></i>';
            } else {
                $icon = '<i class="icon-building-filled"></i>';
                $url = $this->getView()->url('organization/show', ['id' => $id[$i]]);
            }

            $message = str_replace($subject[$i], sprintf($tpl, $url, $icon.' ' .$text[$i]), $message);
        }
        return $message;
    }
}
